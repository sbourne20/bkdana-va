<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequestLog extends Model
{
    protected $connection = 'bkd_va';
    //table name
    protected $table = 'payment_request_log';
    //primary key
    protected $primaryKey = 'id';
    //set auto incrementing for PK
    public $incrementing = true;
}
