<?php
namespace App\Helpers;

function dd($data, $isError = false): never
{
  $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
  $caller = $backtrace[0] ?? null;

  $output = "<pre style='background: ".($isError ? "#8B0000" : "#222")."; color: #fff; padding: 10px;'>";

  if ($caller) {
    $output .= "📌 File: <b>{$caller['file']}</b> <br>";
    $output .= "📍 Line: <b>{$caller['line']}</b> <br><br>";
  }

  $output .= htmlspecialchars(print_r($data, true));
  $output .= "</pre>";

  if ($isError) {
    error_log(strip_tags($output));
    http_response_code(500);
  }

  die($output);
}