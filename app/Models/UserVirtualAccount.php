<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVirtualAccount extends Model
{
    protected $connection = 'bkd';
    //table name
    protected $table = 'user_virtual_account';
    //primary key
    protected $primaryKey = 'user_id';
    //set auto incrementing for PK
    public $incrementing = false;

    public function wallet(){
        return $this->belongsTo('App\Models\Wallet','user_id','User_id'); // model, foreign_key, local_key
    }
}
