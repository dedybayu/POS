<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        // $data = [
        //     'username' => 'customer-1',
        //     'nama' => 'Pelanggan',
        //     'password' => Hash::make('1234'),
        //     'level_id' => 4
        // ];
        // UserModel::insert($data);

        $data = [
            'nama' => 'Pelanggan Pertama',
        ];
        UserModel::where('username', 'customer-1')->update($data);
        $user = UserModel::all();
        return view('user', ['data' => $user]);

        // return view('user')
        //     ->with('id', $id)
        //     ->with('name', $name);
    }
}
