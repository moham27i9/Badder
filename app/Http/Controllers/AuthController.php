<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    function register(Request $request)
    {
        $validate = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|regex:/^09[0-9]{8}$/',
            'email' => 'required|email',
            'password' => 'required|min:8|max:12',
      ]);
        $user=new User();
        $user->first_name=$validate['first_name'];
        $user->last_name=$validate['last_name'];
        $user->phone=$validate['phone'];
        $user->email=$validate['email'];
        $user->password=bcrypt($validate['password']);
        $image = $request->file('image');
        if($request->hasFile('image')){
                $fileName = date('dmY').time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path("/pictures"), $fileName);
                $user->image = $fileName;
            }
        $user->save();
        $token =JWTAuth::fromUser($user);
        return response()->json([
            'message'=>'success',
            'token'=>$token,
            'image' => $user->image ? url('pictures/' . $user->image) : null,
        ]);
        return response()->json(['message'=>'failed']);
    }

    public function login(Request $request)
    {
          $credentials = ['email'=>$request->email,'password'=>$request->password];
            if(auth()->attempt($credentials )){
                $user = auth()->user();
                $token =JWTAuth::fromUser($user);
                $access['id'] =   $user->id;
                return response()->json([
                    'message'=>'success',
                    'user_id'=>$user->id ,
                    'name'=> $user->first_name . ' ' . $user->last_name,
                    'token'=> $token,
                ]);

            }
            return response()->json([
                'message'=>'failed',
            ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json([
            'message'=>'success','logout successfully'
        ]);
    }

public function update_info_account(Request $request)
    {
        if($request->all() == [])
        { return response()->json(['message' => 'field']); }

        $image = $request->file('image');
        if($request->hasFile('image')){

                $fileName = date('dmY').time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path("/pictures"), $fileName);
            }

        $user = auth()->user();
        $user_id = $user->id;
        $user_exist=User::find($user_id);

        if (!$user_exist) {
            return response()->json(['message' => 'field']);
        }

        $user_exist->update($request->only(['first_name', 'last_name', 'phone', 'image','email','password']));
        $eventDataImage=[];
        if( $image){
            $user_exist->update([
                'image'=>$fileName
            ]);
            $eventDataImage = [
                'image' => $user_exist->image ? url('pictures/' . $fileName) : null,

            ];
        }

        $eventData = [
            'id' => $user_exist->id,
            'first_name' => $user_exist->first_name,
            'last_name' => $user_exist->last_name,
            'phone' => $user_exist->phone,
            'email' => $user_exist->email,
            'password' => $user_exist->password,
        ];

        $user_exist->save();
        return response()->json([
            'message' => 'success',
            'data'=>$eventData,
            'image'=>$eventDataImage,

        ]);
    }}
