<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class Userscontroller extends Controller
{
    public function index(){
        $users = User::all();
        return response()->json(['status' => 'true', 'users' => $users]);
    }

    public function getuser($id){
       $users =User::find($id);
        return response()->Json(['status'=>'true','users'=>$users]);
    }
}

