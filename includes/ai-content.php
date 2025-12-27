<?php
/**
 * Generación de contenido con Multi-IA (OpenAI + Gemini).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LM_AI_Content {

	private $provider;
	private $api_key;

	public function __construct() {
		$this->provider = LM_Settings::get_option( 'ai_provider', 'openai' );
		$this->api_key = ( $this->provider === 'gemini' ) ? LM_Settings::get_option( 'gemini_api_key' ) : LM_Settings::get_option( 'openai_api_key' );
	}

	/**
	 * Generar descripción optimizada.
	 */
	public function generate_description( $business_data, $options = array() ) {
		if ( empty( $this->api_key ) ) {
			return $this->get_fallback_description( $business_data );
		}

		$length = isset( $options['length'] ) ? $options['length'] : LM_Settings::get_option( 'ai_length', '500' );
		$tone = isset( $options['tone'] ) ? $options['tone'] : LM_Settings::get_option( 'ai_tone', 'profesional' );
		$language = isset( $options['language'] ) ? $options['language'] : LM_Settings::get_option( 'ai_language', 'es' );
		$niche = isset( $options['niche'] ) ? $options['niche'] : 'generic';

		$prompt = $this->build_prompt( $business_data, $length, $tone, $language, $niche );

		$response = ( $this->provider === 'gemini' ) ? $this->make_gemini_request( $prompt ) : $this->make_openai_request( $prompt );

		if ( is_wp_error( $response ) ) {
			LM_Logger::log( "Error {$this->provider}: " . $response->get_error_message(), 'ERROR' );
			return $this->get_fallback_description( $business_data );
		}

		return $response;
	}

	/**
	 * Construir el prompt dinámico según el nicho y ubicación base.
	 */
	private function build_prompt( $data, $length, $tone, $language, $niche ) {
		$base_location = LM_Settings::get_option( 'base_location', '' );
		$name = $data['name'];
		$types = implode( ', ', $data['types'] );
		
		$prompts = array(
			'generic' => "Escribe una descripción detallada para el negocio '{$name}'.",
			'restaurant' => "Actúa como un crítico gastronómico. Describe la experiencia culinaria, el ambiente y los platos estrella de '{$name}'.",
			'hotel' => "Actúa como un experto en hospitalidad. Resalta el confort, los servicios y la ubicación estratégica de '{$name}'.",
			'health' => "Actúa como un asesor de salud. Enfócate en la confianza, profesionalidad y servicios especializados de '{$name}'.",
			'retail' => "Actúa como un experto en shopping. Describe la variedad de productos y la experiencia de compra en '{$name}'.",
		);

		$niche_prompt = isset( $prompts[ $niche ] ) ? $prompts[ $niche ] : $prompts['generic'];
		
		$prompt = "{$niche_prompt} ";
		$prompt .= "El negocio es de tipo: {$types}. ";
		
		if ( ! empty( $base_location ) ) {
			$prompt .= "Optimiza el contenido para SEO local en la zona de {$base_location}. ";
		}

		$prompt .= "Longitud: {$length} palabras. Tono: {$tone}. Idioma: {$language}. ";
		$prompt .= "Usa formato HTML básico (p, h2, ul, li). No incluyas el nombre del negocio en los encabezados H2.";
		
		return $prompt;
	}

	/**
	 * Petición a OpenAI.
	 */
	private function make_openai_request( $prompt ) {
		$url = 'https://api.openai.com/v1/chat/completions';
		$body = array(
			'model' => 'gpt-3.5-turbo',
			'messages' => array( array( 'role' => 'user', 'content' => $prompt ) ),
			'temperature' => 0.7,
		);

		$response = wp_remote_post( $url, array(
			'headers' => array( 'Authorization' => 'Bearer ' . $this->api_key, 'Content-Type' => 'application/json' ),
			'body' => json_encode( $body ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) return $response;
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return isset( $data['choices'][0]['message']['content'] ) ? trim( $data['choices'][0]['message']['content'] ) : new WP_Error( 'api_error', 'Error OpenAI' );
	}

	/**
	 * Petición a Google Gemini.
	 */
	private function make_gemini_request( $prompt ) {
		$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->api_key;
		$body = array(
			'contents' => array( array( 'parts' => array( array( 'text' => $prompt ) ) ) )
		);

		$response = wp_remote_post( $url, array(
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => json_encode( $body ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) return $response;
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ? trim( $data['candidates'][0]['content']['parts'][0]['text'] ) : new WP_Error( 'api_error', 'Error Gemini' );
	}

	private function get_fallback_description( $data ) {
		return "<p>Bienvenido a {$data['name']}. Somos especialistas en " . implode( ', ', $data['types'] ) . ".</p>";
	}
}
