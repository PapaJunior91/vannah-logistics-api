<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
        // validate form data
        $validate = Validator::make($request->all(), [
           'company_name'    => 'required|string|unique:companies',
           'company_phone'         => 'required|string|unique:companies',
           'company_email'  => 'nullable|string|unique:companies',
           'company_address'     => 'required|string',
           'company_logo'     => 'nullable|file',
       ])->stopOnFirstFailure(true);
   
       // send response in case validation fails
       if ($validate->fails())
           return AppHelper::instance()->apiResponse(
               false, 
               $validate->errors()->first(), 
               '', 
               Response::HTTP_UNPROCESSABLE_ENTITY
           );

        $company_info = $request->all();

        if($request->hasFile('company_logo'))
           $company_info['company_logo'] = AppHelper::instance()->fileUpload($request->company_logo, 'Company logo');
   
       Company::create($company_info);
   
       return AppHelper::instance()->apiResponse(
           true,
           'Company Created Successfully',
           '',
           Response::HTTP_OK
       );
    }

    public function getCompany($id)
    {
        $company_info = Company::where('id', $id)->first();
        return AppHelper::instance()->apiResponse(
            true,
            'Company Info Retrieved Successfully',
            $company_info
        );
    }
}
