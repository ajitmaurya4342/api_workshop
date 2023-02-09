<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id'.
        'payed',
        'created_at',
        'updated_at'
    ];
}
