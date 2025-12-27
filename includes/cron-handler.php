<?php
/**
 * Manejador de tareas programadas (WP-Cron) para sincronización.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Cron_Handler {

	public static function init() {
		add_action( 'lm_daily_sync_event', array( __CLASS__, 'sync_listings' ) );
		
		if ( ! wp_next_scheduled( 'lm_daily_sync_event' ) ) {
			wp_schedule_event( time(), 'daily', 'lm_daily_sync_event' );
		}
	}

	/**
	 * Sincronizar listings con Google Places.
	 */
	public static function sync_listings() {
		if ( LM_Settings::get_option( 'enable_auto_sync', 'no' ) !== 'yes' ) {
			return;
		}

		$args = array(
			'post_type'      => 'listing',
			'posts_per_page' => 10, // Procesar en lotes pequeños para evitar timeouts.
			'meta_query'     => array(
				array(
					'key'     => '_place_id',
					'compare' => 'EXISTS',
				),
			),
		);

		$listings = get_posts( $args );
		$google = new LM_Google_Places();

		foreach ( $listings as $post ) {
			$place_id = get_post_meta( $post->ID, '_place_id', true );
			$place_data = $google->get_place_details( $place_id );

			if ( ! is_wp_error( $place_data ) ) {
				// Actualizar solo datos dinámicos.
				lm_save_listing_meta( $post->ID, $place_data );
				LM_Logger::log( "Sincronización automática completada para: {$post->post_title}", 'SUCCESS' );
			}
		}
	}
}

LM_Cron_Handler::init();
