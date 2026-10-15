
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Formulario_Contacto_DB {

    private $table_name;

    public function __construct() {
        global $wpdb;

        $this->table_name = $wpdb->prefix . 'lpsd_contact_messages';
    }

    public function crear_tabla() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            subject varchar(200) NOT NULL,
            message text NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql );
    }

    public function guardar_mensaje( $datos ) {
        global $wpdb;

        return $wpdb->insert(
            $this->table_name,
            array(
                'name'       => $datos['name'],
                'email'      => $datos['email'],
                'subject'    => $datos['subject'],
                'message'    => $datos['message'],
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%s' )
        );
    }

    public function obtener_mensajes() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC"
        );
    }

    public function obtener_mensaje( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
    }

    public function eliminar_mensaje( $id ) {
        global $wpdb;

        return $wpdb->delete(
            $this->table_name,
            array( 'id' => $id ),
            array( '%d' )
        );
    }
}
