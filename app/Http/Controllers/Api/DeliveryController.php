<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Delivery;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    //
    public function createDelivery(Request $request)
    {
        //validate form data
        $validate = Validator::make($request->all(), [
            'from_branch_id'    => 'required|int',
            'to_branch_id'      => 'required|int',
            'sender_first_name'   => 'required|string',
            'sender_last_name'   => 'required|string',
            'sender_phone'   => 'required|string',
            'sender_email'   => 'nullable|string',
            'reciever_last_name'  => 'required|string',
            'reciever_first_name'  => 'required|string',
            'reciever_phone' => 'required|string',
            'reciever_email' => 'nullable|string',
            'courier_id'     => 'nullable|int',
            'package_value'  => 'nullable|int',
            'delivery_status'  => 'nullable|string',
            'arrival_date'   => 'nullable|string',
            'description'    => 'nullable|string',
            'payment_option' => 'nullable|string',
            'amount_paid'     => 'nullable|int',
            'created_by'     => 'required|int',
        ])->stopOnFirstFailure(true);

        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            );

        $sender_details = [
            'first_name' => $request->sender_first_name,
            'last_name' => $request->sender_last_name,
            'phone' => $request->sender_phone,
            'email' => $request->sender_email
        ];

        $reciever_details = [
            'first_name' => $request->reciever_first_name,
            'last_name' => $request->reciever_last_name,
            'phone' => $request->reciever_phone,
            'email' => $request->reciever_email
        ];
        
        DB::transaction(function() use($sender_details, $reciever_details, $request){

            //check wether client exists and update if not insert
            $sender = Client::updateOrCreate($sender_details, $sender_details); 
            $reciever = Client::updateOrCreate($reciever_details, $reciever_details);

            $delivery_details = [
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id' => $request->to_branch_id,
                'courier_id' => $request->courier_id,
                'package_value' => $request->package_value,
                'arrival_date' => $request->arrival_date,
                'delivery_status' => $request->delivery_status,
                'amount_paid' => $request->amount_paid,
                'payment_option' => $request->payment_option,
                'description' => $request->description,
                'sender_id' => $sender->id,
                'reciever_id' => $reciever->id,
                'created_by' => $request->created_by
            ];

            // generate tracking code
            $delivery_details['tracking_code'] = $this->generateTrackingCode($request->from_branch_id, $request->to_branch_id);

            Delivery::create($delivery_details);

        });

        $reciever = [
            $request->sender_phone,
            $request->reciever_phone
        ];

        // send notification
        $is_notification_sent = $this->sendSMSNotification($request->notification_message, $request->reciever_phone);

        if($is_notification_sent == 0){
            $response_message = "SMS Sent Successfully";
        }else{
            $response_message = "Failed To Sent SMS";
        }

        return AppHelper::instance()->apiResponse(
            true,
            'Delivery Created Successfully, '.$response_message,
        );

    }

    public function getDeliveries()
    {
        $deliveries = Delivery::leftjoin('clients as sender_table', 'sender_table.id', '=', 'deliveries.sender_id')
        ->leftjoin('clients as reciever_table', 'reciever_table.id', '=', 'deliveries.reciever_id')
        ->leftjoin('branches as from_branch_table', 'from_branch_table.id', '=', 'deliveries.from_branch_id')
        ->leftjoin('branches as to_branch_table', 'to_branch_table.id', '=', 'deliveries.to_branch_id')
        ->leftjoin('couriers', 'couriers.id', '=', 'deliveries.courier_id')
        ->select(
            'sender_table.id as sender_id',
            'sender_table.first_name as sender_first_name',
            'sender_table.last_name as sender_last_name',
            'sender_table.phone as sender_phone',
            'sender_table.email as sender_email',
            'reciever_table.id as reciever_id',
            'reciever_table.first_name as reciever_first_name',
            'reciever_table.last_name as reciever_last_name',
            'reciever_table.phone as reciever_phone',
            'reciever_table.email as reciever_email',
            'from_branch_table.branch_name as from_branch_name',
            'from_branch_table.region as from_region',
            'to_branch_table.branch_name as to_branch_name',
            'to_branch_table.region as to_region','couriers.phone',
            'courier_name', 'couriers.address as courier_address', 
            'couriers.region as courier_region', 'couriers.email as courier_email',
            'deliveries.*'
        )
        ->get();

        $current_date = Carbon::now();

        foreach($deliveries as $delivery)
        {
            $arrival_date = $delivery['arrival_date'];
            $delivery_status = $delivery['delivery_status'];

            if($current_date > $arrival_date && $delivery_status == 'on transit')
                $delivery->delivery_status = "delayed";

        }

        return AppHelper::instance()->apiResponse(
            true,
            'Data Retrieved Successfully',
            $deliveries
        );
    }

    public function filterDeliveries(Request $request)
    {
        $filters = AppHelper::instance()->customFilter($request->all(), 'deliveries');

        $conditions = $filters[0]; 
        $start_date = $filters[1];
        $end_date   = $filters[2];

        $deliveries = Delivery::orWhere($conditions)
        ->leftjoin('clients as sender_table', 'sender_table.id', '=', 'deliveries.sender_id')
        ->leftjoin('clients as reciever_table', 'reciever_table.id', '=', 'deliveries.reciever_id')
        ->leftjoin('branches as from_branch_table', 'from_branch_table.id', '=', 'deliveries.from_branch_id')
        ->leftjoin('branches as to_branch_table', 'to_branch_table.id', '=', 'deliveries.to_branch_id')
        ->leftjoin('couriers', 'couriers.id', '=', 'deliveries.courier_id')
        ->select(
            'sender_table.id as sender_id',
            'sender_table.first_name as sender_first_name',
            'sender_table.last_name as sender_last_name',
            'sender_table.phone as sender_phone',
            'sender_table.email as sender_email',
            'reciever_table.id as reciever_id',
            'reciever_table.first_name as reciever_first_name',
            'reciever_table.last_name as reciever_last_name',
            'reciever_table.phone as reciever_phone',
            'reciever_table.email as reciever_email',
            'from_branch_table.branch_name as from_branch_name',
            'from_branch_table.region as from_region',
            'to_branch_table.branch_name as to_branch_name',
            'to_branch_table.region as to_region','couriers.phone',
            'courier_name', 'couriers.address as courier_address', 
            'couriers.region as courier_region', 'couriers.email as courier_email',
            'deliveries.*'
        )
        ->get();

        return AppHelper::instance()->apiResponse(
            true,
            'Data Retrieved Successfully',
            $deliveries
        );
    }

    public function updateDelivery(Request $request, $id)
    {
       //validate form data
        $validate = Validator::make($request->all(), [
            'from_branch_id'    => 'nullable|int',
            'to_branch_id'      => 'nullable|int',
            'sender_first_name'   => 'nullable|string',
            'sender_last_name'   => 'nullable|string',
            'sender_phone'   => 'nullable|string',
            'sender_email'   => 'nullable|string',
            'reciever_last_name'  => 'nullable|string',
            'reciever_first_name'  => 'nullable|string',
            'reciever_phone' => 'nullable|string',
            'reciever_email' => 'nullable|string',
            'courier_id'     => 'nullable|int',
            'package_value'  => 'nullable|int',
            'delivery_status'  => 'nullable|string',
            'arrival_date'   => 'nullable|string',
            'description'    => 'nullable|string',
            'payment_option' => 'nullable|string',
            'amount_paid'     => 'nullable|int',
        ])->stopOnFirstFailure(true);

        // send response in case validation fails
        if ($validate->fails())
            return AppHelper::instance()->apiResponse(
                false, 
                $validate->errors()->first(), 
                '', 
                Response::HTTP_UNPROCESSABLE_ENTITY
            ); 

        $sender_details = [
            'first_name' => $request->sender_first_name,
            'last_name' => $request->sender_last_name,
            'phone' => $request->sender_phone,
            'email' => $request->sender_email
        ];

        $reciever_details = [
            'first_name' => $request->reciever_first_name,
            'last_name' => $request->reciever_last_name,
            'phone' => $request->sender_phone,
            'email' => $request->sender_email
        ];
        
        DB::transaction(function() use($sender_details, $reciever_details, $request, $id){

            //update sender and reciver info
            Client::where('id', $request->sender_id)->update($sender_details); 
            Client::where('id', $request->reciever_id)->update($reciever_details);

            $delivery_details = [
                'from_branch_id' => $request->from_branch_id,
                'to_branch_id' => $request->to_branch_id,
                'courier_id' => $request->courier_id,
                'package_value' => $request->package_value,
                'arrival_date' => $request->arrival_date,
                'delivery_status' => $request->delivery_status,
                'amount_paid' => $request->amount_paid,
                'payment_option' => $request->payment_option,
                'description' => $request->description,
            ];

            // generate tracking code
            $delivery_details['tracking_code'] = $this->generateTrackingCode($request->from_branch_id, $request->to_branch_id);

    
            Delivery::where('id', $id)->update(array_filter($delivery_details));

        });

        return AppHelper::instance()->apiResponse(
            true,
            'Delivery Updated Successfully',
        );
    
    }

    public function archiveDelivery(String $id)
    {
        $archive_delivery = Delivery::where('id', $id)->update([
            'status' => 'inactive'
        ]);

        return AppHelper::instance()->apiResponse(
            true,
            'Delivery Archived Successfully'
        );
    }

    public function getTotalIncome()
    {
        $total_income = Delivery::sum('amount_paid');

        return AppHelper::instance()->apiResponse(
            true,
            '',
            $total_income
        );

    }

    private function generateTrackingCode($from_branch_id, $to_branch_id)
    {
        $last_insert_id = Delivery::latest('id')->pluck('id')->first();
        $from_region = Branch::where('id', $from_branch_id)->pluck('region')->first();
        $to_region = Branch::where('id', $to_branch_id)->pluck('region')->first();
        return substr($from_region, 0, 3).'-'.substr($to_region, 0, 3).'-'. intval($last_insert_id) + 1;
    }

    private function sendSMSNotification($body=null, $reciever=null)
    {
        $basic  = new \Vonage\Client\Credentials\Basic("760b070e", "IgkytOQrWHi3f2QP");
        $client = new \Vonage\Client($basic);

        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS($reciever, "VANNAH LOGISTICS", $body)
        );
        
        $message = $response->current();
        
        // if ($message->getStatus() == 0) {
        //     echo "The message was sent successfully\n";
        // } else {
        //     echo "The message failed with status: " . $message->getStatus() . "\n";
        // }

         //send invitation msg
        //  $from     = 'VANNAH_LOGISTICS';
        //  $body     = 'MerryGoRound Group Invitation. '.$inviter->firstName.' '.$inviter->lastName.' invites you to join '.$group->groupName;
        //  $reciever = $request->phoneNumber;
 
        //  $apiKey    = '760b070e';
        //  $apiSecret = 'IgkytOQrWHi3f2QP';
 
        //  $basic  = new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret);
        //  $client = new \Vonage\Client($basic);
 
        //  $response = $client->sms()->send(
        //      new \Vonage\SMS\Message\SMS($reciever, $from, $body)
        //  );
         
        //  $message = $response->current();

         return $message->getStatus();
         
        //  if ($message->getStatus() == 0) {
        //      return response()->json(['status' => true, 'message' => 'Message sent successfully']);
        //  } else {
        //      return response()->json(['status' => false, 'message' => 'Message Not Sent']);             
        //  } 
    }
}
