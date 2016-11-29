<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SyapiCreatedLog extends Model
{
    protected $table = 'syapi_created_logs';

    public $timestamps = false;

    protected $guarded = ['id'];
}
