<?php
namespace App\Core\Asset;

class AssetResponse
{
  public bool $success;
  public ?string $error;
  public ?string $fileName;
  public ?string $filePath;

  public function __construct(bool $success, ?string $error = null, ?string $fileName = null, ?string $filePath = null)
  {
    $this->success = $success;
    $this->error = $error;
    $this->fileName = $fileName;
    $this->filePath = $filePath;
  }
}