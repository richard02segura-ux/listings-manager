<?php
/**
 * Funciones de importación y exportación CSV.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Import_Export {

	/**
	 * Guardar datos en un CSV espejo para control en Excel.
	 */
	public static function log_to_mirror_csv( $data ) {
		$log_dir = LM_PLUGIN_DIR . 'logs';
		$file_path = $log_dir . '/google-places-mirror.csv';
		
		$is_new = ! file_exists( $file_path );
		$handle = fopen( $file_path, 'a' );

		if ( $is_new ) {
			fputcsv( $handle, array( 'Timestamp', 'Place ID', 'Nombre', 'Dirección', 'Teléfono', 'Web', 'Rating', 'Categorías Google' ) );
		}

		fputcsv( $handle, array(
			date( 'Y-m-d H:i:s' ),
			$data['place_id'],
			$data['name'],
			isset( $data['formatted_address'] ) ? $data['formatted_address'] : '',
			isset( $data['formatted_phone_number'] ) ? $data['formatted_phone_number'] : '',
			isset( $data['website'] ) ? $data['website'] : '',
			isset( $data['rating'] ) ? $data['rating'] : '',
			implode( ', ', $data['types'] )
		) );

		fclose( $handle );
	}

	/**
	 * Importar desde un archivo CSV.
	 */
	public static function import_csv( $file_path ) {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return new WP_Error( 'file_error', 'No se puede leer el archivo CSV.' );
		}

		$handle = fopen( $file_path, 'r' );
		$header = fgetcsv( $handle ); // Asumimos que la primera línea es el encabezado.
		
		$count = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$data = array_combine( $header, $row );
			if ( isset( $data['place_id'] ) ) {
				LM_Queue_Manager::add_to_queue( $data['place_id'], $data );
				$count++;
			}
		}
		fclose( $handle );

		return $count;
	}

	/**
	 * Exportar listings a CSV.
	 */
	public static function export_csv() {
		$args = array(
			'post_type'      => 'listing',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$posts = get_posts( $args );
		
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="listings-export-' . date( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'ID', 'Título', 'Place ID', 'Dirección', 'Teléfono', 'Web' ) );

		foreach ( $posts as $post ) {
			fputcsv( $output, array(
				$post->ID,
				$post->post_title,
				get_post_meta( $post->ID, '_place_id', true ),
				get_post_meta( $post->ID, '_address', true ),
				get_post_meta( $post->ID, '_phone', true ),
				get_post_meta( $post->ID, '_website', true ),
			) );
		}
		fclose( $output );
		exit;
	}
}
