# Simple MVC Framework

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/locshino/mvc_simple)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A lightweight MVC (Model-View-Controller) framework built with PHP, featuring a clean architecture and Bootstrap 5.3.3 integration.

## Features

- MVC Architecture with clean separation of concerns
- PSR-4 Autoloading for efficient class loading
- Bootstrap 5.3.3 Integration for responsive design
- Clean URL Routing with PHRoute
- BladeOne Template Engine
- Environment Configuration Management
- Storage Management for logs, cache, and uploads

## Project Structure

```
mvc_simple/
├── app/
│   ├── Controllers/    # Application controllers
│   ├── Core/          # Framework core components
│   ├── Models/        # Data models
│   ├── Routes/        # Route definitions
│   ├── Views/         # Blade template files
│   ├── assets/        # Application assets
│   ├── bootstrap/     # Application bootstrap files
│   └── storage/       # Storage for logs, cache, uploads
├── public/           # Public directory
│   └── css/         # CSS files including Bootstrap
└── composer.json    # Composer dependencies
```

## Requirements

- PHP 7.4 or higher
- Composer
- Web server (Apache/Nginx or XAMPP/Laragron)
- MySQL 5.7+ (recommended)

## Installation

### With Composer (Recommended)

1. Clone the repository:
```bash
git clone https://github.com/locshino/mvc_simple.git
```

2. Install dependencies:
```bash
composer install
```

3. Copy the environment file:
```bash
copy .env.example .env
```

### Without Composer

If you prefer not to use Composer, you can still use the framework at a basic level without installing external libraries:

1. Clone or download the repository

2. Copy the environment configuration file:
```bash
copy env.example.php env.php
```

3. Edit the `env.php` file to configure your application settings

> **Note:** When using the framework without Composer, some advanced features that depend on external libraries (BladeOne, PHRoute, PHP dotenv) will be limited. However, you can still use the core MVC architecture and basic routing functionality.

4. Configure your environment variables in `.env`:
```env
APP_NAME=MVCSimple
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Configure your web server:

For Apache (.htaccess is included):
- Point the document root to the `public` directory
- Ensure mod_rewrite is enabled

For Nginx:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Usage

### Creating a Controller

```php:c:\Laragron\www\mvc_simple\app\Controllers\UserController.php
namespace App\Controllers;

class UserController
{
    public function index()
    {
        return view('users.index', ['users' => User::all()]);
    }

    public function show($id)
    {
        return view('users.show', ['user' => User::find($id)]);
    }
}
```

### Defining Routes

```php:c:\Laragron\www\mvc_simple\app\Routes\web.php
use App\Controllers\UserController;

$router->get('/users', [UserController::class, 'index']);
$router->get('/users/{id}', [UserController::class, 'show']);
```

### Creating a Model

```php:c:\Laragron\www\mvc_simple\app\Models\User.php
namespace App\Models;

class User
{
    protected $table = 'users';
    
    public static function all()
    {
        // Implement database query logic
    }
    
    public static function find($id)
    {
        // Implement find logic
    }
}
```

### Creating a View

```php:c:\Laragron\www\mvc_simple\app\Views\users\index.blade.php
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Users List</h1>
        @foreach($users as $user)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">{{ $user->name }}</h5>
                </div>
            </div>
        @endforeach
    </div>
@endsection
```

## Storage

The framework includes dedicated storage directories:

- `storage/cache` - Blade template cache
- `storage/logs` - Application logs
- `storage/uploads` - File uploads

Make sure these directories are writable:
```bash
chmod -R 775 app/storage
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add appropriate comments and documentation
- Write unit tests for new features

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- [Bootstrap v5.3.3](https://getbootstrap.com/)
- [BladeOne Template Engine](https://github.com/EFTEC/BladeOne)
- [PHRoute](https://github.com/mrjgreen/phroute)
- [PHP dotenv](https://github.com/vlucas/phpdotenv)

## Support

For support, please open an issue in the GitHub repository or contact [locshino123@gmail.com](mailto:locshino123@gmail.com).
```
