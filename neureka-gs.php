<?php
/**
 * Plugin Name: Neureka GS
 * Description: Sistema de retos e interactividad para Neureka.
 * Version:     1.0.0
 * Author:      neureka team
 * Text Domain: neureka-gs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar la clase de activación
require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';

// Hook de activación
register_activation_hook( __FILE__, array( 'Neureka_Activator', 'activate' ) );


