<?php
/**
 * Limpieza al desinstalar el plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Eliminar opciones.
delete_option( 'lm_settings' );

// Eliminar tabla custom.
global $wpdb;
$table_name = $wpdb->prefix . 'lm_queue';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Eliminar transients.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_lm_%' OR option_name LIKE '_transient_timeout_lm_%'" );

// Nota: No eliminamos los listings (posts de tipo 'listing') por seguridad del contenido del usuario.
// Pero sí podríamos eliminar los meta keys específicos si fuera necesario.
// $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key IN ('_place_id', '_manually_edited')" );
