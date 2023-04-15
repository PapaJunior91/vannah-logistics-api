<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    //
    public function createClient(Request $request)
    {
        // validate form data
        $validate = Validator::make($request->all(), [
            'first_name'    => 'required|alpha',
            'last_name' => 'nullable|alpha',
            'phone'     => 'required|string',
            'email'     => 'nullable|string',
            'address'   => 'nullable|string',
            'region'    => 'nullable|string',
        ])->stopOnFirstFailure(true);

        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

        
        Client::create($request->all());

        return AppHelper::instance()->apiResponse(
            true,
            'Client Created Successfully',
            '',
            Response::HTTP_OK
        );
    }

    public function getClients()
    {
        $clients = Client::all();

        return AppHelper::instance()->apiResponse(
            true,
            'Client Retrieved Successfully',
            $clients
        );
    }

    public function updateClient(Request $request, $id)
    {
         // validate form data
         $validate = Validator::make($request->all(), [
            'first_name'    => 'nullable|alpha',
            'last_name' => 'nullable|alpha',
            'phone'     => 'nullable|string',
            'email'     => 'nullable|string',
            'address'   => 'nullable|string',
            'region'    => 'nullable|string',
        ])->stopOnFirstFailure(true);
      
        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            ); 

        Client::where('id', $id)->update($request->all());

        return AppHelper::instance()->apiResponse(
            true,
            'Client Updated Successfully',
            '',
            Response::HTTP_OK
        );
        
    }

    public function archiveClient(String $id)
    {
        Client::where('id', $id)->update([
            'status' => 'inactive'
        ]);

        return AppHelper::instance()->apiResponse(
            true,
            'Client Archived Successfully'
        );
    }


}
