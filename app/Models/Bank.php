<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $connection = 'bkd';
    //table name
    protected $table = 'bank';
    //primary key
    protected $primaryKey = 'bank_id';
    //set auto incrementing for PK
    public $incrementing = true;

    public function virtualAccounts(){
        return $this->hasMany('App\Models\UserVirtualAccount','bank_id','bank_id');
    }
}
