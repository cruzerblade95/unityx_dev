<?php

namespace App\Http\Controllers\front_pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Landing extends Controller
{
  public function index()
  {
    $pageConfigs = ['myLayout' => 'blank'];
    // return view('content.front-pages.landing-page', ['pageConfigs' => $pageConfigs]);
    return view('user.login', ['pageConfigs' => $pageConfigs]);
  }
}
