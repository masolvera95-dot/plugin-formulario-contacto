
<?php
/**
 * Plugin Name: Formulario de Contacto
 * Description: Plugin de formulario de contacto para WordPress.
 * Version: 1.0.0
 * Author: Manuel Alejandro Sánchez Olvera
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-formulario-contacto-db.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-formulario-contacto.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-formulario-contacto-admin.php';

function formulario_contacto_activar() {
    $db = new Formulario_Contacto_DB();
    $db->crear_tabla();
}

register_activation_hook(
    __FILE__,
    'formulario_contacto_activar'
);

new Formulario_Contacto();
new Formulario_Contacto_Admin();
