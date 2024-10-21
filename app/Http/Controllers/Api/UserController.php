<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repository\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    private $user;

    public function __construct(UserRepositoryInterface $user)
    {
        $this->user = $user;
    }



    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
            return $this->user->read();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  UserRequest $request
     * @return JsonResponse
     */
    public function store(UserRequest $request)
    {
        $validated = $request->except('role_id');
        $validated['password'] = Hash::make($validated['password']);
        $user = $this->user->create($validated);
        $role = Role::query()->find($request->role_id);
        $user->syncRoles($role->name);
        return $this->handleResponse($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
       return $this->user->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(UserRequest $request, $id)
    {
        $validated = $request->except('role_id');
        if($request->has('password') && strlen($validated['password']) > 0) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $this->user->update($id, $validated);
        $user = User::query()->find($id);
        $role = Role::query()->find($request->role_id);
        $user->syncRoles($role->name);
        return $this->handleResponse($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return $this->user->delete($id);
    }
}
