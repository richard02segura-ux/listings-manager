<?php
/**
 * Generación de contenido con OpenAI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_AI_Content {

	private $api_key;

	public function __construct() {
		$this->api_key = LM_Settings::get_option( 'openai_api_key' );
	}

	/**
	 * Generar descripción optimizada para un negocio.
	 */
	public function generate_description( $business_data, $options = array() ) {
		if ( empty( $this->api_key ) ) {
			return $this->get_fallback_description( $business_data );
		}

		$length = isset( $options['length'] ) ? $options['length'] : LM_Settings::get_option( 'ai_length', '500' );
		$tone = isset( $options['tone'] ) ? $options['tone'] : LM_Settings::get_option( 'ai_tone', 'profesional' );
		$language = isset( $options['language'] ) ? $options['language'] : LM_Settings::get_option( 'ai_language', 'es' );

		$prompt = $this->build_prompt( $business_data, $length, $tone, $language );

		$response = $this->make_openai_request( $prompt );

		if ( is_wp_error( $response ) ) {
			LM_Logger::log( 'Error OpenAI: ' . $response->get_error_message(), 'ERROR' );
			return $this->get_fallback_description( $business_data );
		}

		return $response;
	}

	/**
	 * Generar meta datos SEO.
	 */
	public function generate_seo_meta( $business_data ) {
		if ( empty( $this->api_key ) ) {
			return array(
				'title' => $business_data['name'] . ' - Detalles y Opiniones',
				'description' => 'Descubre todo sobre ' . $business_data['name'] . ' en nuestra guía completa.',
			);
		}

		$prompt = "Genera un título SEO (máx 60 caracteres) y una meta descripción (máx 160 caracteres) para el siguiente negocio: " . $business_data['name'] . ". Formato JSON: {\"title\": \"...\", \"description\": \"...\"}";
		
		$response = $this->make_openai_request( $prompt, true );

		if ( is_wp_error( $response ) ) {
			return array(
				'title' => $business_data['name'],
				'description' => '',
			);
		}

		$data = json_decode( $response, true );
		return $data ? $data : array( 'title' => $business_data['name'], 'description' => '' );
	}

	/**
	 * Construir el prompt para la descripción.
	 */
	private function build_prompt( $data, $length, $tone, $language ) {
		$name = $data['name'];
		$types = implode( ', ', $data['types'] );
		$address = isset( $data['formatted_address'] ) ? $data['formatted_address'] : '';
		
		$prompt = "Actúa como un experto en SEO y redacción de contenidos para directorios de negocios. ";
		$prompt .= "Escribe una descripción detallada para el negocio '{$name}', que es de tipo: {$types}. ";
		if ( $address ) {
			$prompt .= "Ubicado en: {$address}. ";
		}
		$prompt .= "El texto debe tener aproximadamente {$length} palabras, con un tono {$tone} y en idioma {$language}. ";
		$prompt .= "Incluye secciones sobre servicios, por qué elegirlos y qué esperar. Usa formato HTML básico (p, h2, ul, li).";
		
		return $prompt;
	}

	/**
	 * Realizar petición a OpenAI.
	 */
	private function make_openai_request( $prompt, $json_mode = false ) {
		$url = 'https://api.openai.com/v1/chat/completions';
		
		$body = array(
			'model' => 'gpt-3.5-turbo',
			'messages' => array(
				array( 'role' => 'user', 'content' => $prompt )
			),
			'temperature' => 0.7,
		);

		if ( $json_mode ) {
			$body['response_format'] = array( 'type' => 'json_object' );
		}

		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->api_key,
				'Content-Type'  => 'application/json',
			),
			'body'    => json_encode( $body ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			return trim( $data['choices'][0]['message']['content'] );
		}

		return new WP_Error( 'openai_error', isset( $data['error']['message'] ) ? $data['error']['message'] : 'Error desconocido de OpenAI' );
	}

	/**
	 * Contenido de respaldo si falla la IA.
	 */
	private function get_fallback_description( $data ) {
		$desc = "<p>" . sprintf( __( 'Bienvenido a %s. Somos un negocio especializado en %s.', 'listings-manager' ), $data['name'], implode( ', ', $data['types'] ) ) . "</p>";
		if ( isset( $data['formatted_address'] ) ) {
			$desc .= "<p>" . sprintf( __( 'Nos encontramos ubicados en %s.', 'listings-manager' ), $data['formatted_address'] ) . "</p>";
		}
		return $desc;
	}
}
