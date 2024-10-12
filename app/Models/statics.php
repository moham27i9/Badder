<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class statics extends Model
{
    use HasFactory;
    protected $table='statics';
    protected $fillable=[
        'user_id',
        'event_id',
        'description',
        'count_volunteer',
        'count_benefit',

    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
