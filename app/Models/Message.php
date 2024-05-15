<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
class Message extends Model
{
    use HasFactory;
    protected $table = 'messages';
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'path',
        'type',
        'location_link'
    ];
    protected $allowedSorts = [
       
        'created_at',
        'updated_at'
    ];
    protected $guarded = [];
    public function getMessageAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function setMessageAttribute($value)
    {
        $this->attributes['message'] = Crypt::encryptString($value);
    }

    public function getLocationLinkAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function setLocationLinkAttribute($value)
    {
        $this->attributes['location_link'] = Crypt::encryptString($value);
    }

    public function getPahtAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function setPahtAttribute($value)
    {
        $this->attributes['path'] = Crypt::encryptString($value);
    }
}
