<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;
    protected $table='ratings';
    protected $fillable=[
        'ratedUser_id',
        'ratingUser_id',
        'comment',
        'rate',

    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

