<?php

namespace Ghazym\LaravelModuleSuite\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait RepositoryTrait
{
    /**
     * Build and configure the query based on parameters
     *
     * @param Builder $query
     * @param array $parameters
     * @return Builder|array
     */
    private function buildQuery(Builder $query, array $parameters = []): Builder|array
    {
        try {
            $query->when(!empty($parameters['select']), function ($query) use ($parameters) {
                $query->select($parameters['select']);
            });

            $query->when(!empty($parameters['relations']), function ($query) use ($parameters) {
                $query->with($parameters['relations']);
            });

            $query->when(!empty($parameters['where']) && is_array($parameters['where']), function ($query) use ($parameters) {
                foreach ($parameters['where'] as $condition) {
                    if (is_array($condition) && count($condition) >= 2) {
                        $query->where(...$condition);
                    }
                }
            });


            $query->when(!empty($parameters['search']) && is_array($parameters['search']), function ($query) use ($parameters) {
                $searchTerm = $parameters['search']['search'];
                $columns = $parameters['search']['columns'];

                if (method_exists($query, 'whereAny')) {
                    $query->whereAny($columns, 'LIKE', "%{$searchTerm}%");
                } else {
                    // Fallback for older Laravel versions
                    $query->where(function (Builder $q) use ($searchTerm, $columns) {
                        foreach ($columns as $column) {
                            $q->orWhere($column, 'LIKE', "%{$searchTerm}%");
                        }
                    });
                }
            });

            $query->orderBy('created_at', 'desc');

            return $query;
        } catch (\Throwable $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Create a new query builder instance
     *
     * @param Model $model
     * @param array $parameters
     * @return Builder|array
     */
    public function query(Model $model, array $parameters = []): Builder|array
    {
        return $this->buildQuery($model->query(), $parameters);
    }

    /**
     * Get all records
     *
     * @param Model $model
     * @param array $parameters
     * @return Collection|array
     */
    public function getAll(Model $model, array $parameters = []): Collection|array
    {
        return $this->buildQuery($model->query(), $parameters)->get();
    }

    /**
     * Get a single record by ID
     *
     * @param Model $model
     * @param int $id
     * @param array $parameters
     * @return Model|array|null
     */
    public function getOne(Model $model, int $id, array $parameters = []): Model|array|null
    {
        $data = $model->find($id);
        if (!$data) {
            return ['error' => 'Resource not found', 'code' => 404];
        }
        return $this->buildQuery($model->query(), $parameters)->find($id);
    }

    /**
     * Create a new record
     *
     * @param Model $model
     * @param array $request
     * @return Model|array
     */
    public function create(Model $model, array $request): Model|array
    {
        try {
            return $model->create($request);
        } catch (\Throwable $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Update an existing record
     *
     * @param Model $model
     * @param array $request
     * @param int $id
     * @return Model|array|null
     */
    public function edit(Model $model, array $request, int $id): Model|array|null
    {
        try {
            $data = $model->find($id);
            if (!$data) {
                return ['error' => 'Resource not found', 'code' => 404];
            }
            $data->update($request);
            return $data;
        } catch (\Throwable $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Delete a record
     *
     * @param Model $model
     * @param int $id
     * @return bool|array
     */
    public function delete(Model $model, int $id): bool|array
    {
        try {
            $data = $model->find($id);

            if (!$data) {
                return ['error' => 'Resource not found', 'code' => 404];
            }

            return $model->destroy($id);
        } catch (\Throwable $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle errors consistently across the trait
     *
     * @param \Throwable $e
     * @return array
     */
    private function handleError(\Throwable $e): array
    {
        return ['error' => $e->getMessage(), 'code' => 500];
    }
} 