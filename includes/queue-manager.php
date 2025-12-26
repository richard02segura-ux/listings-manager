<?php
/**
 * Gestor de colas para procesamiento por lotes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Queue_Manager {

	/**
	 * Crear la tabla de la cola en la base de datos.
	 */
	public static function create_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'lm_queue';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			place_id varchar(100) NOT NULL,
			status varchar(20) DEFAULT 'pending',
			data longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			processed_at datetime DEFAULT NULL,
			error_message text DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Añadir un item a la cola.
	 */
	public static function add_to_queue( $place_id, $extra_data = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'lm_queue';

		return $wpdb->insert(
			$table_name,
			array(
				'place_id' => $place_id,
				'data'     => json_encode( $extra_data ),
				'status'   => 'pending',
			)
		);
	}

	/**
	 * Procesar el siguiente item de la cola.
	 */
	public static function process_next() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'lm_queue';

		$item = $wpdb->get_row( "SELECT * FROM $table_name WHERE status = 'pending' ORDER BY id ASC LIMIT 1" );

		if ( ! $item ) {
			return false;
		}

		// Marcar como procesando.
		$wpdb->update( $table_name, array( 'status' => 'processing' ), array( 'id' => $item->id ) );

		$google = new LM_Google_Places();
		$place_data = $google->get_place_details( $item->place_id );

		if ( is_wp_error( $place_data ) ) {
			$wpdb->update( $table_name, array( 
				'status' => 'failed', 
				'error_message' => $place_data->get_error_message() 
			), array( 'id' => $item->id ) );
			return false;
		}

		$ai = new LM_AI_Content();
		$description = $ai->generate_description( $place_data );
		$seo_meta = $ai->generate_seo_meta( $place_data );

		$post_id = lm_create_listing( $place_data, $description, $seo_meta );

		if ( is_wp_error( $post_id ) ) {
			$wpdb->update( $table_name, array( 
				'status' => 'failed', 
				'error_message' => $post_id->get_error_message() 
			), array( 'id' => $item->id ) );
			return false;
		}

		// Éxito.
		$wpdb->update( $table_name, array( 
			'status' => 'completed', 
			'processed_at' => current_time( 'mysql' ) 
		), array( 'id' => $item->id ) );

		return $post_id;
	}
}
