<?php
/**
 * Sistema de logging para el plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Logger {

	/**
	 * Registrar un mensaje en los logs.
	 */
	public static function log( $message, $level = 'INFO', $context = array() ) {
		$log_dir = LM_PLUGIN_DIR . 'logs';
		$file_name = strtolower( $level ) === 'error' ? 'api-errors.log' : 'processing.log';
		$file_path = $log_dir . '/' . $file_name;

		$timestamp = date( 'Y-m-d H:i:s' );
		$context_str = ! empty( $context ) ? ' | Context: ' . json_encode( $context ) : '';
		$log_entry = "[{$timestamp}] [{$level}] {$message}{$context_str}\n";

		// Asegurar que el directorio existe.
		if ( ! file_exists( $log_dir ) ) {
			mkdir( $log_dir, 0755, true );
		}

		error_log( $log_entry, 3, $file_path );
		
		// Rotación básica (si el archivo supera 5MB, podrías implementar algo más complejo).
		if ( file_exists( $file_path ) && filesize( $file_path ) > 5 * 1024 * 1024 ) {
			rename( $file_path, $file_path . '.' . time() . '.bak' );
		}
	}

	/**
	 * Limpiar logs antiguos.
	 */
	public static function clear_logs() {
		$log_dir = LM_PLUGIN_DIR . 'logs';
		$files = glob( $log_dir . '/*.log*' );
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}
	}
}
