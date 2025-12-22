<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Security\Users;
use Pages;

class UserController extends Controller
{
    public function store_auth(Request $request) {

        $database=$request->input('database');
        $hostname=($request->input('hostname') == null) ? $request->ip() : $request->input('hostname');

        // Set tenant connection dynamically
        Config::set('database.connections.tenant.database', $database);
        Config::set('database.connections.tenant.host', $hostname);

        // Reconnect with new DB
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Optional: make 'tenant' the default connection
        DB::setDefaultConnection('tenant');

        $validator=Validator::make($request->all(),[
            'username'=>'bail|required',
            'password'=>'bail|required'
        ],[
            'username.required'=>'User ID harus diisi',
            'password.required'=>'Password harus diisi'
        ]);

        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }
        $username=$request->input('username');
        $password=$request->input('password');

        $user=Users::selectRaw("password0")
        ->where('user_id',$username)
        ->where('fl_grup',false)
        ->where('fl_aktif',true)
        ->first();
        if ($user) {
            //if (Hash::check($password, $user->password)) { pake fungsi ini kalo password sudah di hashing
            if ($user->password0==$password) {
                $token=Pages::GenerateToken($username,$password);
                $response['token']=$token;
                return response()->success('success',$response);
            } else {
                return response()->error('',1001,'Password salah');
            }
        } else {
            return response()->error('',1001,"User tidak ditemukan");
        }
    }
}
