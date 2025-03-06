<?php
namespace App\Controllers;

class HomeController extends Controller
{
  public function index()
  {
    $title = 'Home page';
    return viewPage('welcome', compact('title'));
  }
}