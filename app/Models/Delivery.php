<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 
        'tracking_code', 
        'from_branch_id', 
        'to_branch_id', 
        'sender_id', 
        'reciever_id', 
        'courier_id', 
        'package_value', 
        'delivery_status', 
        'arrival_date', 
        'description', 
        'payment_option', 
        'amount_paid',
        'created_by'
        
    ];
}
