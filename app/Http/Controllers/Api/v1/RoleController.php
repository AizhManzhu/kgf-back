<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\RoleStoreRequest;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

    public function __construct() {
//        $this->middleware('permission:');
    }

    public function index(): JsonResponse
    {
        $roles = Role::query()->with('permissions')->get();
        return $this->handleResponse($roles);
    }

    public function show($id): JsonResponse
    {
        $role = Role::query()->with('permissions')->find($id);
        return $this->handleResponse($role);
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $roleData = [
            'name' =>  $request->get('name'),
            'guard_name' => 'web'
        ];
        $role = Role::query()->create($roleData);
        if ($request->has('permissions'))
        {
            $role->givePermissionTo($request->only('permissions')['permissions']);
        }
        return $this->handleResponse($role);
    }

    public function update(RoleStoreRequest $request, $id): JsonResponse
    {
        $role = Role::query()->find($id)->update($request->validated());
        return $this->handleResponse($role);
    }

    public function destroy($id): JsonResponse
    {
        Role::query()->find($id)->delete();
        return $this->handleResponse(true);
    }
}
