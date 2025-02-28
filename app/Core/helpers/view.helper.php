<?php
function view(string $view, array $data = [], bool $debug = false)
{
  static $blade = null;
  if ($blade === null) {
    $views = basePath('app/Views');
    $cache = basePath('app/storage/blade');
    if (! file_exists($cache)) {
      mkdir($cache, 0755, true);
    }
    if (! file_exists($views)) {
      mkdir($views, 0755, true);
    }
    $mode = $debug
      ? eftec\bladeone\BladeOne::MODE_DEBUG
      : eftec\bladeone\BladeOne::MODE_AUTO;
    $blade = new eftec\bladeone\BladeOne($views, $cache, $mode);
  }
  return $blade->run($view, $data);
}