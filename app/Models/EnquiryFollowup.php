<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnquiryFollowup extends Model
{
    use HasFactory;
    public $table = 'enquiry_followup';
    protected $fillable = [
        'userId',
        'enquiry_id',
        'message',
    ];
}
