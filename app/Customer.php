<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'job_title',
        'email',
        'first_name',
        'registered_since',
        'phone',
        'created_at',
        'updated_at'
    ];
}
