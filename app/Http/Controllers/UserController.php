<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        // $user = UserModel::find(1);
        // $user = UserModel::where('level_id', 1)->first();
        $user = UserModel::FirstWhere('level_id', 1);
        return view('user', ['data' => $user]);


        // $data = [
        //     'level_id' => 2,
        //     'username' => 'manager_tiga',
        //     'nama' => 'Manager 3',
        //     'password' => Hash::make('12345')
        // ];
        // UserModel::create($data);

        // $user = UserModel::all();
        // return view('user', ['data' => $user]);
    }
}
