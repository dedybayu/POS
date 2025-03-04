<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        // $user = UserModel::firstOrNew([
        //     'username' => 'manager',
        //     'nama' => 'Manager',

        // ]);
        // return view('user', ['data' => $user]);


        $user = UserModel::firstOrNew([
            'username' => 'manager33',
            'nama' => 'Manager Tiga TigaD',
            'password' => Hash::make('12345'),
            'level_id' => 2
        ]);
        $user->save();
        return view('user', ['data' => $user]);
    }
}
