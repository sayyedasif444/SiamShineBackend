<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributes extends Model
{
    use HasFactory;
    public $table = 'product_attritubes';
    protected $fillable = [
        'product_id',
        'attribute_id'
    ];
}
