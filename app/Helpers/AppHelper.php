<?php
namespace App\Helpers;

use App\Models\Otp;
use App\Models\User;
use App\Mail\MainMailable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class AppHelper
{
    public $start_date, $end_date;

    public function __construct()
    {
       $this->start_date = "";
       $this->end_date = "";
    }

    public function fileUpload($file, $folder)
    {
        $file_extension = $file->getClientOriginalExtension();
        $file_name = mt_rand().'.'.$file_extension;
        $file->storeAs($folder, $file_name, 'public');
        $file_path = 'storage/'.$folder.'/' . $file_name;
        return $file_path = env('APP_URL').'storage/'.$folder.'/' . $file_name;
    }

    public function apiResponse($success, $msg = '', $data = [], $status = null)
    {
        return response()->json([
            'success' => $success, 
            'status' => $status,
            'message' => $msg,
            'data' => $data
        ]);
    }
    
    public function customFilter($parameters, $tbl_name)
    {
        // create custom filter
        $parameters = array_filter($parameters);
        $keys =  array_keys($parameters);
        $values =  array_values($parameters);
        $length = count($parameters);

        $conditions = [];

        for ($i=0; $i<$length; $i++ )
        {
            $key = $tbl_name.'.'.$keys[$i];
            $val = $values[$i];

            if($keys[$i] == 'startDate'){
                $this->start_date = $val;
                continue;
            }
            
            if($keys[$i] == 'endDate'){
                $this->end_date = $val;
                continue;
            }

            $conditions[$key] =  $val;
        }
        
        return [$conditions, $this->start_date, $this->end_date];
    }
    
    public static function instance()
    {
        return new AppHelper();
    }

}