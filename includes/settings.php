<?php
/**
 * Manejo de configuraciones del plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Settings {

	/**
	 * Nombre de la opción en la base de datos.
	 */
	private static $option_name = 'lm_settings';

	/**
	 * Obtener una opción específica.
	 */
	public static function get_option( $key, $default = '' ) {
		$options = get_option( self::$option_name, array() );
		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	/**
	 * Guardar una opción.
	 */
	public static function set_option( $key, $value ) {
		$options = get_option( self::$option_name, array() );
		$options[ $key ] = $value;
		return update_option( self::$option_name, $options );
	}

	/**
	 * Eliminar una opción.
	 */
	public static function delete_option( $key ) {
		$options = get_option( self::$option_name, array() );
		if ( isset( $options[ $key ] ) ) {
			unset( $options[ $key ] );
			return update_option( self::$option_name, $options );
		}
		return false;
	}

	/**
	 * Obtener todas las opciones.
	 */
	public static function get_all_options() {
		return get_option( self::$option_name, array() );
	}
}
