<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Support\Facades\Validator;

class CourierController extends Controller
{
    //
    public function createCourier(Request $request)
    {
        // validate form data
        $validate = Validator::make($request->all(), [
            'courier_name'    => 'required|string|unique:couriers',
            'region'         => 'required|string',
            'phone'     => 'required|string|unique:couriers',
            'email'     => 'nullable|string|unique:couriers',
            'address'     => 'nullable|string',
        ])->stopOnFirstFailure(true);

        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

        Courier::create($request->all());

        return AppHelper::instance()->apiResponse(
            true,
            'Courier Created Successfully',
            '',
            Response::HTTP_OK
        );
    }

    public function getCouriers()
    {
        $couriers = Courier::all();

        return AppHelper::instance()->apiResponse(
            true,
            'Data Retrieved Successfully',
            $couriers
        );
    }
}
