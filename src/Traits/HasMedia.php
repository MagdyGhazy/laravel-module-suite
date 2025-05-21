<?php

namespace Ghazym\ModuleBuilder\Traits;

use Ghazym\ModuleBuilder\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

trait HasMedia
{
    /**
     * Get all media for the model.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
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
    public function getFirstMedia(string $name): ?Media
    {
        return $this->getMedia($name)->first();
    }

    /**
     * Get the last media from a collection.
     */
    public function getLastMedia(string $name): ?Media
    {
        return $this->getMedia($name)->latest()->first();
    }

    /**
     * Add a file to the model.
     *
     * @return Media|array  
     */
    public function addMedia(UploadedFile $file, string $name, string $folder): Media|array
    {
        try {
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
            $this->deleteFile($filePath ?? null);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Add multiple files to the model.
     *
     * @return array  
     */
    public function addMultipleMedia(array $files, string $name, string $folder): array
    {
        $media = [];
        $errors = [];

        foreach ($files as $file) {

            $media = $this->addMedia($file, $name, $folder);

            if ($media) {
                $media[] = $media;
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

        return $media;
    }

    /**
     * Update a media file for the model.
     *
     * @return Media|array  
     */
    public function updateMedia(Media $media, UploadedFile $file, string $name, string $folder): Media|array
    {
        try {
            DB::beginTransaction();

            if ($this->isMediaBelongsToModel($media)) {
                // Delete old file if it exists
                $this->deleteFile($media->file_path);

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
            $this->deleteFile($filePath ?? null);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Update multiple media files for the model.
     *
     * @return array  
     */
    public function updateMultipleMedia(array $mediaFiles, string $name, string $folder): array
    {
        $errors = [];

        $this->clearMultipleMedia($name);

        foreach ($mediaFiles as $mediaFile) {

            $media = $this->addMedia($mediaFile['file'], $name, $folder);

            if ($media) {
                $media[] = $media;
            } else {
                $errors[] = [
                    'file' => $mediaFile['file']->getClientOriginalName(),
                    'error' => $media['error']
                ];
            }   
        }

        if (!empty($errors)) {
            return ['error' => 'Some files failed to update: ' . json_encode($errors)];
        }

        return $media;
    }

    /**
     * Remove a media from the model.
     *
     * @return bool|array  
     */
    public function removeMedia(Media $media): bool|array
    {
        try {
            DB::beginTransaction();

            if ($this->isMediaBelongsToModel($media)) {
                
                $this->deleteFile($media->file_path);
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
     * @throws Exception
     */
    public function clearMultipleMedia(string $name): void
    {
        try {
            DB::beginTransaction();

            $this->getMedia($name)->each(function (Media $media) {
                $this->removeMedia($media);
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if media belongs to the model.
     */
    private function isMediaBelongsToModel(Media $media): bool
    {
        return $media->mediable_id === $this->id && $media->mediable_type === get_class($this);
    }

    /**
     * Store a file in storage.
     */ 
    private function storeFile(UploadedFile $file, string $folder): string
    {
        $fileName = 'M-' . Str::random(10) . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('media/' . $folder, $fileName);
    }

    /**
     * Delete a file from storage.
     */
    private function deleteFile(?string $filePath): void
    {
        if ($filePath && Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }
} 