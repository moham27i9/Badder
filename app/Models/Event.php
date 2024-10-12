<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $table='events';
    protected $fillable=[
        'name',
        'type',
        'end_date',
        'description',
        'location',
        'image',
        'start_date',
    ];

    public function subscribe(){
        return $this->hasMany(Subscribe::class);
    }

    public function react(){
        return $this->hasMany(React::class);
    }
    public function gallery(){
        return $this->hasMany(Gallery::class);
    }
}
