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
		add_action( 'wp_ajax_lm_generate_single', array( $this, 'ajax_generate_single' ) );
	}

	/**
	 * Manejador AJAX para generar una ficha individual.
	 */
	public function ajax_generate_single() {
		check_ajax_referer( 'lm_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permisos insuficientes.' ) );
		}

		$place_id = sanitize_text_field( $_POST['place_id'] );
		$niche = isset( $_POST['niche'] ) ? sanitize_text_field( $_POST['niche'] ) : 'generic';
		
		if ( empty( $place_id ) ) {
			wp_send_json_error( array( 'message' => 'Place ID no proporcionado.' ) );
		}

		$google = new LM_Google_Places();
		$place_data = $google->get_place_details( $place_id );

		if ( is_wp_error( $place_data ) ) {
			wp_send_json_error( array( 'message' => $place_data->get_error_message() ) );
		}

		$ai = new LM_AI_Content();
		$description = $ai->generate_description( $place_data, array( 'niche' => $niche ) );
		$seo_meta = $ai->generate_seo_meta( $place_data );

		$post_id = lm_create_listing( $place_data, $description, $seo_meta );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( array( 'message' => $post_id->get_error_message() ) );
		}

		// Descargar fotos si existen.
		if ( isset( $place_data['photos'] ) ) {
			$photo_ids = $google->download_place_photos( $place_id, $place_data['photos'] );
			if ( ! empty( $photo_ids ) ) {
				set_post_thumbnail( $post_id, $photo_ids[0] );
				update_post_meta( $post_id, '_gallery', $photo_ids );
			}
		}

		wp_send_json_success( array( 
			'message' => 'Ficha creada correctamente. <a href="' . get_edit_post_link( $post_id ) . '" target="_blank">Ver ficha</a>',
			'post_id' => $post_id 
		) );
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
