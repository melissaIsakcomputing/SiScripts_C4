<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        echo "hi";
        return view('welcome_message');
    }
}
