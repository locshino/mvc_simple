<?php

use eftec\bladeone\BladeOne;

/**
 * Render a view file. It prioritizes Blade files (.blade.php) but falls back to plain PHP files (.php).
 *
 * @param string $view The view name (e.g., 'home.index').
 * @param array $data Data to pass to the view.
 * @param bool $bladeDebug Enable Blade debug mode.
 * @return string The rendered HTML content.
 * @throws RuntimeException If the view file cannot be found or rendered.
 */
function view(string $view, array $data = [], bool $bladeDebug = false): string
{
    $viewsDir = basePath('app/Views');
    $cacheDir = basePath('app/storage/blade');

    // Convert dot notation to a path segment. e.g., 'pages.home' -> 'pages/home'
    $viewPath = str_replace('.', '/', $view);

    $bladeFilePath = $viewsDir . '/' . $viewPath . '.blade.php';
    $phpFilePath = $viewsDir . '/' . $viewPath . '.php';

    // Priority 1: Check for a .blade.php file.
    if (file_exists($bladeFilePath)) {
        // This will throw an error if BladeOne class is not found, as intended.
        return renderWithBlade($view, $data, $bladeDebug, $viewsDir, $cacheDir);
    }

    // Priority 2: Fallback to a plain .php file.
    if (file_exists($phpFilePath)) {
        return renderWithPhp($phpFilePath, $data);
    }

    // If no view file is found at all.
    throw new RuntimeException("Không tìm thấy file view cho '$view'. Đã kiểm tra: $bladeFilePath và $phpFilePath");
}

/**
 * Renders a view using the BladeOne engine.
 * Helper function for view().
 */
function renderWithBlade(string $view, array $data, bool $bladeDebug, string $viewsDir, string $cacheDir): string
{
    static $blade = null;

    // Create cache directory if it doesn't exist
    if (!is_dir($cacheDir)) {
        if (!mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
            throw new RuntimeException("Không thể tạo thư mục cache: $cacheDir");
        }
    }

    $mode = $bladeDebug && !isProduction() ? BladeOne::MODE_DEBUG : BladeOne::MODE_AUTO;

    // Initialize BladeOne instance
    if ($blade === null) {
        $blade = new BladeOne($viewsDir, $cacheDir, $mode);
    }

    try {
        return $blade->run($view, $data);
    } catch (\Exception $e) {
        throw new RuntimeException("Không thể render view Blade '$view': " . $e->getMessage());
    }
}

/**
 * Renders a plain PHP view file.
 * Helper function for view().
 */
function renderWithPhp(string $filePath, array $data): string
{
    // Make data array keys available as variables in the included file
    extract($data);

    // Use output buffering to capture the file's content as a string
    ob_start();
    try {
        require $filePath;
    } catch (\Throwable $e) {
        ob_end_clean(); // Discard buffer on error
        throw new RuntimeException("Lỗi khi render file PHP view '$filePath': " . $e->getMessage());
    }
    return ob_get_clean();
}


/**
 * Renders a Blade page from the 'pages' namespace.
 * No changes needed here.
 */
function viewPage(string $page, array $data = [], bool $bladeDebug = false): string
{
    return view("pages.$page", $data, $bladeDebug);
}

/**
 * Renders an error page.
 * No changes needed here.
 */
function viewError(string $error, array $data = [], bool $bladeDebug = false): string
{
    // Note: This logic assumes error pages are in 'pages/errors/'.
    // It might be better to have a dedicated 'errors' folder at the root of Views.
    // For example: view("errors.$error", $data, $bladeDebug)
    return view("pages.errors.$error", $data, $bladeDebug);
}


/**
 * Checks if the application is in production environment.
 * No changes needed here.
 */
function isProduction(): bool
{
    return env('APP_ENV') === 'production';
}