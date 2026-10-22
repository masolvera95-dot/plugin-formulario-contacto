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

    public function maybe_migrar() {
        $db_version = get_option( 'formulario_contacto_db_version', '0.0.0' );

        if ( version_compare( $db_version, FORMULARIO_CONTACTO_DB_VERSION, '>=' ) ) {
            return;
        }

        $this->crear_tabla();
        $this->agregar_columna_is_read_si_falta();

        update_option( 'formulario_contacto_db_version', FORMULARIO_CONTACTO_DB_VERSION );
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
            is_read tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql );
    }

    public function agregar_columna_is_read_si_falta() {
        global $wpdb;

        $tabla   = $this->table_name;
        $columna = 'is_read';

        $resultado = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM {$tabla} LIKE %s",
                $columna
            )
        );

        if ( ! empty( $resultado ) ) {
            return;
        }

        $wpdb->query(
            "ALTER TABLE {$tabla} ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message"
        );
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
                'is_read'    => 0,
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%d', '%s' )
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

    public function actualizar_estado_lectura( $id, $is_read ) {
        global $wpdb;

        return $wpdb->update(
            $this->table_name,
            array(
                'is_read' => (int) $is_read,
            ),
            array(
                'id' => absint( $id ),
            ),
            array( '%d' ),
            array( '%d' )
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