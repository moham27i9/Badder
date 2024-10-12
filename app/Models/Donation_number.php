<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donation_number extends Model
{
    use HasFactory;

    protected $table='donation_numbers';
    protected $fillable=[
        'phone',
        'user_id',

    ];


    public function user(){
        return $this->belongsTo(User::class);
    }
}
