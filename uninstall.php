<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Eliminar la tabla neureka_intentos al borrar el plugin
$table_name = $wpdb->prefix . 'neureka_intentos';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
