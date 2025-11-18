# WP Logger - Quick Usage Guide

## Files Required

**Only one file is required for full functionality**
- ✅ `class-ls-logger.php` – Contains the entire system (logger + admin UI)

**Optional helpers**
- `ls-example-logger.php` – Cron + helper examples
- `README.md` – Full documentation
- `usage-guide.md` – This quick reference

## All Available Log Functions

### 1. Main log function
```php
wp_logger::log( $message, $level, $context );
```
**Example**
```php
wp_logger::log( 'Something happened', 'info', array( 'source' => 'my-module' ) );
```

### 2. Log level helpers (6 methods)
```php
// 1. Info - General information
wp_logger::info( $message, $context );

// 2. Debug - Debug information
wp_logger::debug( $message, $context );

// 3. Notice - Notices
wp_logger::notice( $message, $context );

// 4. Warning - Warnings
wp_logger::warning( $message, $context );

// 5. Error - Errors
wp_logger::error( $message, $context );

// 6. Critical - Critical errors
wp_logger::critical( $message, $context );
```

**Examples**
```php
wp_logger::info( 'User logged in', array( 'source' => 'auth', 'user_id' => 123 ) );
wp_logger::debug( 'Debug data', array( 'source' => 'debug', 'data' => $data ) );
wp_logger::warning( 'Low memory warning', array( 'source' => 'system' ) );
wp_logger::error( 'API call failed', array( 'source' => 'api', 'url' => $url ) );
wp_logger::critical( 'Database connection lost', array( 'source' => 'database' ) );
```

### 3. Helper functions (2 helpers)
```php
// 1. Get logger instance (WooCommerce-style)
$logger = wp_get_logger( $source );

// 2. Log cron events (auto-detect hook name as source)
wp_log_cron( $message, $level, $context );
```

**Examples**
```php
// Get logger instance
$logger = wp_get_logger( 'my-module' );

// Log inside a cron callback
add_action( 'my_cron_hook', function() {
    wp_log_cron( 'Cron started', 'info' );
    // Your code here
    wp_log_cron( 'Cron completed', 'info' );
} );
```

### 4. Utility methods (4 methods)
```php
// 1. Get all log files
$files = wp_logger::get_log_files();

// 2. Get log content
$content = wp_logger::get_log_content( $file_path );

// 3. Cleanup old logs
$deleted = wp_logger::cleanup( $days ); // Default: 30

// 4. Get log directory
$dir = wp_logger::get_log_dir();
```

**Examples**
```php
// List log files
$files = wp_logger::get_log_files();
foreach ( $files as $file ) {
    echo basename( $file ) . "\n";
}

// Read a log file
$content = wp_logger::get_log_content( '/path/to/log.log' );

// Delete logs older than 30 days
$deleted = wp_logger::cleanup( 30 );
echo "Deleted {$deleted} log files";

// Get log directory path
$log_dir = wp_logger::get_log_dir();
```

## Summary: total functions available

| Category | Count | Functions |
|----------|-------|-----------|
| **Main log function** | 1 | `wp_logger::log()` |
| **Log level helpers** | 6 | `info()`, `debug()`, `notice()`, `warning()`, `error()`, `critical()` |
| **Helper functions** | 2 | `wp_get_logger()`, `wp_log_cron()` |
| **Utility methods** | 4 | `get_log_files()`, `get_log_content()`, `cleanup()`, `get_log_dir()` |
| **TOTAL** | **13** | All logging helpers |

## Quick reference – most common usage

### Basic logging
```php
wp_logger::info( 'Message here' );
wp_logger::error( 'Error occurred' );
```

### With source (creates a separate log file)
```php
wp_logger::info( 'Message', array( 'source' => 'my-cron-hook' ) );
```

### In cron events
```php
add_action( 'my_cron_hook', function() {
    wp_log_cron( 'Started', 'info' );
    // Your code
    wp_log_cron( 'Completed', 'info' );
} );
```

### With context data
```php
wp_logger::info( 'User action', array(
    'source' => 'user-actions',
    'user_id' => 123,
    'action' => 'login',
    'ip' => $_SERVER['REMOTE_ADDR'],
) );
```

## Installation

**Minimum setup (just one line)**
```php
require_once get_stylesheet_directory() . '/logger/class-ls-logger.php';
```

That's it! The logger is ready to use everywhere in your project.

