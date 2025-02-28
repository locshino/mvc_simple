<?php
namespace App\Controllers;

class HomeController extends Controller
{
  public function index()
  {
    $title = 'Home page';
    return view('welcome', compact('title'));
  }
}