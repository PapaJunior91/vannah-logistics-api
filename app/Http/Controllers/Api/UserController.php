<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;



class UserController extends Controller
{
    //
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
