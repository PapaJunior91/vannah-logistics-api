<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Company;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;



class UserController extends Controller
{
    //
    public function registerUser(Request $request)
    {
        // validate form data
        $validate = Validator::make($request->all(), [
            'first_name'    => 'required|alpha',
            'last_name' => 'nullable|alpha',
            'username'  => 'required|string|unique:users',
            'password'  => 'required',
            'confirm_password'  => 'required|same:password|min:6',
            'phone'     => 'required|string',
            'address'     => 'required|string',
            'email'     => 'nullable|string',
            'company_name'      => 'nullable|string',
        ])->stopOnFirstFailure(true);

        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

        
        $user_info = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'role' => "admin",
            'password' => bcrypt(($request->password) ?? 'test@123')
        ];

        $company_info = [
            'company_name' => $request->company_name,
            'company_phone' => $request->phone,
            'company_email' => $request->email,
            'company_address' => $request->address,
        ];

        DB::transaction(function() use ($company_info, $user_info){
            Company::create($company_info);

            $user_info['company_id'] = Company::latest('id')->first()->id;

            User::create($user_info);

        });

        return AppHelper::instance()->apiResponse(
            true,
            'Registration Successfully',
            '',
            Response::HTTP_OK
        );
    }

    public function createUser(Request $request)
    {
        // validate form data
        $validate = Validator::make($request->all(), [
            'first_name'    => 'required|alpha',
            'last_name' => 'nullable|alpha',
            'username'  => 'required|string|unique:users',
            'password'  => 'nullable|string',
            'phone'     => 'required|string|unique:users',
            'email'     => 'nullable|string|unique:users',
            'branch_id'      => 'nullable|int',
        ])->stopOnFirstFailure(true);

        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

        
        $user_info = $request->all();
        $user_info['password'] = bcrypt(($request->password) ?? 'test@123');

        User::create($user_info);

        return AppHelper::instance()->apiResponse(
            true,
            'User Created Successfully',
            '',
            Response::HTTP_OK
        );
    }

    public function getUsers()
    {
        $users = User::leftjoin('branches', 'branches.id', '=', 'users.branch_id')
        ->select('users.id as user_id', 'users.*', 'branches.branch_name', 'region')
        ->get();

        return AppHelper::instance()->apiResponse(
            true,
            'Data Retrived Successfully',
            $users
        );
    }
}
