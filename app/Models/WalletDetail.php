<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletDetail extends Model
{
    protected $connection = 'bkd';
    //table name
    protected $table = 'detail_wallet';
    //primary key
    protected $primaryKey = 'Detail_wallet_id';
    //set auto incrementing for PK
    public $incrementing = false;

    public function wallet(){
        return $this->belongsTo('App\Models\Wallet','id','id');
    }
}
