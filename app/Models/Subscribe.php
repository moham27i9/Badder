<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscribe extends Model
{
    use HasFactory;
    protected $table='subscribes';
    protected $fillable=[
        'user_id',
        'event_id',
        'benefit',
        'volunteering',
        'request_status_ben',
        'request_status_vol',


    ];

    // relationship for user and event

    public function event(){
        return $this->belongsTo(Event::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
