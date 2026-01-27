<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class LandingController extends Controller
{
    public function __invoke()
    {
        return view('pages.landing');
    }
}
