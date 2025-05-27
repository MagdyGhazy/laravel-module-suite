<?php

namespace Ghazym\LaravelModuleSuite\Services;

use Ghazym\LaravelModuleSuite\Traits\RepositoryTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
            'search' => $search ? ['search' => $search , 'columns' => ['name']] : null,
        ];

        $query = $this->query($this->model, $parameters);

        return $query->paginate($perPage);
    }

    /**
     * Get single role by ID
     *
     * @param int $id
     * @return Model|array|null
     */
    public function show(int $id): Model|array|null
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
     * @return Model|array
     */
    public function store(array $request): Model|array
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
     * @return Model|array|null
     */
    public function update(array $request, int $id): Model|array|null
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
     * Get all permissions
     *
     * @return Collection|array
     */

    public function allPermissions(): Collection|array
    {
        $search = request()->get('search');

        $parameters = [
            'select'    => ['id', 'name', 'description'],
            'search' => $search ? ['search' => $search , 'columns' => ['name', 'description']] : null,
        ];

        $permissionModelClass = config('laravel-module-suite.permissions.model');
        return $this->getAll(new $permissionModelClass(), $parameters);
    }

    /**
     * Update existing Permission
     *
     * @param array $request
     * @param int $id
     * @return Model|array|null
     */
    public function updatePermission(array $request, int $id): Model|array|null
    {
        $permissionModelClass = config('laravel-module-suite.permissions.model');
        return $this->edit(new $permissionModelClass(), $request, $id);
    }
}
