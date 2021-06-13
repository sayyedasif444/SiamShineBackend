<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public $table = 'products';
    protected $fillable = [
        'product_u_id',
        'product_name',
        'product_desc',
        'product_price',
        'product_price_range',
        'userId',
    ];
}
