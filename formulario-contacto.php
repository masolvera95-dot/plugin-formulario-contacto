<?php
/**
 * Plugin Name: Formulario de Contacto
 * Description: Plugin de formulario de contacto para WordPress.
 * Version: 1.2.0
 * Author: Manuel Alejandro Sánchez Olvera
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FORMULARIO_CONTACTO_DB_VERSION', '1.2.0' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-formulario-contacto-db.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-formulario-contacto.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-formulario-contacto-admin.php';

function formulario_contacto_migrar_base_datos() {
    $db = new Formulario_Contacto_DB();
    $db->maybe_migrar();
}

register_activation_hook(
    __FILE__,
    'formulario_contacto_migrar_base_datos'
);

add_action(
    'plugins_loaded',
    'formulario_contacto_migrar_base_datos'
);

new Formulario_Contacto();
new Formulario_Contacto_Admin();