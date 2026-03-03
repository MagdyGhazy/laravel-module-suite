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
    protected function buildQuery(Builder $query, array $parameters = []): Builder|array
    {
        try {
            // Select
            $query->when(!empty($parameters['select']), function ($query) use ($parameters) {
                $query->select($parameters['select']);
            });

            // Relations
            $query->when(!empty($parameters['relations']), function ($query) use ($parameters) {
                $query->with($parameters['relations']);
            });

            // Basic where
            $query->when(!empty($parameters['where']), function ($query) use ($parameters) {
                foreach ($parameters['where'] as $condition) {
                    if (is_array($condition)) {
                        $query->where(...$condition);
                    }
                }
            });

            // whereIn
            $query->when(!empty($parameters['whereIn']), function ($query) use ($parameters) {
                foreach ($parameters['whereIn'] as $condition) {
                    if (count($condition) === 2) {
                        [$column, $values] = $condition;
                        $query->whereIn($column, $values);
                    }
                }
            });

            // whereNotIn
            $query->when(!empty($parameters['whereNotIn']), function ($query) use ($parameters) {
                foreach ($parameters['whereNotIn'] as $condition) {
                    if (count($condition) === 2) {
                        [$column, $values] = $condition;
                        $query->whereNotIn($column, $values);
                    }
                }
            });

            // whereNull
            $query->when(!empty($parameters['whereNull']), function ($query) use ($parameters) {
                foreach ($parameters['whereNull'] as $column) {
                    $query->whereNull($column);
                }
            });

            // whereNotNull
            $query->when(!empty($parameters['whereNotNull']), function ($query) use ($parameters) {
                foreach ($parameters['whereNotNull'] as $column) {
                    $query->whereNotNull($column);
                }
            });

            // whereBetween
            $query->when(!empty($parameters['whereBetween']), function ($query) use ($parameters) {
                foreach ($parameters['whereBetween'] as $condition) {
                    if (count($condition) === 2) {
                        [$column, $range] = $condition;
                        $query->whereBetween($column, $range);
                    }
                }
            });

            // orWhere
            $query->when(!empty($parameters['orWhere']), function ($query) use ($parameters) {
                foreach ($parameters['orWhere'] as $condition) {
                    if (is_array($condition)) {
                        $query->orWhere(...$condition);
                    }
                }
            });

            // Search
            $query->when(!empty($parameters['search']), function ($query) use ($parameters) {

                $searchTerm = $parameters['search']['search'];
                $columns    = $parameters['search']['columns'];

                $query->where(function (Builder $q) use ($searchTerm, $columns) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'LIKE', "%{$searchTerm}%");
                    }
                });
            });

            // Order
            if (!empty($parameters['orderBy'])) {
                foreach ($parameters['orderBy'] as $order) {
                    [$column, $direction] = $order;
                    $query->orderBy($column, $direction ?? 'asc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

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
    protected function handleError(\Throwable $e): array
    {
        return ['error' => $e->getMessage(), 'code' => 500];
    }
} 