<?php
/**
 * Manejador de caché usando Transients API.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Cache_Handler {

	/**
	 * Guardar en caché.
	 */
	public static function set( $key, $value, $expiration = DAY_IN_SECONDS ) {
		return set_transient( 'lm_cache_' . $key, $value, $expiration );
	}

	/**
	 * Obtener de caché.
	 */
	public static function get( $key ) {
		return get_transient( 'lm_cache_' . $key );
	}

	/**
	 * Eliminar de caché.
	 */
	public static function delete( $key ) {
		return delete_transient( 'lm_cache_' . $key );
	}

	/**
	 * Limpiar todo el caché del plugin.
	 */
	public static function flush_all() {
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_lm_cache_%' OR option_name LIKE '_transient_timeout_lm_cache_%'" );
	}
}
