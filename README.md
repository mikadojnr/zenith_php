# ZenithPHP Framework

A modern, lightweight PHP MVC framework with built-in authentication, Google OAuth, database migrations, and comprehensive development features.

## Features

- 🏗️ **MVC Architecture** - Clean separation of concerns
- 🗄️ **Database Migrations** - Version control for your database
- 🔐 **Authentication System** - Built-in user authentication with session management
- 🔑 **Google OAuth Integration** - Sign in with Google account
- 🛡️ **Middleware System** - Request filtering and processing
- 🎨 **Template Engine** - Simple yet powerful view rendering
- 🛣️ **Routing System** - Clean URL routing with middleware support
- 📦 **Composer Compatible** - Modern PHP dependency management
- 🔧 **CLI Tool** - Command-line interface for development tasks
- 🌐 **Environment Configuration** - Flexible environment-based configuration

## Quick Start

### Installation

1. **Create a new project:**
```bash
composer create-project zenithphp/framework my-app
cd my-app
```

2. **Set up environment:**
```bash
cp .env.example .env
```

3. **Configure your database and Google OAuth in `.env`:**
```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

4. **Run migrations:**
```bash
php zenith migrate
```

5. **Start the development server:**
```bash
php zenith serve
```

Visit `http://localhost:8000` in your browser!

## Google OAuth Setup

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google+ API
4. Create OAuth 2.0 credentials:
   - Application type: Web application
   - Add your domain to authorized origins
   - Add your callback URL to authorized redirect URIs
5. Copy the Client ID and Client Secret to your `.env` file

## Framework Structure

### Controllers

Controllers handle HTTP requests and return responses. Create a new controller:

```bash
php zenith make:controller UserController
```

Example controller:
```php
<?php

namespace App\Controllers;

use Core\Auth;
use App\Models\User;

class UserController extends BaseController
{
    public function index()
    {
        $users = User::all();
        $this->view('users.index', ['users' => $users]);
    }
    
    public function show()
    {
        $id = $_GET['id'] ?? 1;
        $user = User::find($id);
        
        if (!$user) {
            $this->redirect('/users');
        }
        
        $this->view('users.show', ['user' => $user]);
    }
    
    public function api()
    {
        $users = User::all();
        $this->json(['users' => $users]);
    }
}
```

### Models

Models represent data and business logic. Create a new model:

```bash
php zenith make:model Post
```

Example model:
```php
<?php

namespace App\Models;

class Post extends BaseModel
{
    protected static string $table = 'posts';
    
    public static function findBySlug(string $slug): ?array
    {
        $db = Database::getInstance();
        return $db->findBy(static::$table, ['slug' => $slug]);
    }
    
    public function getExcerpt(int $length = 100): string
    {
        return substr($this->content, 0, $length) . '...';
    }
}
```

### Views

Views handle the presentation layer. Create views in `app/Views/`:

```php
<!-- app/Views/posts/index.php -->
<h1>All Posts</h1>

<?php foreach ($posts as $post): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($post['title']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($post['excerpt']) ?></p>
            <a href="/posts/<?= $post['id'] ?>" class="btn btn-primary">Read More</a>
        </div>
    </div>
<?php endforeach; ?>
```

### Routing

Define routes in `routes/web.php`:

```php
<?php

use Core\Application;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;

$router = Application::app()->router;

// Public routes
$router->get('/', 'HomeController@index');
$router->get('/posts', 'PostController@index');
$router->get('/posts/{id}', 'PostController@show');

// API routes with CORS
$router->get('/api/posts', 'PostController@apiIndex')
       ->middleware([CorsMiddleware::class]);

// Protected routes
$router->get('/dashboard', 'DashboardController@index')
       ->middleware([AuthMiddleware::class]);

$router->post('/posts', 'PostController@store')
       ->middleware([AuthMiddleware::class]);
```

### Middleware

Middleware provides a convenient mechanism for filtering HTTP requests:

```php
<?php

namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): void
    {
        if (!\Core\Auth::user()->isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Access denied';
            exit;
        }
    }
}
```

Use middleware in routes:
```php
$router->get('/admin', 'AdminController@index')
       ->middleware([AuthMiddleware::class, AdminMiddleware::class]);
```

### Database Migrations

Create and run database migrations:

```bash
php zenith make:migration create_posts_table
```

Example migration:
```php
<?php

use Core\Database;

class CreatePostsTable
{
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            content TEXT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        
        $db->query($sql);
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE posts");
    }
}
```

Run migrations:
```bash
php zenith migrate
```

## Database Operations

### Basic CRUD Operations

```php
// Create
$userId = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);

// Read
$user = User::find($userId);
$allUsers = User::all();
$userByEmail = User::findByEmail('john@example.com');

// Update
$db = Database::getInstance();
$db->update('users', $userId, ['name' => 'Jane Doe']);

// Delete
$db->delete('users', $userId);
```

### Advanced Queries

```php
$db = Database::getInstance();

// Custom query
$posts = $db->query(
    "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC",
    [$userId]
)->fetchAll();

// Find with conditions
$activeUsers = $db->query(
    "SELECT * FROM users WHERE status = ?",
    ['active']
)->fetchAll();
```

## Authentication

### Basic Authentication

```php
// Login attempt
if (Auth::attempt($email, $password)) {
    // Login successful
    redirect('/dashboard');
} else {
    // Login failed
    Session::flash('error', 'Invalid credentials');
}

// Check authentication
if (Auth::check()) {
    $user = Auth::user();
    echo "Hello, " . $user->name;
}

// Logout
Auth::logout();
```

### Google OAuth

The framework includes built-in Google OAuth support:

1. User clicks "Sign in with Google"
2. Redirected to Google OAuth
3. Google redirects back to `/auth/google/callback`
4. Framework handles the callback and creates/logs in the user

## Helper Functions

The framework includes several helper functions:

```php
// Environment variables
$appName = env('APP_NAME', 'Default Name');

// Configuration
$dbHost = config('database.connections.mysql.host');

// URLs
$cssUrl = asset('css/app.css');
$homeUrl = url('/');

// Debugging
dd($variable); // Dump and die

// Redirects
redirect('/login');
```

## CLI Commands

### Available Commands

```bash
# Run migrations
php zenith migrate

# Create new controller
php zenith make:controller PostController

# Create new model  
php zenith make:model Post

# Create new migration
php zenith make:migration create_posts_table

# Start development server
php zenith serve
php zenith serve 9000  # Custom port
```

## Configuration

### Application Configuration

Edit `config/app.php`:
```php
return [
    'name' => env('APP_NAME', 'ZenithPHP'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
];
```

### Database Configuration

Edit `config/database.php`:
```php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'zenithphp'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
        ],
    ],
];
```

## Session Management

```php
// Set session data
Session::set('key', 'value');

// Get session data
$value = Session::get('key', 'default');

// Check if session has key
if (Session::has('key')) {
    // Key exists
}

// Flash messages
Session::flash('success', 'Operation completed!');
$message = Session::flash('success'); // Retrieve and remove

// Remove session data
Session::remove('key');

// Destroy session
Session::destroy();
```

## Error Handling

The framework includes basic error handling:

```php
// In your controller
try {
    $user = User::find($id);
    if (!$user) {
        throw new Exception('User not found');
    }
} catch (Exception $e) {
    Session::flash('error', $e->getMessage());
    $this->redirect('/users');
}
```

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **SQL Injection Protection**: PDO prepared statements
- **XSS Protection**: Manual escaping required (`htmlspecialchars()`)
- **CSRF Protection**: Implement manually as needed
- **Session Security**: Configurable session settings

## Best Practices

### 1. Controller Structure
```php
class PostController extends BaseController
{
    public function index()
    {
        $posts = Post::all();
        $this->view('posts.index', compact('posts'));
    }
    
    public function store()
    {
        // Validate input
        $title = $_POST['title'] ?? '';
        if (empty($title)) {
            Session::flash('error', 'Title is required');
            return $this->redirect('/posts/create');
        }
        
        // Create post
        Post::create([
            'title' => $title,
            'content' => $_POST['content'] ?? '',
            'user_id' => Auth::user()->id
        ]);
        
        Session::flash('success', 'Post created successfully');
        $this->redirect('/posts');
    }
}
```

### 2. Model Relationships
```php
class User extends BaseModel
{
    protected static string $table = 'users';
    
    public function posts(): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT * FROM posts WHERE user_id = ?",
            [$this->id]
        )->fetchAll();
    }
}
```

### 3. View Organization
```
app/Views/
├── layouts/
│   ├── app.php        # Main layout
│   └── admin.php      # Admin layout
├── components/
│   ├── navbar.php     # Reusable components
│   └── footer.php
├── posts/
│   ├── index.php
│   ├── show.php
│   └── create.php
└── users/
    ├── index.php
    └── profile.php
```

## Deployment

### Production Setup

1. **Server Requirements:**
   - PHP 8.0 or higher
   - MySQL/MariaDB
   - Apache/Nginx with mod_rewrite

2. **Environment Configuration:**
```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=your_production_host
DB_DATABASE=your_production_db
```

3. **Web Server Configuration:**

**Apache (.htaccess already included):**
```apache
<VirtualHost *:80>
    DocumentRoot "/path/to/your/app/public"
    ServerName yourdomain.com
    
    <Directory "/path/to/your/app/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/app/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

4. **Run Migrations:**
```bash
php zenith migrate
```

## Extending the Framework

### Custom Middleware
```php
<?php

namespace App\Middleware;

class RateLimitMiddleware
{
    public function handle(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit_$ip";
        
        // Implement rate limiting logic
        // This is a basic example
        if ($this->isRateLimited($ip)) {
            http_response_code(429);
            echo 'Too many requests';
            exit;
        }
    }
    
    private function isRateLimited(string $ip): bool
    {
        // Implement your rate limiting logic
        return false;
    }
}
```

### Custom Validation
```php
<?php

namespace Core;

class Validator
{
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (str_contains($rule, 'required') && empty($value)) {
                $errors[$field] = "$field is required";
            }
            
            if (str_contains($rule, 'email') && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "$field must be a valid email";
            }
        }
        
        return $errors;
    }
}
```

Use in controller:
```php
public function store()
{
    $errors = Validator::validate($_POST, [
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required'
    ]);
    
    if (!empty($errors)) {
        Session::flash('errors', $errors);
        return $this->redirect('/register');
    }
    
    // Process valid data
}
```

## Troubleshooting

### Common Issues

1. **404 Errors**
   - Check if mod_rewrite is enabled
   - Verify .htaccess file exists in public/
   - Ensure routes are defined correctly

2. **Database Connection Issues**
   - Verify database credentials in .env
   - Check if database exists
   - Ensure PDO MySQL extension is installed

3. **Google OAuth Issues**
   - Verify Google Client ID and Secret
   - Check redirect URI matches exactly
   - Ensure Google+ API is enabled

4. **Permission Issues**
   - Ensure storage/ directory is writable
   - Check file permissions on uploaded files

### Debug Mode

Enable debug mode in .env:
```env
APP_DEBUG=true
```

This will show detailed error messages and stack traces.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This framework is open-sourced software licensed under the MIT license.

## Support

For support and questions:
- GitHub Issues: [Report bugs and request features]
- Documentation: [Full documentation available online]
- Community: [Join our community discussions]

## Directory Structure

```
zenith-php/
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── HomeController.php
│   │   └── AuthController.php
│   ├── Models/
│   │   ├── BaseModel.php
│   │   └── User.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   └── CorsMiddleware.php
│   └── Views/
│       ├── layouts/
│       │   └── app.php
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       └── home/
│           └── index.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── auth.php
├── core/
│   ├── Application.php
│   ├── Router.php
│   ├── Database.php
│   ├── View.php
│   ├── Migration.php
│   ├── Auth.php
│   └── Session.php
├── database/
│   └── migrations/
│       └── 001_create_users_table.php
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       └── js/
├── storage/
│   └── logs/
├── vendor/
├── .env.example
├── .gitignore
├── composer.json
├── README.md
└── zenith
```

## Core Files

### composer.json
```json
{
    "name": "zenithphp/framework",
    "description": "A lightweight PHP MVC framework",
    "type": "project",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Core\\": "core/"
        }
    },
    "require": {
        "php": ">=8.0",
        "google/apiclient": "^2.12",
        "vlucas/phpdotenv": "^5.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5"
    },
    "scripts": {
        "post-create-project-cmd": [
            "cp .env.example .env"
        ]
    }
}
```

### .env.example
```env
# Application Configuration
APP_NAME="ZenithPHP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zenithphp
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth Configuration
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Session Configuration
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_FILES=storage/sessions
SESSION_SECURE_COOKIE=false
```

### .gitignore
```gitignore
/vendor/
.env
/storage/logs/*.log
/storage/sessions/*
composer.lock
.DS_Store
Thumbs.db
```

### public/.htaccess
```apache
RewriteEngine On

# Handle Angular and Vue.js client side routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### public/index.php
```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Application;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create and run application
$app = new Application();
$app->run();
```

### core/Application.php
```php
<?php

namespace Core;

use Core\Router;
use Core\Database;
use Core\Session;

class Application
{
    public Router $router;
    public Database $db;
    public Session $session;
    public static Application $app;

    public function __construct()
    {
        self::$app = $this;
        $this->router = new Router();
        $this->db = new Database();
        $this->session = new Session();
        
        $this->loadRoutes();
    }

    public function run()
    {
        $this->router->resolve();
    }

    private function loadRoutes()
    {
        // Load web routes
        require_once __DIR__ . '/../routes/web.php';
    }

    public static function app(): Application
    {
        return self::$app;
    }
}
```

### core/Router.php
```php
<?php

namespace Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, $callback): Router
    {
        $this->routes['GET'][$path] = $callback;
        return $this;
    }

    public function post(string $path, $callback): Router
    {
        $this->routes['POST'][$path] = $callback;
        return $this;
    }

    public function put(string $path, $callback): Router
    {
        $this->routes['PUT'][$path] = $callback;
        return $this;
    }

    public function delete(string $path, $callback): Router
    {
        $this->routes['DELETE'][$path] = $callback;
        return $this;
    }

    public function middleware(array $middlewares): Router
    {
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    public function resolve()
    {
        $path = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remove query string from path
        $path = parse_url($path, PHP_URL_PATH);

        $callback = $this->routes[$method][$path] ?? false;

        if (!$callback) {
            http_response_code(404);
            echo "404 - Page Not Found";
            return;
        }

        // Execute middlewares
        foreach ($this->middlewares as $middleware) {
            $middlewareInstance = new $middleware();
            $middlewareInstance->handle();
        }

        // Execute callback
        if (is_string($callback)) {
            $this->executeControllerAction($callback);
        } else {
            call_user_func($callback);
        }

        // Reset middlewares for next route
        $this->middlewares = [];
    }

    private function executeControllerAction(string $callback)
    {
        [$controller, $action] = explode('@', $callback);
        $controller = "App\\Controllers\\$controller";
        
        if (class_exists($controller)) {
            $controllerInstance = new $controller();
            if (method_exists($controllerInstance, $action)) {
                $controllerInstance->$action();
            }
        }
    }
}
```

### core/Database.php
```php
<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private static ?Database $instance = null;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $dsn = sprintf(
            "%s:host=%s;port=%s;dbname=%s;charset=utf8mb4",
            $_ENV['DB_CONNECTION'],
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_DATABASE']
        );

        try {
            $this->pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function find(string $table, int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM $table WHERE id = ?", [$id]);
        return $stmt->fetch() ?: null;
    }

    public function findBy(string $table, array $conditions): ?array
    {
        $where = implode(' AND ', array_map(fn($key) => "$key = ?", array_keys($conditions)));
        $stmt = $this->query("SELECT * FROM $table WHERE $where", array_values($conditions));
        return $stmt->fetch() ?: null;
    }

    public function all(string $table): array
    {
        $stmt = $this->query("SELECT * FROM $table");
        return $stmt->fetchAll();
    }

    public function create(string $table, array $data): int
    {
        $fields = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->pdo->prepare("INSERT INTO $table ($fields) VALUES ($placeholders)");
        $stmt->execute($data);
        
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, int $id, array $data): bool
    {
        $fields = implode(' = ?, ', array_keys($data)) . ' = ?';
        $stmt = $this->query("UPDATE $table SET $fields WHERE id = ?", [...array_values($data), $id]);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $table, int $id): bool
    {
        $stmt = $this->query("DELETE FROM $table WHERE id = ?", [$id]);
        return $stmt->rowCount() > 0;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
```

### core/View.php
```php
<?php

namespace Core;

class View
{
    private static string $viewsPath = __DIR__ . '/../app/Views/';

    public static function render(string $view, array $data = [], ?string $layout = 'layouts.app')
    {
        $viewPath = self::$viewsPath . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: $viewPath");
        }

        // Extract data for view
        extract($data);
        
        // Start output buffering
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        // If layout is specified, include it
        if ($layout) {
            $layoutPath = self::$viewsPath . str_replace('.', '/', $layout) . '.php';
            if (file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
}
```

### core/Migration.php
```php
<?php

namespace Core;

class Migration
{
    private Database $db;
    private string $migrationsPath;
    private string $migrationsTable = 'migrations';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrationsPath = __DIR__ . '/../database/migrations/';
        $this->createMigrationsTable();
    }

    private function createMigrationsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->query($sql);
    }

    public function run()
    {
        $executedMigrations = $this->getExecutedMigrations();
        $migrationFiles = $this->getMigrationFiles();

        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($migrationName, $executedMigrations)) {
                $this->executeMigration($file, $migrationName);
            }
        }
    }

    private function getExecutedMigrations(): array
    {
        $stmt = $this->db->query("SELECT migration FROM {$this->migrationsTable}");
        return array_column($stmt->fetchAll(), 'migration');
    }

    private function getMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '*.php');
        sort($files);
        return $files;
    }

    private function executeMigration(string $file, string $migrationName)
    {
        require_once $file;
        
        $className = $this->getClassNameFromFile($file);
        if (class_exists($className)) {
            $migration = new $className();
            $migration->up();
            
            // Record the migration
            $this->db->query(
                "INSERT INTO {$this->migrationsTable} (migration) VALUES (?)",
                [$migrationName]
            );
            
            echo "Executed migration: $migrationName\n";
        }
    }

    private function getClassNameFromFile(string $file): string
    {
        $content = file_get_contents($file);
        preg_match('/class\s+([^\s]+)/', $content, $matches);
        return $matches[1] ?? '';
    }
}
```

### core/Auth.php
```php
<?php

namespace Core;

use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;

class Auth
{
    private static ?User $user = null;

    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            self::login($user);
            return true;
        }
        
        return false;
    }

    public static function login(array $user): void
    {
        Session::set('user_id', $user['id']);
        self::$user = new User($user);
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$user = null;
    }

    public static function user(): ?User
    {
        if (self::$user === null && Session::has('user_id')) {
            $userData = User::find(Session::get('user_id'));
            if ($userData) {
                self::$user = new User($userData);
            }
        }
        
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function getGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $client->addScope('email');
        $client->addScope('profile');
        
        return $client;
    }

    public static function handleGoogleCallback(string $code): bool
    {
        $client = self::getGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);
        
        if (isset($token['error'])) {
            return false;
        }
        
        $client->setAccessToken($token);
        $service = new Google_Service_Oauth2($client);
        $userInfo = $service->userinfo->get();
        
        // Find or create user
        $user = User::findByEmail($userInfo->email);
        
        if (!$user) {
            $userId = User::create([
                'name' => $userInfo->name,
                'email' => $userInfo->email,
                'google_id' => $userInfo->id,
                'avatar' => $userInfo->picture,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            $user = User::find($userId);
        }
        
        self::login($user);
        return true;
    }
}
```

### core/Session.php
```php
<?php

namespace Core;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_destroy();
    }

    public static function flash(string $key, $value = null)
    {
        if ($value === null) {
            $flash = self::get("flash_$key");
            self::remove("flash_$key");
            return $flash;
        }
        
        self::set("flash_$key", $value);
    }
}
```

### app/Controllers/BaseController.php
```php
<?php

namespace App\Controllers;

use Core\View;

abstract class BaseController 
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts.app')
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
```

### app/Controllers/HomeController.php
```php
<?php

namespace App\Controllers;

use Core\Auth;

class HomeController extends BaseController
{
    public function index()
    {
        $this->view('home.index', [
            'title' => 'Welcome to ZenithPHP',
            'user' => Auth::user()
        ]);
    }
}
```

### app/Controllers/AuthController.php
```php
<?php

namespace App\Controllers;

use Core\Auth;
use Core\Session;
use App\Models\User;

class AuthController extends BaseController
{
    public function showLogin()
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        
        $this->view('auth.login', [
            'title' => 'Login',
            'googleUrl' => Auth::getGoogleClient()->createAuthUrl()
        ]);
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (Auth::attempt($email, $password)) {
            $this->redirect('/');
        } else {
            Session::flash('error', 'Invalid credentials');
            $this->redirect('/login');
        }
    }

    public function showRegister()
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        
        $this->view('auth.register', [
            'title' => 'Register'
        ]);
    }

    public function register()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'All fields are required');
            $this->redirect('/register');
            return;
        }

        // Check if user exists
        if (User::findByEmail($email)) {
            Session::flash('error', 'Email already exists');
            $this->redirect('/register');
            return;
        }

        // Create user
        $userId = User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $user = User::find($userId);
        Auth::login($user);
        
        $this->redirect('/');
    }

    public function googleCallback()
    {
        $code = $_GET['code'] ?? null;
        
        if ($code && Auth::handleGoogleCallback($code)) {
            $this->redirect('/');
        } else {
            Session::flash('error', 'Google authentication failed');
            $this->redirect('/login');
        }
    }

    public function logout()
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
```

### app/Models/BaseModel.php
```php
<?php

namespace App\Models;

use Core\Database;

abstract class BaseModel
{
    protected static string $table;
    protected array $attributes = [];
    protected Database $db;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->db = Database::getInstance();
    }

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->find(static::$table, $id);
    }

    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->all(static::$table);
    }

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        return $db->create(static::$table, $data);
    }

    public function update(array $data): bool
    {
        return $this->db->update(static::$table, $this->id, $data);
    }

    public function delete(): bool
    {
        return $this->db->delete(static::$table, $this->id);
    }

    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
}
```

### app/Models/User.php
```php
<?php

namespace App\Models;

use Core\Database;

class User extends BaseModel
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        return $db->findBy(static::$table, ['email' => $email]);
    }

    public function getFullName(): string
    {
        return $this->name;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
```

### app/Middleware/AuthMiddleware.php
```php
<?php

namespace App\Middleware;

use Core\Auth;

class AuthMiddleware
{
    public function handle(): void
    {
        if (Auth::guest()) {
            header('Location: /login');
            exit;
        }
    }
}
```

### app/Middleware/CorsMiddleware.php
```php
<?php

namespace App\Middleware;

class CorsMiddleware
{
    public function handle(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}
```

### database/migrations/001_create_users_table.php
```php
<?php

use Core\Database;

class CreateUsersTable
{
    public function up()
    {
        $db = Database::getInstance();
        
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255),
            google_id VARCHAR(255),
            avatar VARCHAR(255),
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->query($sql);
    }

    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE users");
    }
}
```

### routes/web.php
```php
<?php

use Core\Application;
use App\Middleware\AuthMiddleware;

$router = Application::app()->router;

// Public routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/auth/google/callback', 'AuthController@googleCallback');

// Protected routes
$router->get('/logout', 'AuthController@logout')->middleware([AuthMiddleware::class]);
```

### app/Views/layouts/app.php
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ZenithPHP Framework' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">ZenithPHP</a>
            <div class="navbar-nav ms-auto">
                <?php if (\Core\Auth::check()): ?>
                    <span class="navbar-text me-3">
                        Hello, <?= \Core\Auth::user()->name ?>!
                    </span>
                    <a class="nav-link" href="/logout">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="/login">Login</a>
                    <a class="nav-link" href="/register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (\Core\Session::flash('error')): ?>
            <div class="alert alert-danger">
                <?= \Core\Session::flash('error') ?>
            </div>
        <?php endif; ?>

        <?php if (\Core\Session::flash('success')): ?>
            <div class="alert alert-success">
                <?= \Core\Session::flash('success') ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### app/Views/home/index.php
```php
<div class="row">
    <div class="col-md-8">
        <h1>Welcome to ZenithPHP Framework</h1>
        <p class="lead">A modern, lightweight PHP MVC framework with built-in authentication and comprehensive features.</p>
        
        <?php if ($user): ?>
            <div class="alert alert-success">
                <h4>Hello, <?= $user->name ?>!</h4>
                <p>You are successfully logged in.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <h4>Get Started</h4>
                <p>Please <a href="/login">login</a> or <a href="/register">register</a> to access protected features.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Framework Features</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li>✅ MVC Architecture</li>
                    <li>✅ Database Migrations</li>
                    <li>✅ Authentication System</li>
                    <li>✅ Google OAuth</li>
                    <li>✅ Middleware System</li>
                    <li>✅ Template Engine</li>
                    <li>✅ Routing System</li>
                    <li>✅ Composer Compatible</li>
                </ul>
            </div>
        </div>
    </div>
</div>
```

### app/Views/auth/login.php
```php
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Login</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/login">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <a href="<?= $googleUrl ?>" class="btn btn-danger">
                        Sign in with Google
                    </a>
                </div>
                
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="/register">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
```

### app/Views/auth/register.php
```php
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Register</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/register">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="/login">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
```

### config/app.php
```php
<?php

return [
    'name' => env('APP_NAME', 'ZenithPHP'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
];
```

### config/database.php
```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'zenithphp'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
];
```

### config/auth.php
```php
<?php

return [
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    ],
    
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120),
        'encrypt' => env('SESSION_ENCRYPT', false),
        'files' => env('SESSION_FILES', 'storage/sessions'),
        'secure_cookie' => env('SESSION_SECURE_COOKIE', false),
    ],
];
```

### zenith (CLI Tool)
```php
#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Migration;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$command = $argv[1] ?? '';

switch ($command) {
    case 'migrate':
        $migration = new Migration();
        $migration->run();
        echo "Migrations completed successfully!\n";
        break;
        
    case 'make:controller':
        $name = $argv[2] ?? '';
        if (empty($name)) {
            echo "Error: Controller name is required.\n";
            echo "Usage: php zenith make:controller ControllerName\n";
            exit(1);
        }
        makeController($name);
        break;
        
    case 'make:model':
        $name = $argv[2] ?? '';
        if (empty($name)) {
            echo "Error: Model name is required.\n";
            echo "Usage: php zenith make:model ModelName\n";
            exit(1);
        }
        makeModel($name);
        break;
        
    case 'make:migration':
        $name = $argv[2] ?? '';
        if (empty($name)) {
            echo "Error: Migration name is required.\n";
            echo "Usage: php zenith make:migration migration_name\n";
            exit(1);
        }
        makeMigration($name);
        break;
        
    case 'serve':
        $port = $argv[2] ?? '8000';
        echo "Starting development server on http://localhost:$port\n";
        echo "Press Ctrl+C to stop the server\n";
        exec("php -S localhost:$port -t public");
        break;
        
    default:
        echo "ZenithPHP Framework CLI\n\n";
        echo "Available commands:\n";
        echo "  migrate              Run database migrations\n";
        echo "  make:controller      Create a new controller\n";
        echo "  make:model          Create a new model\n";
        echo "  make:migration      Create a new migration\n";
        echo "  serve [port]        Start development server (default port: 8000)\n";
        break;
}

function makeController($name)
{
    $controllerPath = __DIR__ . "/app/Controllers/{$name}.php";
    
    if (file_exists($controllerPath)) {
        echo "Error: Controller {$name} already exists.\n";
        return;
    }
    
    $template = "<?php

namespace App\Controllers;

class {$name} extends BaseController
{
    public function index()
    {
        \$this->view('index', [
            'title' => '{$name}'
        ]);
    }
}
";
    
    file_put_contents($controllerPath, $template);
    echo "Controller {$name} created successfully!\n";
}

function makeModel($name)
{
    $modelPath = __DIR__ . "/app/Models/{$name}.php";
    
    if (file_exists($modelPath)) {
        echo "Error: Model {$name} already exists.\n";
        return;
    }
    
    $tableName = strtolower($name) . 's';
    
    $template = "<?php

namespace App\Models;

class {$name} extends BaseModel
{
    protected static string \$table = '{$tableName}';
}
";
    
    file_put_contents($modelPath, $template);
    echo "Model {$name} created successfully!\n";
}

function makeMigration($name)
{
    $timestamp = date('Y_m_d_His');
    $fileName = "{$timestamp}_{$name}.php";
    $migrationPath = __DIR__ . "/database/migrations/{$fileName}";
    
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    
    $template = "<?php

use Core\Database;

class {$className}
{
    public function up()
    {
        \$db = Database::getInstance();
        
        // Add your migration logic here
        \$sql = \"CREATE TABLE example (
            id INT AUTO_INCREMENT PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )\";
        
        \$db->query(\$sql);
    }

    public function down()
    {
        \$db = Database::getInstance();
        \$db->query(\"DROP TABLE example\");
    }
}
";
    
    file_put_contents($migrationPath, $template);
    echo "Migration {$fileName} created successfully!\n";
}
```

## Helper Functions (core/helpers.php)
```php
<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $file = array_shift($keys);
        
        $configPath = __DIR__ . "/../config/{$file}.php";
        
        if (!file_exists($configPath)) {
            return $default;
        }
        
        $config = require $configPath;
        
        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                return $default;
            }
            $config = $config[$key];
        }
        
        return $config;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return env('APP_URL', 'http://localhost:8000') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return env('APP_URL', 'http://localhost:8000') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        exit;
    }
}
```

### Update composer.json to include helpers
```json
{
    "name": "zenithphp/framework",
    "description": "A lightweight PHP MVC framework",
    "type": "project",
    "license": "MIT",
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Core\\": "core/"
        },
        "files": [
            "core/helpers.php"
        ]
    },
    "require": {
        "php": ">=8.0",
        "google/apiclient": "^2.12",
        "vlucas/phpdotenv": "^5.4"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5"
    },
    "scripts": {
        "post-create-project-cmd": [
            "cp .env.example .env"
        ]
    }
}
```