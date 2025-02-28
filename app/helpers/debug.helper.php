<?php

/**
 * Dump dữ liệu debug và dừng script với giao diện cải tiến
 *
 * @param mixed $data Dữ liệu cần hiển thị
 * @param bool $isError Có phải lỗi nghiêm trọng không
 * @return never
 */
function dd($data, bool $isError = false): never
{
  $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
  $caller = $backtrace[0] ?? null;
  $timestamp = date('Y-m-d H:i:s');

  // CSS inline cải tiến
  $styles = [
    'container' => 'background: '.($isError ? '#ffcccc' : '#f5f5f5').'; color: '.($isError ? '#721c24' : '#333').'; padding: 20px; border-radius: 8px; font-family: "Courier New", monospace; font-size: 14px; line-height: 1.5; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 90%; margin: 20px auto;',
    'header' => 'border-bottom: 2px solid '.($isError ? '#dc3545' : '#ccc').'; padding-bottom: 10px; margin-bottom: 15px;',
    'meta' => 'color: '.($isError ? '#721c24' : '#555').'; font-weight: bold;',
    'data' => 'background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;',
    'copy' => 'background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; float: right;'
  ];

  $output = "<div style='{$styles['container']}'>";

  // Header với meta thông tin
  $output .= "<header style='{$styles['header']}'>";
  $output .= "<span style='{$styles['meta']}'>Debug Output</span>";
  if ($caller) {
    $output .= "<br>📌 File: <span style='{$styles['meta']}'>{$caller['file']}</span>";
    $output .= "<br>📍 Line: <span style='{$styles['meta']}'>{$caller['line']}</span>";
  }
  $output .= "<br>⏰ Time: <span style='{$styles['meta']}'>{$timestamp}</span>";
  $output .= "</header>";

  // Dữ liệu trong thẻ details để có thể collapse
  $output .= "<details open><summary>Dữ liệu:</summary>";
  $output .= "<button style='{$styles['copy']}' onclick=\"navigator.clipboard.writeText('".htmlspecialchars(print_r($data, true), ENT_QUOTES)."')\">Copy</button>";
  $output .= "<div style='{$styles['data']}'>".htmlspecialchars(print_r($data, true))."</div>";
  $output .= "</details>";

  $output .= "</div>";

  // Ghi log nếu là lỗi
  if ($isError) {
    $logMessage = "File: {$caller['file']} | Line: {$caller['line']} | Data: ".print_r($data, true);
    app_log($logMessage, 'error');
    http_response_code(500);
  }

  die($output);
}

/**
 * Ghi log vào file trong storage/logs
 *
 * @param string $message Thông điệp cần ghi
 * @param string $level Mức độ log (info, error, warning, v.v.)
 * @param string|null $logDir Thư mục lưu log (mặc định app/storage/logs)
 * @param string|null $logFile Tên file log (mặc định log-YYYY-MM-DD.log)
 * @return void
 */
function app_log(string $message, string $level = 'info', ?string $logDir = null, ?string $logFile = null): void
{
  $defaultLogDir = basePath('app/storage/logs');
  $logDir ??= $defaultLogDir;

  if (! file_exists($logDir)) {
    mkdir($logDir, 0755, true);
  }

  // Dùng getCurrentTime() để tạo tên file theo múi giờ
  $defaultLogFile = 'log-'.date('Y-m-d').'.log';
  $logFile ??= $defaultLogFile;

  $filePath = rtrim($logDir, '/').'/'.$logFile;

  // Dùng getCurrentTime() cho timestamp trong log
  $formattedMessage = sprintf(
    "[%s] [%s] %s\n",
    date('Y-m-d H:i:s'),
    strtoupper($level),
    $message
  );

  file_put_contents($filePath, $formattedMessage, FILE_APPEND | LOCK_EX);
}