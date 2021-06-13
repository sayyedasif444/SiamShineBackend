<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageDeck extends Model
{
    use HasFactory;
    public $table = 'image_deck';
    protected $fillable = [
        'image_path',
        'image_name',
    ];
}
