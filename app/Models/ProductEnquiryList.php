<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEnquiryList extends Model
{
    use HasFactory;
    public $table = 'productenquire_list';
    protected $fillable = [
        'enquiry_id',
        'product_id',
        'quantity'
    ];

}
