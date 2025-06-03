<?php

namespace App\Controllers;

use Core\Auth;

class HomeController extends BaseController
{
    public function index()
    {
        $this->view('home.index', [
            'title' => 'Welcome to ZenithPHP',
            'user' => Auth::user()
        ]);
    }
}