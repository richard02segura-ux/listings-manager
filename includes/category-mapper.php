<?php
/**
 * Mapeo de tipos de Google Places a categorías de WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mapear tipos de Google a una categoría de Listeo.
 */
function lm_map_google_type_to_category( $google_types ) {
	$mapping = LM_Settings::get_option( 'category_mapping', array(
		'restaurant' => 'Restaurantes',
		'hotel'      => 'Hoteles',
		'gym'        => 'Gimnasios',
		'hospital'   => 'Salud',
		'veterinary_care' => 'Veterinarias',
	) );

	foreach ( $google_types as $type ) {
		if ( isset( $mapping[ $type ] ) ) {
			$category_name = $mapping[ $type ];
			$term = get_term_by( 'name', $category_name, 'listing_category' );
			
			if ( $term ) {
				return $term->term_id;
			} else {
				// Crear la categoría si no existe.
				$new_term = wp_insert_term( $category_name, 'listing_category' );
				if ( ! is_wp_error( $new_term ) ) {
					return $new_term['term_id'];
				}
			}
		}
	}

	// Categoría por defecto.
	$default_cat = get_term_by( 'name', 'Otros', 'listing_category' );
	return $default_cat ? $default_cat->term_id : 0;
}
