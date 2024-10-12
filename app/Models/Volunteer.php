<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $table='volunteers';
    protected $fillable=[
        'studding',
        'user_id',
        'skills',
        'vol_Date',
        'availableTime',

    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

        // في ملف Volunteer.php

public function getAvailableTimeAttribute($value)
{
    return json_decode($value, true);
}

public function setAvailableTimeAttribute($value)
{
    $this->attributes['availableTime'] = json_encode($value);
}

}
