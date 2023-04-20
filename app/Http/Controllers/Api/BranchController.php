<?php

namespace App\Http\Controllers\Api;

use App\Models\Branch;

use App\Models\Region;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    //
    public function createBranch(Request $request)
    {
        // validate form data
        $validate = Validator::make($request->all(), [
            'branch_name'    => 'required|string',
            'region'         => 'required|string',
            'manager_id'  => 'nullable|int',
            'phone'     => 'required|string|unique:branches',
            'email'     => 'nullable|string|unique:branches',
        ])->stopOnFirstFailure(true);

        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

        Branch::create($request->all());

        return AppHelper::instance()->apiResponse(
            true,
            'Branch Created Successfully',
            '',
            Response::HTTP_OK
        );
    }

    public function getBranches()
    {
        $branches = Branch::leftjoin('users', 'branches.manager_id', '=', 'users.id')
        ->select('users.first_name', 'users.last_name', 'branches.*', 'branches.id as branch_id')
        ->get();

        return AppHelper::instance()->apiResponse(
            true,
            'Data Retrieved Successfully',
            $branches
        );
    }

    public function getRegions()
    {
        $regions = Region::orderBy('region_name')->get();

        return AppHelper::instance()->apiResponse(
            true,
            '',
            $regions
        );
    }
}
