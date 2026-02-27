<?php

namespace Ghazym\LaravelModuleSuite\Controllers;

use App\Http\Controllers\Controller;
use Ghazym\LaravelModuleSuite\Requests\StoreRoleRequest;
use Ghazym\LaravelModuleSuite\Requests\UpdateRoleRequest;
use Ghazym\LaravelModuleSuite\Requests\UpdatePermissionRequest;
use Ghazym\LaravelModuleSuite\Services\RoleService;
use Ghazym\LaravelModuleSuite\Traits\ResponseTrait;
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
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->handleServiceResponse($this->service->index());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
    */
    public function show(int $id): JsonResponse
    {
        return $this->handleServiceResponse($this->service->show($id));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param {{ store_request }} $request
     * @return JsonResponse
    */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        return $this->handleServiceResponse($this->service->store($request->validated()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param {{ update_request }} $request
     * @param int $id
     * @return JsonResponse
    */
    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        return $this->handleServiceResponse($this->service->update($request->validated(), $id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
    */
    public function destroy(int $id): JsonResponse
    {
        return $this->handleServiceResponse($this->service->destroy($id));
    }

    public function allPermissions()
    {
        return $this->handleServiceResponse($this->service->allPermissions());
    }


    public function updatePermission(UpdatePermissionRequest $request, $id)
    {
        return $this->handleServiceResponse($this->service->updatePermission($request->validated(), $id));
    }
} 