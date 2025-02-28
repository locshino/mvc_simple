<?php
/**
 * Trả về instance của Request
 *
 * @return App\Core\Request
 */
function request(): App\Core\Request
{
  return App\Core\Request::instance();
}