<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;
    public $table = 'stockitem';
    protected $fillable = [
        'product_id',
        'number_of_items',
    ];
}
