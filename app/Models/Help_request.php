<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Help_request extends Model
{
    use HasFactory;

    protected $table='help_requests';
    protected $fillable=[
        'description ',
        'user_id',
        'city',
        'neighborhood',
        'street',

    ];
  

    public function user(){
        return $this->belongsTo(User::class);
    }


}
