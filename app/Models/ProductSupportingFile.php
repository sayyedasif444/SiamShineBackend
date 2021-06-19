<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSupportingFile extends Model
{
    use HasFactory;
    public $table = 'product_supporting_files';
    protected $fillable = [
        'image_id',
        'product_id',
    ];

}
