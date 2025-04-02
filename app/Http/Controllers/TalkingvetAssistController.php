<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TalkingvetAssistController extends Controller
{
    public function index(){
        return view('assist.home');
    }
}
