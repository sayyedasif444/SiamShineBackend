<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Useraddress extends Model
{
    use HasFactory;
    public $table = 'useraddress';

    protected $fillable = [
        'contact_person',
        'contact_phone',
        'pincode',
        'address',
        'city',
        'state',
        'landmark',
        'alternate_number',
        'address_type',
        'isPrimary',
        'userId',
    ];
}
