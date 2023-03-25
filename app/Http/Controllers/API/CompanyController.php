<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $companiesQuery = Company::whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        })->with(['users']);

        //Get single data
        if($id)
        {
            $company = $companiesQuery->find($id);

            if($company) {
                return ResponseFormatter::success($company, 'Company found');
            }
            return ResponseFormatter::error('Company not found', 404);
        }

        // list all company by user
        // $companies = Company::with(['users']);

        // list all company by user
        //Get multiple data
        $companies = $companiesQuery;

        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {

        try {
            // Upload logo
            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }
    
            //Create company
            $company = Company::create([
                'name' => $request->name,
                'logo' => isset($path) ? $path : '',
            ]);

            if(!$company) {
                throw new Exception('Company not created');
            }

            //Attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);
    
            //Load users at company
            $company->load('users');

            return ResponseFormatter::success($company, 'Company created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            //Get company
            $company = Company::find($id);

            // check if company not  exists
            if(!$company){
                throw new Exception('Company not found');
            }

            //Upload logo
            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }

            //Update company
            $company->update([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo,
            ]);

            return ResponseFormatter::success($company, 'Company updated');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
    
}
