<?php
namespace App\Core\Asset;

use finfo;

class Asset
{
  private static string $assetDir = '';
  private static string $uploadDir = '';
  private static array $allowedFolders = ['img', 'js', 'css', 'uploads'];

  // Bảng ánh xạ extension sang MIME type
  private static array $mimeMap = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'pdf' => 'application/pdf',
    // Có thể thêm các loại khác nếu cần
  ];

  // Các extension mặc định cho ảnh
  private static array $defaultImageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

  private static function init(): void
  {
    if (empty(self::$assetDir)) {
      self::$assetDir = basePath('app/assets/');
      self::$uploadDir = self::$assetDir.'uploads/';

      if (! is_dir(self::$uploadDir)) {
        mkdir(self::$uploadDir, 0755, true);
      }
    }
  }

  /**
   * Upload file với danh sách extension tùy chỉnh
   *
   * @param array $file Thông tin file từ $_FILES
   * @param int $maxSize Dung lượng tối đa (MB)
   * @param array $allowedExtensions Các extension được phép (mặc định rỗng)
   * @return AssetResponse
   */
  public static function uploadFile(array $file, int $maxSize = 5, array $allowedExtensions = []): AssetResponse
  {
    self::init();

    // Nếu không truyền allowedExtensions, dùng mặc định cho ảnh
    $allowedExt = ! empty($allowedExtensions) ? array_map('strtolower', $allowedExtensions) : self::$defaultImageExtensions;

    // Tạo danh sách MIME types từ extensions
    $allowedMime = [];
    foreach ($allowedExt as $ext) {
      if (isset(self::$mimeMap[$ext])) {
        $allowedMime[] = self::$mimeMap[$ext];
      }
    }
    if (empty($allowedMime)) {
      return new AssetResponse(false, 'Không có định dạng file nào được hỗ trợ!');
    }

    $maxSizeByte = $maxSize * 1024 * 1024;

    // Kiểm tra lỗi upload
    if (! isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
      return new AssetResponse(false, 'Có lỗi khi tải lên file!');
    }

    // Kiểm tra dung lượng
    if ($file['size'] > $maxSizeByte) {
      return new AssetResponse(false, "Dung lượng file không được vượt quá {$maxSize}MB!");
    }

    // Kiểm tra MIME type thực sự
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    if (! in_array($realMime, $allowedMime)) {
      $allowedExts = implode(', ', $allowedExt);
      return new AssetResponse(false, "Chỉ chấp nhận file có định dạng: {$allowedExts}!");
    }

    // Kiểm tra extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (! in_array($ext, $allowedExt)) {
      $allowedExts = implode(', ', $allowedExt);
      return new AssetResponse(false, "Phần mở rộng file không được phép: {$allowedExts}!");
    }

    // Tạo tên file an toàn
    $fileName = uniqid('file_', true).'.'.$ext;
    $filePath = self::$uploadDir.$fileName;

    // Lưu file
    if (! move_uploaded_file($file['tmp_name'], $filePath)) {
      return new AssetResponse(false, 'Có lỗi khi lưu file!');
    }

    return new AssetResponse(true, null, $fileName, $filePath);
  }

  /**
   * Upload ảnh với danh sách extension tùy chỉnh
   */
  public static function uploadImage(array $file, int $maxSize = 5, array $allowedExtensions = []): AssetResponse
  {
    $allowedExtensions = ! empty($allowedExtensions) ? $allowedExtensions : self::$defaultImageExtensions;
    return self::uploadFile($file, $maxSize, $allowedExtensions);
  }

  /**
   * Phục vụ file tĩnh
   */
  public static function serveFile(string $type, string $fileName): void
  {
    self::init();

    if (! in_array($type, self::$allowedFolders)) {
      http_response_code(403);
      die('Truy cập bị từ chối!');
    }

    $filePath = self::$assetDir.$type.'/'.basename($fileName);

    if (! file_exists($filePath)) {
      http_response_code(404);
      die('File không tồn tại!');
    }

    $mimeTypes = self::$mimeMap; // Tái sử dụng mimeMap
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filePath)).' GMT');
    header('Cache-Control: public, max-age=31536000');
    header('Content-Type: '.$mimeType);
    header('Content-Length: '.filesize($filePath));
    readfile($filePath);
    exit;
  }

  /**
   * Tải ảnh từ uploads
   */
  public static function downloadImage(string $fileName): void
  {
    self::serveFile('uploads', $fileName);
  }

  public static function setMimeMap(array $mimeMap): void
  {
    self::$mimeMap = $mimeMap;
  }
}