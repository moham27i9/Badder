<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->integer('phone');
            $table->string('image')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        $this->SuperAdmin();
    }

    private function SuperAdmin(){



        $data =[[
            'first_name' => 'abd',
            'last_name' => 'abd',
            'email' => 'aa24@gmail.com',
            'role'=>'superAdmin',
            'phone'=>'0911111111',
            'password' => bcrypt('12341234'),

        ],
       ];

    Illuminate\Support\Facades\DB::table('users')->insert($data);
    }


    public function down()
    {
        Schema::dropIfExists('users');
    }
}
