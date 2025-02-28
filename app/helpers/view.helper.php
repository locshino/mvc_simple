<?php
use eftec\bladeone\BladeOne;

function view(string $view, array $data = [], bool $debug = false)
{
  static $blade = null;
  if ($blade === null) {
    $views = __DIR__.'/../Views';
    $cache = __DIR__.'/../storage';
    if (! file_exists($cache)) {
      mkdir($cache, 0755, true);
    }
    if (! file_exists($views)) {
      mkdir($views, 0755, true);
    }
    $mode = $debug ? BladeOne::MODE_DEBUG : BladeOne::MODE_AUTO;
    $blade = new BladeOne($views, $cache, $mode);
  }
  echo $blade->run($view, $data);
}