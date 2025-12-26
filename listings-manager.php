<?php
/**
 * Plugin Name: Listings Manager
 * Plugin URI: https://github.com/richard02segura-ux/listings-manager
 * Description: Plugin para generar automáticamente listings con Google Places y OpenAI
 * Version: 1.0.0
 * Author: Manus AI
 * Text Domain: listings-manager
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Definición de constantes.
define( 'LM_VERSION', '1.0.0' );
define( 'LM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Clase principal del plugin.
 */
class Listings_Manager {

	/**
	 * Instancia única de la clase.
	 */
	private static $instance = null;

	/**
	 * Obtener la instancia única.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Incluir archivos necesarios.
	 */
	private function includes() {
		require_once LM_PLUGIN_DIR . 'includes/settings.php';
		require_once LM_PLUGIN_DIR . 'includes/logger.php';
		require_once LM_PLUGIN_DIR . 'includes/google-places.php';
		require_once LM_PLUGIN_DIR . 'includes/ai-content.php';
		require_once LM_PLUGIN_DIR . 'includes/listings-functions.php';
		require_once LM_PLUGIN_DIR . 'includes/admin-panel.php';
		require_once LM_PLUGIN_DIR . 'includes/queue-manager.php';
		require_once LM_PLUGIN_DIR . 'includes/cache-handler.php';
		require_once LM_PLUGIN_DIR . 'includes/import-export.php';
		require_once LM_PLUGIN_DIR . 'includes/category-mapper.php';
	}

	/**
	 * Inicializar hooks.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Cargar traducciones.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'listings-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Hook de activación.
	 */
	public function activate() {
		// Crear tabla de cola si es necesario.
		if ( class_exists( 'LM_Queue_Manager' ) ) {
			LM_Queue_Manager::create_table();
		}
		
		// Crear directorio de logs y protegerlo.
		$log_dir = LM_PLUGIN_DIR . 'logs';
		if ( ! file_exists( $log_dir ) ) {
			mkdir( $log_dir, 0755, true );
		}
		
		$htaccess_content = "Order deny,allow\nDeny from all";
		file_put_contents( $log_dir . '/.htaccess', $htaccess_content );
		
		flush_rewrite_rules();
	}

	/**
	 * Hook de desactivación.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}
}

/**
 * Inicializar el plugin.
 */
function lm_init() {
	return Listings_Manager::get_instance();
}

add_action( 'plugins_loaded', 'lm_init' );
