<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class DisplayController extends Controller
{
    public function page()
    {
        return view('pages.display');
    }
}
