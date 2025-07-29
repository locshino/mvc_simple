<?php

/**
 * Load environment variables from .env file or fallback to env.php.
 *
 * @param string $path Path to the directory containing the .env file.
 * @return void
 */
function loadEnv(string $path = ''): void
{
    // Check if the Dotenv library class exists
    if (class_exists(Dotenv\Dotenv::class)) {
        // --- Library logic (if installed) ---
        $envPath = $path ?: basePath();
        $envFile = $envPath . '/.env';

        if (!file_exists($envFile)) {
            die("File .env không tồn tại tại: $envFile");
        }

        try {
            $dotenv = Dotenv\Dotenv::createImmutable($envPath);
            $dotenv->load();
        } catch (Exception $e) {
            app_log("Environment error: " . $e->getMessage(), 'error');
            if (env('APP_DEBUG', false)) {
                die("Environment error: " . $e->getMessage());
            }
            die("Failed to load environment configuration");
        }
    } else {
        // --- Fallback logic (if library is not installed) ---
        $fallbackEnvFile = ($path ?: basePath()) . '/env.php';

        if (file_exists($fallbackEnvFile)) {
            require_once $fallbackEnvFile;
        } else {
            // Stop if neither Dotenv nor the fallback file is available
            die("Thư viện Dotenv chưa được cài đặt và file dự phòng env.php cũng không tìm thấy.");
        }
    }
}

/**
 * Get an environment variable or a default value.
 *
 * @param string $key
 * @param mixed|null $default
 * @return mixed
 */
function env(string $key, $default = null): mixed
{
    // This function does not need to be changed
    return $_ENV[$key] ?? $default;
}