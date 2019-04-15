<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $connection = 'bkd';
    //table name
    protected $table = 'master_wallet';
    //primary key
    protected $primaryKey = 'User_id';
    //set auto incrementing for PK
    public $incrementing = false;

    public function virtualAccounts(){
        return $this->hasMany('App\Models\UserVirtualAccount','User_id','user_id');
    }

    public function walletDetails(){
        return $this->hasMany('App\Models\WalletDetail','id','id');
    }
}
