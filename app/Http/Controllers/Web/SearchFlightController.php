<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchFlightController extends Controller
{
    public function __invoke () {
        return view('search');
    }
}