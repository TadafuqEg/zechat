<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;



class Notification extends Model
{
    use HasFactory,SoftDeletes;
    public $imageCollection = 'notification-image';

    protected $table = 'notifications';
    protected $fillable = [
        
        'data',
        'type',
        'user_id',
        'seen'
    ];

    protected $allowedSorts = [
     
        'created_at',
        'updated_at'
    ];
}