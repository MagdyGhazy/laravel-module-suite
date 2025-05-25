<?php

namespace Ghazym\LaravelModuleSuite\Services;

use Ghazym\LaravelModuleSuite\Models\Role;
use Ghazym\LaravelModuleSuite\Traits\RepositoryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleService
{
    use RepositoryTrait;

    protected $model;

    public function __construct()
    {
        $modelClass = config('laravel-module-suite.roles.model');
        $this->model = new $modelClass();
    }

    /**
     * Get paginated list of roles
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        $search = request()->get('search');
        $perPage = request()->get('limit', 10);

        $parameters = [
            'select' => ['id', 'name'],
        ];

        $query = $this->query($this->model, $parameters);

        if ($search) {
            $query = $this->search($query, $search);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get single role by ID
     *
     * @param int $id
     * @return Role|array|null
     */
    public function show(int $id): Role|array|null
    {
        $parameters = [
            'select' => ['id', 'name'],
            'relations' => ['permissions:id,name,description'],
        ];

        return $this->getOne($this->model, $id, $parameters);
    }

    /**
     * Create new role
     *
     * @param array $request
     * @return Role|array
     */
    public function store(array $request): Role|array
    {
        $role = $this->create($this->model, $request);

        if (isset($request['permissions'])) {
            $role->syncPermissions($request['permissions']);
        }

        return $role;
    }

    /**
     * Update existing role
     *
     * @param array $request
     * @param int $id
     * @return Role|array|null
     */
    public function update(array $request, int $id): Role|array|null
    {
        $role = $this->edit($this->model, $request, $id);

        if ($role && isset($request['permissions'])) {
            $role->syncPermissions($request['permissions']);
        }

        return $role;
    }

    /**
     * Delete role
     *
     * @param int $id
     * @return bool|array
     */
    public function destroy(int $id): bool|array
    {
        return $this->delete($this->model, $id);
    }

    /**
     * Filter query by search term
     *
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    protected function search(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
            ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    public function allPermissions()
    {
        $search = request()->get('search');

        $parameters = [
            'select'    => ['id', 'name', 'description'],
        ];

        $permissionModelClass = config('laravel-module-suite.permissions.model');
        $query = $this->query(new $permissionModelClass(), $parameters);

        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        return $query->get();
    }

    public function updatePermission(array $request, int $id)
    {
        $permissionModelClass = config('laravel-module-suite.permissions.model');
        return $this->edit(new $permissionModelClass(), $request, $id);
    }
}
