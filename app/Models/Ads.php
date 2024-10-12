<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ads extends Model
{
    use HasFactory;
    protected $table='ads';
    protected $fillable=[
        'user_id',
        'title',
        'description',
        'image',

    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
