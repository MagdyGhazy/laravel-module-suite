<?php

namespace Ghazym\ModuleBuilder\Controllers;

use App\Http\Controllers\Controller;
use Ghazym\ModuleBuilder\Requests\StoreRoleRequest;
use Ghazym\ModuleBuilder\Requests\UpdateRoleRequest;
use Ghazym\ModuleBuilder\Requests\UpdatePermissionRequest;
use Ghazym\ModuleBuilder\Services\RoleService;
use Ghazym\ModuleBuilder\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    use ResponseTrait;

    protected RoleService $service;
    protected string $key;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
        $this->key = 'role';
    }

    /**
     * Display a listing of roles.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = $this->service->index();
        return !isset($data['error']) ? $this->successResponse($data, 'All ' . $this->key . 's retrieved successfully') : $this->errorResponse('Cannot fetch ' . $this->key . 's', 404, $data['error']);
    }

    /**
     * Display the specified role.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->service->show($id);
        return !isset($data['error']) ? $this->successResponse($data, $this->key . ' details retrieved successfully') : $this->notFoundResponse('Cannot fetch ' . $this->key);
    }

    /**
     * Store a newly created role.
     *
     * @param StoreRoleRequest $request
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $this->service->store($request->validated());
        return !isset($data['error']) ? $this->successResponse($data, $this->key . ' created successfully', 201) : $this->errorResponse('Cannot create ' . $this->key, 400, $data['error']);
    }

    /**
     * Update the specified role.
     *
     * @param UpdateRoleRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $data = $this->service->update($request->validated(), $id);
        return !isset($data['error']) ? $this->successResponse($data, $this->key . ' updated successfully') : $this->errorResponse('Cannot update ' . $this->key, 400, $data['error']);
    }

    /**
     * Remove the specified role.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $data = $this->service->destroy($id);
        return !isset($data['error']) ? $this->successResponse($data, $this->key . ' deleted successfully') : $this->errorResponse('Cannot delete ' . $this->key, 400, $data['error']);
    }

    public function allPermissions()
    {
        $data = $this->service->allPermissions();
        return !isset($data['error']) ? $this->successResponse($data, 'All Permissions') : $this->errorResponse( 404, 'Cannot fetch Permissions', $data['error']);
    }


    public function updatePermission(UpdatePermissionRequest $request, $id)
    {
        $data = $this->service->updatePermission($request->validated(), $id);
        return !isset($data['error']) ? $this->successResponse($data, 'Permission updated successfully') : $this->errorResponse( 404, 'Cannot update Permission', $data['error']);
    }
} 