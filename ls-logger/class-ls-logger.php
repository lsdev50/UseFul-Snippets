<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Logger Class - WooCommerce-style logging with admin UI
 * Handles logging, file management, and admin interface in a single class.
 * Supports source-based log files (like WooCommerce) for per-cron tracking.
 *
 * @since 1.0.0
 * @version 2.0.0
 */
if ( ! class_exists( 'wp_logger' ) ) {

	class wp_logger {

		/**
		 * @var string Directory path for storing log files
		 */
		private static $log_dir;

		/**
		 * @var string Capability required to view logs
		 */
		private static $capability = 'manage_options';

		/**
		 * @var string Text domain for translations
		 */
		private static $text_domain = 'wp-logger';

		/**
		 * @var string Default source for logs
		 */
		private static $default_source = 'general';

		/**
		 * @var wp_logger Singleton instance
		 */
		private static $instance = null;

		/**
		 * @var string Current source context
		 */
		private $source = '';

		/**
		 * Initialize the logger and register hooks
		 *
		 * @param string $source Optional source identifier for log file naming
		 */
		public function __construct( $source = '' ) {
			$this->source = ! empty( $source ) ? sanitize_file_name( $source ) : self::$default_source;
			$this->setup_log_directory();
			$this->register_admin_hooks();
			$this->register_cron_tracking();
		}

		/**
		 * Get singleton instance
		 *
		 * @param string $source Optional source identifier
		 * @return wp_logger
		 */
		public static function get_instance( $source = '' ) {
			if ( null === self::$instance ) {
				self::$instance = new self( $source );
			}
			return self::$instance;
		}

		/**
		 * Create log directory if it doesn't exist
		 */
		private function setup_log_directory() {
			self::$log_dir = wp_upload_dir()['basedir'] . '/wp-logs/';
			if ( ! file_exists( self::$log_dir ) ) {
				wp_mkdir_p( self::$log_dir );
				// Create index.php for security
				file_put_contents( self::$log_dir . 'index.php', '<?php // Silence is golden' );
			}
		}

		/**
		 * Register WordPress admin menu and hooks
		 */
		private function register_admin_hooks() {
			add_action( 'admin_menu', array( $this, 'add_admin_menu_page' ) );
			add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
		}

		/**
		 * Register automatic cron event tracking
		 * Note: Automatic tracking is disabled by default for performance.
		 * Use wp_log_cron() helper function in your cron callbacks instead.
		 */
		private function register_cron_tracking() {
			// Automatic tracking can be enabled here if needed
			// For now, we rely on manual logging via wp_log_cron() helper
		}

		/**
		 * Add menu page to WordPress admin
		 */
		public function add_admin_menu_page() {
			add_menu_page(
				__( 'WP Logs', self::$text_domain ),
				__( 'WP Logs', self::$text_domain ),
				self::$capability,
				'wp-logs',
				array( $this, 'render_admin_page' ),
				'dashicons-media-text',
				80
			);
		}

		/**
		 * Render the admin page for viewing logs
		 */
		public function render_admin_page() {
			if ( ! current_user_can( self::$capability ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', self::$text_domain ) );
			}

			$files    = self::get_log_files();
			$selected = isset( $_GET['log'] ) ? sanitize_file_name( $_GET['log'] ) : '';
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'WP Logger', self::$text_domain ); ?></h1>

				<div style="display:flex; gap:20px; margin-top:20px;">
					<!-- Sidebar with log file list -->
					<div style="width:25%; flex-shrink:0;">
						<h3><?php esc_html_e( 'Log Files', self::$text_domain ); ?></h3>
						<?php if ( ! empty( $files ) ) : ?>
							<ul style="list-style:none; padding:0; border:1px solid #ddd; background:#f9f9f9; border-radius:4px; max-height:600px; overflow-y:auto;">
								<?php foreach ( $files as $file ) : ?>
									<?php $basename = basename( $file ); ?>
									<li style="border-bottom:1px solid #eee;margin-bottom:0px;">
										<a href="<?php echo esc_url( add_query_arg( 'log', $basename ) ); ?>" style="display:block; padding:10px; text-decoration:none; color:#0073aa; transition:background 0.2s;" onmouseover="this.style.backgroundColor='#f0f0f0';" onmouseout="this.style.backgroundColor='';">
											<?php echo esc_html( $basename ); ?>
											<br><small style="color:#666;"><?php echo esc_html( size_format( filesize( $file ) ) ); ?> - <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), filemtime( $file ) ) ); ?></small>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
							<form method="post" style="margin-top:10px;">
								<?php wp_nonce_field( 'wp_logger_cleanup' ); ?>
								<input type="hidden" name="action" value="cleanup">
								<button type="submit" class="button" onclick="return confirm('<?php esc_attr_e( 'Delete logs older than 30 days?', self::$text_domain ); ?>');">
									<?php esc_html_e( 'Cleanup Old Logs', self::$text_domain ); ?>
								</button>
							</form>
						<?php else : ?>
							<p style="color:#999;"><?php esc_html_e( 'No log files found.', self::$text_domain ); ?></p>
						<?php endif; ?>
					</div>

					<!-- Main content area -->
					<div style="flex:1;">
						<?php if ( $selected ) : ?>
							<?php $file_path = self::$log_dir . $selected; ?>
							<?php if ( file_exists( $file_path ) && $this->is_valid_log_file( $file_path ) ) : ?>
								<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
									<h2 style="margin:0;"><?php printf( esc_html__( 'Viewing: %s', self::$text_domain ), esc_html( $selected ) ); ?></h2>
									<div>
										<a href="<?php echo esc_url( add_query_arg( array( 'log' => $selected, 'download' => '1' ) ) ); ?>" class="button"><?php esc_html_e( 'Download', self::$text_domain ); ?></a>
										<form method="post" style="display:inline;">
											<?php wp_nonce_field( 'wp_logger_delete_' . $selected ); ?>
											<input type="hidden" name="log" value="<?php echo esc_attr( $selected ); ?>">
											<input type="hidden" name="action" value="delete">
											<button type="submit" class="button button-danger" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this log?', self::$text_domain ); ?>');">
												<?php esc_html_e( 'Delete Log', self::$text_domain ); ?>
											</button>
										</form>
									</div>
								</div>
								<div style="background:#fff; border:1px solid #ddd; border-radius:4px; padding:15px; max-height:700px; overflow-y:auto; font-family:monospace; font-size:12px; line-height:1.5; white-space:pre-line; word-wrap:break-word;">
									<?php echo esc_html( self::get_log_content( $file_path ) ); ?>
								</div>
							<?php else : ?>
								<p style="color:#d63638; font-weight:bold;"><?php esc_html_e( 'File not found or invalid.', self::$text_domain ); ?></p>
							<?php endif; ?>
						<?php else : ?>
							<div style="background:#f0f6fc; border:1px solid #72aee6; border-radius:4px; padding:20px;">
								<h2><?php esc_html_e( 'Welcome to WP Logger', self::$text_domain ); ?></h2>
								<p><?php esc_html_e( 'Select a log file from the list to view its contents.', self::$text_domain ); ?></p>
								<h3><?php esc_html_e( 'Usage Examples:', self::$text_domain ); ?></h3>
								<pre style="background:#fff; padding:15px; border:1px solid #ddd; border-radius:4px; overflow-x:auto;"><?php
echo esc_html( "// Basic logging
wp_logger::log( 'Message here', 'info' );

// With source (creates separate log file)
wp_logger::log( 'Cron executed', 'info', array( 'source' => 'my-cron-hook' ) );

// Using helper methods
wp_logger::info( 'Info message', array( 'source' => 'my-cron' ) );
wp_logger::error( 'Error message', array( 'source' => 'my-cron' ) );

// For cron events (automatically uses hook name as source)
add_action( 'my_cron_hook', function() {
    wp_log_cron( 'Cron started', 'info' );
    // Your cron logic here
    wp_log_cron( 'Cron completed', 'info' );
});" );
								?></pre>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Handle admin actions like deleting logs
		 */
		public function handle_admin_actions() {
			if ( ! current_user_can( self::$capability ) ) {
				return;
			}

			// Handle log deletion
			if ( isset( $_POST['action'] ) && 'delete' === $_POST['action'] && isset( $_POST['log'] ) ) {
				$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
				$log   = sanitize_file_name( $_POST['log'] );

				if ( wp_verify_nonce( $nonce, 'wp_logger_delete_' . $log ) ) {
					$file_path = self::$log_dir . $log;
					if ( file_exists( $file_path ) && $this->is_valid_log_file( $file_path ) ) {
						unlink( $file_path );
						wp_safe_redirect( remove_query_arg( 'log' ) );
						exit;
					}
				}
			}

			// Handle cleanup
			if ( isset( $_POST['action'] ) && 'cleanup' === $_POST['action'] ) {
				$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
				if ( wp_verify_nonce( $nonce, 'wp_logger_cleanup' ) ) {
					self::cleanup( 30 );
					wp_safe_redirect( remove_query_arg( array( 'log', 'action' ) ) );
					exit;
				}
			}

			// Handle download
			if ( isset( $_GET['download'] ) && isset( $_GET['log'] ) ) {
				$log = sanitize_file_name( $_GET['log'] );
				$file_path = self::$log_dir . $log;
				if ( file_exists( $file_path ) && $this->is_valid_log_file( $file_path ) ) {
					header( 'Content-Type: text/plain' );
					header( 'Content-Disposition: attachment; filename="' . $log . '"' );
					readfile( $file_path );
					exit;
				}
			}
		}

		/**
		 * Verify that a file is a valid log file
		 */
		private function is_valid_log_file( $file_path ) {
			$file_path = realpath( $file_path );
			$log_dir   = realpath( self::$log_dir );
			return $file_path && $log_dir && strpos( $file_path, $log_dir ) === 0 && pathinfo( $file_path, PATHINFO_EXTENSION ) === 'log';
		}

		/**
		 * Get log file name based on source
		 *
		 * @param string $source Source identifier
		 * @return string Log file path
		 */
		private static function get_log_file_name( $source = '' ) {
			$instance = new self();
			$source   = ! empty( $source ) ? sanitize_file_name( $source ) : self::$default_source;
			$date     = gmdate( 'Y-m-d' );
			return self::$log_dir . 'log-' . $source . '-' . $date . '.log';
		}

		/**
		 * Write a log entry to file
		 *
		 * @param string $message Log message
		 * @param string $level Log level (info, warning, error, critical, debug)
		 * @param array  $context Additional context data (can include 'source' key)
		 */
		public static function log( $message, $level = 'info', $context = array() ) {
			$instance = new self();

			// Extract source from context or use default
			$source = isset( $context['source'] ) ? sanitize_file_name( $context['source'] ) : self::$default_source;
			$file   = self::get_log_file_name( $source );

			$timestamp = gmdate( 'Y-m-d H:i:s' );
			$level     = strtoupper( $level );

			// Build log entry
			$entry = "[$timestamp] $level: $message";
			
			// Add context data (excluding source as it's used for file naming)
			$log_context = $context;
			unset( $log_context['source'] );
			if ( ! empty( $log_context ) ) {
				$entry .= ' ' . wp_json_encode( $log_context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
			}
			$entry .= PHP_EOL;

			// Write to file
			error_log( $entry, 3, $file );
		}

		/**
		 * Handle dynamic log level methods like wp_logger::info() or wp_logger::error()
		 */
		public static function __callStatic( $name, $arguments ) {
			$allowed_levels = array( 'info', 'warning', 'error', 'critical', 'debug', 'notice' );
			if ( in_array( $name, $allowed_levels, true ) ) {
				// If first argument is a string, it's the message
				// If second argument exists and is an array, it's the context
				$message = isset( $arguments[0] ) ? $arguments[0] : '';
				$context = isset( $arguments[1] ) && is_array( $arguments[1] ) ? $arguments[1] : ( isset( $arguments[1] ) ? array( 'data' => $arguments[1] ) : array() );
				
				// If only one argument and it's an array, treat as context with default message
				if ( empty( $message ) && isset( $arguments[0] ) && is_array( $arguments[0] ) ) {
					$context = $arguments[0];
					$message = 'Log entry';
				}
				
				return self::log( $message, $name, $context );
			}
			throw new BadMethodCallException( "Undefined log level: {$name}" );
		}

		/**
		 * Retrieve all log files from the logs directory
		 *
		 * @return array Array of log file paths
		 */
		public static function get_log_files() {
			$instance = new self();
			$files    = glob( self::$log_dir . '*.log' );
			if ( ! $files ) {
				return array();
			}
			// Sort by modification time, newest first
			usort( $files, function( $a, $b ) {
				return filemtime( $b ) - filemtime( $a );
			} );
			return $files;
		}

		/**
		 * Read and return contents of a log file
		 *
		 * @param string $file File path
		 * @return string File contents
		 */
		public static function get_log_content( $file ) {
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				// Limit to last 500KB to prevent memory issues
				if ( strlen( $content ) > 500000 ) {
					$lines = explode( "\n", $content );
					$content = implode( "\n", array_slice( $lines, -5000 ) );
					$content = "... (showing last 5000 lines) ...\n" . $content;
				}
				return $content;
			}
			return '';
		}

		/**
		 * Delete log files older than specified number of days
		 *
		 * @param int $days Number of days
		 */
		public static function cleanup( $days = 30 ) {
			$cutoff = strtotime( "-$days days" );
			$deleted = 0;
			foreach ( self::get_log_files() as $file ) {
				if ( filemtime( $file ) < $cutoff ) {
					unlink( $file );
					$deleted++;
				}
			}
			return $deleted;
		}

		/**
		 * Get the log directory path
		 *
		 * @return string Log directory path
		 */
		public static function get_log_dir() {
			$instance = new self();
			return self::$log_dir;
		}

		/**
		 * Get logger instance for a specific source
		 *
		 * @param string $source Source identifier
		 * @return wp_logger Logger instance
		 */
		public static function get_logger( $source = '' ) {
			return new self( $source );
		}
	}

	/**
	 * Initialize the logger instance
	 */
	if ( is_admin() ) {
		new wp_logger();
	}
}

if ( ! function_exists( 'wp_get_logger' ) ) {
	/**
	 * Helper function to get logger instance (WooCommerce-style)
	 *
	 * @param string $source Optional source identifier
	 * @return wp_logger Logger instance
	 */
	function wp_get_logger( $source = '' ) {
		return wp_logger::get_logger( $source );
	}
}

if ( ! function_exists( 'wp_log_cron' ) ) {
	/**
	 * Helper function to log cron events (automatically uses current hook as source)
	 *
	 * @param string $message Log message
	 * @param string $level Log level
	 * @param array  $context Additional context
	 */
	function wp_log_cron( $message, $level = 'info', $context = array() ) {
		// Get current hook name from backtrace
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
		$hook_name = '';
		
		foreach ( $backtrace as $trace ) {
			if ( isset( $trace['function'] ) && ( 'do_action' === $trace['function'] || 'apply_filters' === $trace['function'] ) ) {
				if ( isset( $trace['args'][0] ) ) {
					$hook_name = sanitize_file_name( $trace['args'][0] );
					break;
				}
			}
		}
		
		// If no hook found, try to get from current filter
		if ( empty( $hook_name ) ) {
			global $wp_current_filter;
			if ( ! empty( $wp_current_filter ) ) {
				$hook_name = sanitize_file_name( end( $wp_current_filter ) );
			}
		}
		
		// Use hook name as source if found
		if ( ! empty( $hook_name ) ) {
			$context['source'] = $hook_name;
		}
		
		wp_logger::log( $message, $level, $context );
	}
}
