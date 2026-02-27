<?php

namespace Ghazym\LaravelModuleSuite\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

trait HasMedia
{
    /**
     * Boot the trait.
     */
    public static function bootHasMedia()
    {
        static::deleting(function ($model) {
            // Delete all media files when model is deleted
            $model->removeAllMedia();
        });
    }

    /**
     * Get all media for the model.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(config('laravel-module-suite.media.model', config('laravel-module-suite.media.model')), 'mediable');
    }

    /**
     * Get media from a specific collection.
     */
    public function getMedia(string $name): MorphMany
    {
        return $this->media()->where('name', $name);
    }

    /**
     * Get the first media from a collection.
     */
    public function getFirstMedia(string $name): ?Model
    {
        return $this->getMedia($name)->first();
    }

    /**
     * Get the last media from a collection.
     */
    public function getLastMedia(string $name): ?Model
    {
        return $this->getMedia($name)->latest()->first();
    }

    /**
     * Add a file to the model.
     *
     * @return Model|array
     */
    public function addMedia(UploadedFile $file, string $name, string $folder): Model|array
    {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (isset($validation['error'])) {
                return $validation;
            }

            DB::beginTransaction();

            $filePath = $this->storeFile($file, $folder);

            if (!$filePath) {
                return ['error' => 'Failed to store file.'];
            }

            $media = $this->media()->create([
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'file_path' => $filePath,
                'mime_type' => $file->getMimeType(),
                'name' => $name,
            ]);

            DB::commit();
            return $media;
        } catch (Exception $e) {
            DB::rollBack();
            // Clean up any uploaded file if database transaction fails
            $this->deleteFile(null, $filePath ?? null);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Validate file size and type.
     */
    protected function validateFile(UploadedFile $file): array|bool
    {
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Check if file type is allowed
        if (!in_array($mimeType, config('laravel-module-suite.media.allowed_mimes'))) {
            return ['error' => 'File type not allowed.'];
        }

        // Get max size for this file type or use default
        $maxSize = config('laravel-module-suite.media.max_sizes.' . $mimeType, config('laravel-module-suite.media.max_size'));

        if ($fileSize > $maxSize) {
            $maxSizeMB = $maxSize / 1024 / 1024;
            return ['error' => "File size exceeds maximum limit of {$maxSizeMB}MB for this file type."];
        }

        return true;
    }

    /**
     * Add multiple files to the model.
     *
     * @return array
     */
    public function addMultipleMedia(array $files, string $name, string $folder): array
    {
        $allMedia = [];
        $errors = [];

        foreach ($files as $file) {

            $media = $this->addMedia($file, $name, $folder);

            if ($media) {
                $allMedia[] = $media;
            } else {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $media['error']
                ];
            }
        }

        if (!empty($errors)) {
            return ['error' => 'Some files failed to upload: ' . json_encode($errors)];
        }

        return $allMedia;
    }

    /**
     * Update a media file for the model.
     *
     * @return Model|array
     *
     */
    public function updateMedia(Model $media, UploadedFile $file, string $name, string $folder): Model|array
    {
        try {
            DB::beginTransaction();

            if ($this->isMediaBelongsToModel($media)) {
                // Delete old file if it exists
                $this->deleteFile($media);

                // Store new file
                $filePath = $this->storeFile($file, $folder);

                if (!$filePath) {
                    return ['error' => 'Failed to store new file.'];
                }

                // Update media record
                $media->update([
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'name' => $name,
                ]);

                DB::commit();
                return $media->fresh();
            }

            DB::commit();
            return ['error' => 'Media does not belong to this model.'];
        } catch (Exception $e) {
            DB::rollBack();
            // Clean up any uploaded file if transaction fails
            $this->deleteFile(null, $filePath ?? null);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Sync media files: Keep specified ones and add new ones.
     *
     * @param array $files
     * @param string $name
     * @param string $folder
     * @param array $keepMediaIds
     * @return array
     */
    public function updateMultipleMedia(array $files, string $name, string $folder, array $keepMediaIds = []): array
    {
        try {
            DB::beginTransaction();

            $currentMedia = $this->getMedia($name);

            $currentMedia->whereNotIn('id', $keepMediaIds)->get()->each(function ($media) {
                $this->removeMedia($media);
            });

            if (!empty($files)) {
                $this->addMultipleMedia($files, $name, $folder);
            }

            DB::commit();

            return $this->getMedia($name)->get()->toArray();

        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Remove a media from the model.
     *
     * @return bool|array
     */
    public function removeMedia(Model $media): bool|array
    {
        try {
            DB::beginTransaction();

            if ($this->isMediaBelongsToModel($media)) {

                $this->deleteFile($media);
                $deleted = $media->delete();

                DB::commit();
                return $deleted;
            }

            DB::commit();
            return false;
        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Remove multiple media.
     *
     * @return bool|array
     */
    public function removeMultipleMedia(string $name): bool|array
    {
        try {
            DB::beginTransaction();

            $this->getMedia($name)->each(function (Model $media) {
                $this->removeMedia($media);
            });

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Remove all media from the model.
     *
     * @return bool|array
     */
    public function removeAllMedia(): bool|array
    {
        try {
            DB::beginTransaction();

            $this->media()->each(function (Model $media) {
                $this->removeMedia($media);
            });

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Check if media belongs to the model.
     */
    protected function isMediaBelongsToModel(Model $media): bool
    {
        return $media->mediable_id === $this->getKey() && $media->mediable_type === $this->getMorphClass();
    }

    /**
     * Store a file in storage.
     */
    protected function storeFile(UploadedFile $file, string $folder): string
    {
        $fileName = 'M-' . Str::random(10) . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();

        // Determine file type category
        $mimeType = $file->getMimeType();
        $type = match(true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            str_starts_with($mimeType, 'application/zip') || str_starts_with($mimeType, 'application/x-rar-compressed') => 'archive',
            default => 'document'
        };

        // Get appropriate disk for file type
        $disk = config("laravel-module-suite.media.disk.types.{$type}", config('laravel-module-suite.media.disk.default'));

        return $file->storeAs(
            config('laravel-module-suite.media.default_folder') . '/' . $folder,
            $fileName,
            ['disk' => $disk]
        );
    }

    /**
     * Delete a file from storage.
     */
    protected function deleteFile(?Model $media = null, ?string $path = null): void
    {
        $filePath = $path ?? $media?->file_path;

        if (!$filePath) {
            return;
        }

        if ($media) {
            $mimeType = $media->mime_type;
            $type = match(true) {
                str_starts_with($mimeType, 'image/') => 'image',
                str_starts_with($mimeType, 'video/') => 'video',
                str_starts_with($mimeType, 'audio/') => 'audio',
                str_starts_with($mimeType, 'application/zip') || str_starts_with($mimeType, 'application/x-rar-compressed') => 'archive',
                default => 'document'
            };
        } else {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $type = match($extension) {
                'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
                'mp4', 'mov', 'avi', 'wmv' => 'video',
                'mp3', 'wav', 'ogg' => 'audio',
                'zip', 'rar' => 'archive',
                default => 'document'
            };
        }

        $disk = config("laravel-module-suite.media.disk.types.{$type}", config('laravel-module-suite.media.disk.default'));

        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
        }
    }
} 
