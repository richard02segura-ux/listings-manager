<?php
/**
 * Funciones de importación y exportación CSV.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Import_Export {

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
