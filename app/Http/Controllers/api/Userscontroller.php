<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class Userscontroller extends Controller
{
    public function index(){
        $users =User::get();
        return response()->Json(['ststus'=>'true','users'=>$users]);
    }
}
