<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneCode extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
}
