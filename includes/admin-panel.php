<?php
/**
 * Panel de administración del plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Admin_Panel {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_lm_preview_listing', array( $this, 'ajax_preview_listing' ) );
		add_action( 'wp_ajax_lm_confirm_import', array( $this, 'ajax_confirm_import' ) );
	}

	/**
	 * AJAX: Pre-visualizar datos antes de importar.
	 */
	public function ajax_preview_listing() {
		check_ajax_referer( 'lm_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'No autorizado' ) );

		$place_id = sanitize_text_field( $_POST['place_id'] );
		$niche = sanitize_text_field( $_POST['niche'] );

		$google = new LM_Google_Places();
		$place_data = $google->get_place_details( $place_id );
		if ( is_wp_error( $place_data ) ) wp_send_json_error( array( 'message' => $place_data->get_error_message() ) );

		$ai = new LM_AI_Content();
		$description = $ai->generate_description( $place_data, array( 'niche' => $niche ) );
		
		// Guardar en CSV "Espejo" automáticamente.
		LM_Import_Export::log_to_mirror_csv( $place_data );

		wp_send_json_success( array(
			'place_id'    => $place_id,
			'name'        => $place_data['name'],
			'address'     => isset($place_data['formatted_address']) ? $place_data['formatted_address'] : '',
			'phone'       => isset($place_data['formatted_phone_number']) ? $place_data['formatted_phone_number'] : '',
			'description' => $description,
			'niche'       => $niche
		) );
	}

	/**
	 * AJAX: Confirmar e importar a WordPress.
	 */
	public function ajax_confirm_import() {
		check_ajax_referer( 'lm_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'No autorizado' ) );

		$place_id = sanitize_text_field( $_POST['place_id'] );
		$description = wp_kses_post( $_POST['description'] );
		
		$google = new LM_Google_Places();
		$place_data = $google->get_place_details( $place_id );
		
		$post_id = lm_create_listing( $place_data, $description );
		
		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => 'Importado con éxito', 'url' => get_edit_post_link($post_id) ) );
	}

	/**
	 * Agregar menús al escritorio de WordPress.
	 */
	public function add_menu() {
		add_menu_page(
			'Listings Manager',
			'Listings Manager',
			'manage_options',
			'listings-manager',
			array( $this, 'render_dashboard' ),
			'dashicons-location-alt',
			30
		);

		add_submenu_page(
			'listings-manager',
			'Dashboard',
			'Dashboard',
			'manage_options',
			'listings-manager',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'listings-manager',
			'Generar Fichas',
			'Generar Fichas',
			'manage_options',
			'lm-generate',
			array( $this, 'render_generate' )
		);

		add_submenu_page(
			'listings-manager',
			'Configuración',
			'Configuración',
			'manage_options',
			'lm-settings',
			array( $this, 'render_settings' )
		);

		add_submenu_page(
			'listings-manager',
			'Logs',
			'Logs',
			'manage_options',
			'lm-logs',
			array( $this, 'render_logs' )
		);
	}

	/**
	 * Cargar assets para el admin.
	 */
	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'listings-manager' ) === false && strpos( $hook, 'lm-' ) === false ) {
			return;
		}

		wp_enqueue_style( 'lm-admin-css', LM_PLUGIN_URL . 'assets/css/admin.css', array(), LM_VERSION );
		wp_enqueue_script( 'lm-admin-js', LM_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), LM_VERSION, true );
		
		wp_localize_script( 'lm-admin-js', 'lm_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'lm_nonce' ),
		) );
	}

	/**
	 * Renderizar Dashboard.
	 */
	public function render_dashboard() {
		include LM_PLUGIN_DIR . 'templates/admin/dashboard.php';
	}

	/**
	 * Renderizar Generar Fichas.
	 */
	public function render_generate() {
		include LM_PLUGIN_DIR . 'templates/admin/bulk-import.php';
	}

	/**
	 * Renderizar Configuración.
	 */
	public function render_settings() {
		if ( isset( $_POST['lm_save_settings'] ) && check_admin_referer( 'lm_settings_nonce' ) ) {
			$this->save_settings();
			echo '<div class="updated"><p>Configuración guardada.</p></div>';
		}
		include LM_PLUGIN_DIR . 'templates/admin/settings.php';
	}

	/**
	 * Renderizar Logs.
	 */
	public function render_logs() {
		include LM_PLUGIN_DIR . 'templates/admin/logs-viewer.php';
	}

	/**
	 * Guardar configuraciones.
	 */
	private function save_settings() {
		$fields = array(
			'google_places_api_key',
			'openai_api_key',
			'gemini_api_key',
			'ai_provider',
			'base_location',
			'enable_auto_sync',
			'ai_length',
			'ai_tone',
			'ai_language',
		);

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				LM_Settings::set_option( $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}
	}
}

new LM_Admin_Panel();
