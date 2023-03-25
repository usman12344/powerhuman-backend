<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::withCount('employees');

        //Get single data 
        if($id)
        {
            $team = $teamQuery->find($id);

            if($team) {
                return ResponseFormatter::success($team, 'Team found');
            }
            return ResponseFormatter::error('Team not found', 404);
        }

        // list all team by user
        // $companies = Team::with(['users']);

        // list all team by company
        //Get multiple data
        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'teams found'
        );
    }

    public function create(CreateTeamRequest $request)
    {

        try {
            // Upload icon
            if($request->hasFile('icon')){
                $path = $request->file('icon')->store('public/icons');
            }
    
            //Create team
            $team = Team::create([
                'name' => $request->name,
                'icon' => isset($path) ? $path : '',
                'company_id' => $request->company_id,
            ]);

            if(!$team) {
                throw new Exception('Team not created');
            }

            //Attach team to user
            // $user = User::find(Auth::id());
            // $user->companies()->attach($team->id);
    
            //Load users at team
            // $team->load('users');

            return ResponseFormatter::success($team, 'Team created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            //Get team
            $team = Team::find($id);

            // check if team not  exists
            if(!$team){
                throw new Exception('Team not found');
            }

            //Upload icon
            if($request->hasFile('icon')){
                $path = $request->file('icon')->store('public/icons');
            }

            //Update team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($team, 'Team updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            //Get team
            $team = Team::find($id);

            //TODO : Check if team is owned by user

            //Check if team exists
            if(!$team) {
                throw new Exception('Team not found');
            }

            //Delete team
            $team->delete();

            return ResponseFormatter::success('Team deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
