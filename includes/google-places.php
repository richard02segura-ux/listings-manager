<?php
/**
 * Integración con Google Places API.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_Google_Places {

	private $api_key;
	private $base_url = 'https://maps.googleapis.com/maps/api/place/';

	public function __construct() {
		$this->api_key = LM_Settings::get_option( 'google_places_api_key' );
	}

	/**
	 * Obtener detalles de un lugar por su Place ID.
	 */
	public function get_place_details( $place_id ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', 'Google Places API Key no configurada.' );
		}

		$cache_key = 'lm_place_' . $place_id;
		$cached_data = get_transient( $cache_key );
		if ( $cached_data ) {
			return $cached_data;
		}

		$url = $this->base_url . 'details/json?place_id=' . $place_id . '&key=' . $this->api_key . '&language=es';
		
		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( isset( $response['result'] ) ) {
			set_transient( $cache_key, $response['result'], DAY_IN_SECONDS );
			return $response['result'];
		}

		return new WP_Error( 'api_error', 'Error en la respuesta de Google Places: ' . ( isset( $response['status'] ) ? $response['status'] : 'Unknown' ) );
	}

	/**
	 * Buscar lugares por una consulta.
	 */
	public function search_places( $query, $location = '' ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', 'Google Places API Key no configurada.' );
		}

		$url = $this->base_url . 'textsearch/json?query=' . urlencode( $query ) . '&key=' . $this->api_key . '&language=es';
		
		if ( ! empty( $location ) ) {
			$url .= '&location=' . urlencode( $location );
		}

		$response = $this->make_request( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return isset( $response['results'] ) ? $response['results'] : array();
	}

	/**
	 * Realizar la petición a la API con reintentos.
	 */
	private function make_request( $url, $retries = 3 ) {
		$attempt = 0;
		while ( $attempt < $retries ) {
			$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

			if ( is_wp_error( $response ) ) {
				$attempt++;
				LM_Logger::log( "Intento {$attempt} fallido para URL: {$url}. Error: " . $response->get_error_message(), 'WARNING' );
				if ( $attempt >= $retries ) {
					return $response;
				}
				sleep( 1 );
				continue;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( isset( $data['status'] ) && $data['status'] === 'OK' ) {
				return $data;
			}

			if ( isset( $data['status'] ) && $data['status'] === 'OVER_QUERY_LIMIT' ) {
				$attempt++;
				LM_Logger::log( "Límite de cuota alcanzado. Intento {$attempt}.", 'WARNING' );
				sleep( 2 );
				continue;
			}

			return $data;
		}

		return new WP_Error( 'max_retries', 'Se alcanzó el máximo de reintentos.' );
	}

	/**
	 * Descargar fotos de un lugar.
	 */
	public function download_place_photos( $place_id, $photo_references ) {
		if ( empty( $this->api_key ) || empty( $photo_references ) ) {
			return array();
		}

		$downloaded_ids = array();
		foreach ( array_slice( $photo_references, 0, 5 ) as $photo ) {
			$photo_ref = is_array( $photo ) ? $photo['photo_reference'] : $photo;
			$url = 'https://maps.googleapis.com/maps/api/place/photo?maxwidth=1200&photoreference=' . $photo_ref . '&key=' . $this->api_key;
			
			$image_id = $this->download_image( $url, $place_id . '-' . substr( $photo_ref, 0, 10 ) );
			if ( $image_id ) {
				$downloaded_ids[] = $image_id;
			}
		}

		return $downloaded_ids;
	}

	/**
	 * Helper para descargar e insertar imagen en la biblioteca de medios.
	 */
	private function download_image( $url, $filename ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			return false;
		}

		$file_array = array(
			'name'     => $filename . '.jpg',
			'tmp_name' => $tmp,
		);

		$id = media_handle_sideload( $file_array, 0 );

		if ( is_wp_error( $id ) ) {
			@unlink( $tmp );
			return false;
		}

		return $id;
	}
}
