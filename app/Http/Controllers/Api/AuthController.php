<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Company;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {        
         // validate data
        $user_ceredentials = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ])->stopOnFirstFailure(true);

        if ($user_ceredentials->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $user_ceredentials->errors()->first(), '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

        if (!Auth::attempt($request->all()))
            return AppHelper::instance()->apiResponse(false, 'Wrong Username Or Password');

        $user = Auth::user();

        $user['company_info'] = Company::where('id', $user->company_id)->first();
        $user['today_deliveries_count'] = Delivery::where('arrival_date', Carbon::now()->toDateString())->count();

        User::where('id', $user->id)->update([
            'last_login' => Carbon::now()
        ]);

        return AppHelper::instance()->apiResponse(
            true, 
            'Login Successful',
            $user
        );

    }
}
