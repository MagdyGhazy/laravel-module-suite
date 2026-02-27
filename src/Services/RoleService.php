<?php

namespace Ghazym\LaravelModuleSuite\Services;

use Ghazym\LaravelModuleSuite\Resources\RoleResource;
use Ghazym\LaravelModuleSuite\Services\ServiceResponse;
use Ghazym\LaravelModuleSuite\Traits\RepositoryTrait;
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
     * Get paginated list of records
     *
     * @return ServiceResponse
     */
    public function index(): ServiceResponse
    {
        $search = request()->get('search');
        $perPage = request()->get('limit', 10);

        $parameters = [
            'select' => ['id', 'name'],
            'search' => $search ? ['search' => $search , 'columns' => ['name']] : null,
        ];

        $query = $this->query($this->model, $parameters);
        $data = $query->paginate($perPage);
        return $this->wrap($data);
    }

    /**
     * Get single role by ID
     *
     * @param int $id
     * @return ServiceResponse
     */
    public function show(int $id): ServiceResponse
    {
        $parameters = [
            'select' => ['id', 'name'],
            'relations' => ['permissions:id,name,description'],
        ];

        return $this->wrap($this->getOne($this->model, $id, $parameters), 'Data retrieved');
    }

    /**
     * Create new record
     *
     * @param array $request
     * @return ServiceResponse
    */
    public function store(array $request): ServiceResponse
    {
        $role = $this->create($this->model, $request);

        if (isset($request['permissions'])) {
            $role->syncPermissions($request['permissions']);
        }

        return $this->wrap($role, 'Role created successfully');
    }

    /**
     * Update existing role
     *
     * @param array $request
     * @param int $id
     * @return ServiceResponse
     */
    public function update(array $request, int $id): ServiceResponse
    {
        $role = $this->edit($this->model, $request, $id);

        if ($role && isset($request['permissions'])) {
            $role->syncPermissions($request['permissions']);
        }

        return $this->wrap($role, 'Role updated successfully');
    }

    /**
     * Delete record
     *
     * @param int $id
     * @return ServiceResponse
    */
    public function destroy(int $id): ServiceResponse
    {
        $data = $this->delete($this->model, $id);
        if (is_array($data) && isset($data['error'])) {
            return ServiceResponse::error($data['error'], $data['code'] ?? 400);
        }
        return ServiceResponse::success($data, 'Deleted successfully');
    }

    /**
     * Get all permissions
     *
     * @return ServiceResponse
     */

    public function allPermissions(): ServiceResponse
    {
        $search = request()->get('search');

        $parameters = [
            'select'    => ['id', 'name', 'description'],
            'search' => $search ? ['search' => $search , 'columns' => ['name', 'description']] : null,
        ];

        $permissionModelClass = config('laravel-module-suite.permissions.model');
        return $this->wrap($this->getAll(new $permissionModelClass(), $parameters), 'Data retrieved');
    }

    /**
     * Update existing Permission
     *
     * @param array $request
     * @param int $id
     * @return ServiceResponse
     */
    public function updatePermission(array $request, int $id): ServiceResponse
    {
        $permissionModelClass = config('laravel-module-suite.permissions.model');
        return $this->wrap($this->edit(new $permissionModelClass(), $request, $id), 'Permission updated successfully');
    }

    /**
     * Handel output
     *
     * @param mixed $result
     * @param string $successMsg
     * @param int $successCode
     * @return ServiceResponse
    */
    protected function wrap($result, $successMsg = 'Success', $successCode = 200): ServiceResponse
    {
        if (is_array($result) && isset($result['error'])) {
            return ServiceResponse::error($result['error'], $result['code'] ?? 400);
        }

        if ($result !== null) {
            $result = ($result instanceof LengthAwarePaginator) ? RoleResource::collection($result) : new RoleResource($result);
        }

        return ServiceResponse::success($result, $successMsg, $successCode);
    }
}
