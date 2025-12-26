<?php
/**
 * Funciones para la creación y gestión de listings en WordPress/Listeo.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Crear un listing a partir de datos de Google y contenido de IA.
 */
function lm_create_listing( $place_data, $ai_content, $seo_data = array() ) {
	
	// Verificar duplicados.
	$existing_id = lm_check_duplicate( $place_data['place_id'], $place_data['name'] );
	if ( $existing_id ) {
		return lm_update_listing( $existing_id, $place_data, 'soft' );
	}

	$post_data = array(
		'post_title'   => sanitize_text_field( $place_data['name'] ),
		'post_content' => wp_kses_post( $ai_content ),
		'post_status'  => 'publish',
		'post_type'    => 'listing',
	);

	$post_id = wp_insert_post( $post_data );

	if ( is_wp_error( $post_id ) ) {
		LM_Logger::log( 'Error al crear post: ' . $post_id->get_error_message(), 'ERROR' );
		return $post_id;
	}

	// Guardar meta datos de Listeo.
	lm_save_listing_meta( $post_id, $place_data );

	// Guardar Place ID para futuras referencias.
	update_post_meta( $post_id, '_place_id', $place_data['place_id'] );

	// Asignar categoría.
	if ( function_exists( 'lm_map_google_type_to_category' ) ) {
		$category_id = lm_map_google_type_to_category( $place_data['types'] );
		if ( $category_id ) {
			wp_set_object_terms( $post_id, $category_id, 'listing_category' );
		}
	}

	// SEO Meta.
	if ( ! empty( $seo_data ) && function_exists( 'lm_set_seo_meta' ) ) {
		lm_set_seo_meta( $post_id, $seo_data );
	}

	return $post_id;
}

/**
 * Guardar meta datos específicos de Listeo.
 */
function lm_save_listing_meta( $post_id, $data ) {
	$meta_mapping = array(
		'_address'      => 'formatted_address',
		'_phone'        => 'formatted_phone_number',
		'_website'      => 'website',
		'_geolocation_lat' => array( 'geometry', 'location', 'lat' ),
		'_geolocation_long' => array( 'geometry', 'location', 'lng' ),
	);

	foreach ( $meta_mapping as $meta_key => $data_key ) {
		$value = '';
		if ( is_array( $data_key ) ) {
			$temp = $data;
			foreach ( $data_key as $k ) {
				if ( isset( $temp[ $k ] ) ) {
					$temp = $temp[ $k ];
				} else {
					$temp = '';
					break;
				}
			}
			$value = $temp;
		} else {
			$value = isset( $data[ $data_key ] ) ? $data[ $data_key ] : '';
		}

		if ( ! empty( $value ) ) {
			update_post_meta( $post_id, $meta_key, sanitize_text_field( $value ) );
		}
	}

	// Horarios (Listeo format).
	if ( isset( $data['opening_hours']['periods'] ) ) {
		// Aquí se podría implementar la conversión al formato de Listeo.
		update_post_meta( $post_id, '_opening_hours', $data['opening_hours']['periods'] );
	}
}

/**
 * Verificar si un listing ya existe.
 */
function lm_check_duplicate( $place_id, $name ) {
	global $wpdb;
	
	// Por Place ID.
	$post_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_place_id' AND meta_value = %s LIMIT 1",
		$place_id
	) );

	if ( $post_id ) {
		return $post_id;
	}

	// Por nombre (opcional, más arriesgado).
	$post_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'listing' AND post_status = 'publish' LIMIT 1",
		$name
	) );

	return $post_id;
}

/**
 * Actualizar un listing existente.
 */
function lm_update_listing( $post_id, $new_data, $mode = 'soft' ) {
	if ( $mode === 'full' ) {
		// Actualizar contenido también (IA).
		// ...
	}

	lm_save_listing_meta( $post_id, $new_data );
	return $post_id;
}

/**
 * Eliminar un listing.
 */
function lm_delete_listing( $post_id ) {
	return wp_delete_post( $post_id, true );
}
