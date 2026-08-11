<?php

namespace App\Controllers;

use App\Models\SureScriptAdapter;
use Exception;

class Connectivity extends BaseController
{
    public function __construct()
    {
        $this->adpater = new SureScriptAdapter();
    }

    public function index()
    {
        return $this->adpater->connectivy();
    }

}