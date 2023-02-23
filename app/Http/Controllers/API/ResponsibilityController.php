<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $responsibilityQuery = Responsibility::query();

        //Get single data 
        if($id)
        {
            $responsibility = $responsibilityQuery->find($id);

            if($responsibility) {
                return ResponseFormatter::success($responsibility, 'Responsibility found');
            }
            return ResponseFormatter::error('Responsibility not found', 404);
        }

        // list all responsibility by user
        // $companies = Responsibility::with(['users']);

        // list all responsibility by company
        //Get multiple data
        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilities->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'responsibilities found'
        );
    }

    public function create(CreateResponsibilityRequest $request)
    {

        try {
    
            //Create responsibility
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if(!$responsibility) {
                throw new Exception('Responsibility not created');
            }

            //Attach responsibility to user
            // $user = User::find(Auth::id());
            // $user->companies()->attach($responsibility->id);
    
            //Load users at responsibility
            // $responsibility->load('users');

            return ResponseFormatter::success($responsibility, 'Responsibility created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }


    public function destroy($id)
    {
        try {
            //Get responsibility
            $responsibility = Responsibility::find($id);

            //TODO : Check if responsibility is owned by user

            //Check if responsibility exists
            if(!$responsibility) {
                throw new Exception('Responsibility not found');
            }

            //Delete responsibility
            $responsibility->delete();

            return ResponseFormatter::success('Responsibility deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

}
