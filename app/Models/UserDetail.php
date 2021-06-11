<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;
    public $table = 'userdetails';

    protected $fillable = [
        'first_name',
        'last_name',
        'additional_email',
        'additional_number',
        'gender',
        'userId'
    ];
}
