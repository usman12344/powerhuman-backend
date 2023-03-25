<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $company_id = $request->input('company_id');
        $with_responsibilities = $request->input('with_responsibilities', false);

        $roleQuery = Role::withCount('employees');
        // list all role by user

        // $companies = Role::all();

        $roles = $roleQuery;
        
        //Get single data 
        if($id)
        {
            $role = $roleQuery->with('responsibilities')->find($id);

            if($role) {
                return ResponseFormatter::success($role, 'Role found');
            }
            return ResponseFormatter::error('Role not found', 404);
        }
        
        

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        if($with_responsibilities) {
            $roles->with('responsibilities');
        }

        if($company_id){
            // list all role by company
        //Get multiple data
         $roles = $roleQuery->where('company_id', $request->company_id);
        }


        // if( $roleQuery === '') {

        //     $role = $companies;
        //     if($role) {
        //         return ResponseFormatter::success($role, 'Role found');
        //     }
        //     return ResponseFormatter::error('Role not found', 404);
        // }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'roles found'
        );

        
    }



    public function create(CreateRoleRequest $request)
    {

        try {
    
            //Create role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if(!$role) {
                throw new Exception('Role not created');
            }

            //Attach role to user
            // $user = User::find(Auth::id());
            // $user->companies()->attach($role->id);
    
            //Load users at role
            // $role->load('users');

            return ResponseFormatter::success($role, 'Role created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            //Get role
            $role = Role::find($id);

            // check if role not  exists
            if(!$role){
                throw new Exception('Role not found');
            }


            //Update role
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($role, 'Role updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            //Get role
            $role = Role::find($id);

            //TODO : Check if role is owned by user

            //Check if role exists
            if(!$role) {
                throw new Exception('Role not found');
            }

            //Delete role
            $role->delete();

            return ResponseFormatter::success('Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

}
