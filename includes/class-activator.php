<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Neureka_Activator {

    public static function activate() {
        global $wpdb;

        // Nombre de la tabla usando el prefijo de WordPress
        $table_name = $wpdb->prefix . 'neureka_intentos';
        $charset_collate = $wpdb->get_charset_collate();

        // Estructura de la tabla neureka_intentos
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            challenge_id bigint(20) NOT NULL,
            score float DEFAULT 0 NOT NULL,
            details longtext NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY challenge_id (challenge_id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}