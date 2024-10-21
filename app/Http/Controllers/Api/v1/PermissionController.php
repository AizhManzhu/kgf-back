<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Role;
use App\Http\Requests\v1\PermissionStoreRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller {

    public function index(): JsonResponse
    {
        $permissions = Permission::query()->get();
        return $this->handleResponse($permissions);
    }

    public function show($id): JsonResponse
    {
        $permission = Permission::query()->find($id);
        return $this->handleResponse($permission);
    }

    public function store(PermissionStoreRequest $permissionStoreRequest): JsonResponse
    {
        $permission = Permission::query()->create($permissionStoreRequest->validated());
        return $this->handleResponse($permission);
    }

    public function update(PermissionStoreRequest $permissionStoreRequest, $id): JsonResponse
    {
        $permission = Permission::query()->find($id)->update($permissionStoreRequest->validated());
        return $this->handleResponse($permission);
    }

    public function destroy($id): JsonResponse
    {
        Permission::query()->find($id)->delete();
        return $this->handleResponse('');
    }

    public function set($roleId, $permission)
    {
        $role = \Spatie\Permission\Models\Role::find($roleId);
        $role->givePermissionTo($permission);
    }

    public function remove($roleId, $permission)
    {
        $role = \Spatie\Permission\Models\Role::find($roleId);
        $role->revokePermissionTo($permission);
    }
}
