<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductEnquiry extends Model
{
    use HasFactory;
    public $table = 'productenquiry';
    protected $fillable = [
        'name',
        'message',
        'email',
    ];

}
