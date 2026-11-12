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
        $db_version = get_option(
            'formulario_contacto_db_version',
            '0.0.0'
        );

        if (
            version_compare(
                $db_version,
                FORMULARIO_CONTACTO_DB_VERSION,
                '>='
            )
        ) {
            return;
        }

        $this->crear_tabla();
        $this->agregar_columna_is_read_si_falta();

        update_option(
            'formulario_contacto_db_version',
            FORMULARIO_CONTACTO_DB_VERSION
        );
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

    private function agregar_columna_is_read_si_falta() {
        global $wpdb;

        $columna = $wpdb->get_var(
            $wpdb->prepare(
                'SHOW COLUMNS FROM ' . $this->table_name . ' LIKE %s',
                'is_read'
            )
        );

        if ( $columna ) {
            return;
        }

        $wpdb->query(
            "ALTER TABLE {$this->table_name}
            ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message"
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

    /**
     * Construye las condiciones compartidas por la búsqueda y el filtro.
     */
    private function construir_condiciones( $busqueda, $filtro_estado ) {
        global $wpdb;

        $condiciones = array();
        $valores     = array();

        $busqueda = trim( sanitize_text_field( $busqueda ) );

        if ( '' !== $busqueda ) {
            $coincidencia = '%' . $wpdb->esc_like( $busqueda ) . '%';

            $condiciones[] = '(name LIKE %s OR email LIKE %s OR subject LIKE %s)';
            $valores[]     = $coincidencia;
            $valores[]     = $coincidencia;
            $valores[]     = $coincidencia;
        }

        if ( 'no_leidos' === $filtro_estado ) {
            $condiciones[] = 'is_read = %d';
            $valores[]     = 0;
        } elseif ( 'leidos' === $filtro_estado ) {
            $condiciones[] = 'is_read = %d';
            $valores[]     = 1;
        }

        return array(
            'condiciones' => $condiciones,
            'valores'     => $valores,
        );
    }

    public function obtener_mensajes(
        $orden = 'recientes',
        $busqueda = '',
        $filtro_estado = 'todos',
        $pagina = 1,
        $por_pagina = 10
    ) {
        global $wpdb;

        $orden = 'no_leidos' === $orden
            ? 'no_leidos'
            : 'recientes';

        $filtros_validos = array( 'todos', 'no_leidos', 'leidos' );

        if ( ! in_array( $filtro_estado, $filtros_validos, true ) ) {
            $filtro_estado = 'todos';
        }

        $pagina     = max( 1, absint( $pagina ) );
        $por_pagina = max( 1, absint( $por_pagina ) );
        $offset     = ( $pagina - 1 ) * $por_pagina;

        $filtro = $this->construir_condiciones(
            $busqueda,
            $filtro_estado
        );

        $sql = "SELECT *
                FROM {$this->table_name}";

        if ( ! empty( $filtro['condiciones'] ) ) {
            $sql .= ' WHERE ' . implode( ' AND ', $filtro['condiciones'] );
        }

        if ( 'no_leidos' === $orden ) {
            $sql .= ' ORDER BY is_read ASC, created_at DESC, id DESC';
        } else {
            $sql .= ' ORDER BY created_at DESC, id DESC';
        }

        $sql .= ' LIMIT %d OFFSET %d';

        $valores          = $filtro['valores'];
        $valores[]        = $por_pagina;
        $valores[]        = $offset;
        $sql_preparada    = $wpdb->prepare( $sql, $valores );

        return $wpdb->get_results( $sql_preparada );
    }

    public function contar_mensajes( $busqueda = '', $filtro_estado = 'todos' ) {
        global $wpdb;

        $filtros_validos = array( 'todos', 'no_leidos', 'leidos' );

        if ( ! in_array( $filtro_estado, $filtros_validos, true ) ) {
            $filtro_estado = 'todos';
        }

        $filtro = $this->construir_condiciones(
            $busqueda,
            $filtro_estado
        );

        $sql = "SELECT COUNT(*)
                FROM {$this->table_name}";

        if ( ! empty( $filtro['condiciones'] ) ) {
            $sql .= ' WHERE ' . implode( ' AND ', $filtro['condiciones'] );
        }

        if ( ! empty( $filtro['valores'] ) ) {
            $sql = $wpdb->prepare( $sql, $filtro['valores'] );
        }

        return (int) $wpdb->get_var( $sql );
    }

    public function obtener_mensaje( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT *
                FROM {$this->table_name}
                WHERE id = %d",
                absint( $id )
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
            array(
                'id' => absint( $id ),
            ),
            array( '%d' )
        );
    }
}