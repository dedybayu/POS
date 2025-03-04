<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        $user = UserModel::where('username', 'manager9')->firstOrFail();
        return view('user', ['data' => $user]);
    }
}
