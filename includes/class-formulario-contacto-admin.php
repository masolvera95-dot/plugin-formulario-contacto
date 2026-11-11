<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Formulario_Contacto_Admin {

    public function __construct() {
        add_action(
            'admin_menu',
            array( $this, 'registrar_menu' )
        );

        add_action(
            'admin_post_formulario_contacto_eliminar',
            array( $this, 'eliminar_mensaje' )
        );

        add_action(
            'admin_post_formulario_contacto_marcar_leido',
            array( $this, 'marcar_leido' )
        );

        add_action(
            'admin_post_formulario_contacto_marcar_no_leido',
            array( $this, 'marcar_no_leido' )
        );

        add_action(
            'admin_enqueue_scripts',
            array( $this, 'cargar_estilos_admin' )
        );
    }

    public function registrar_menu() {
        add_menu_page(
            'LPSD - Contactos',
            'LPSD - Contactos',
            'manage_options',
            'formulario-contacto',
            array( $this, 'mostrar_pagina' ),
            'dashicons-email',
            25
        );
    }

    public function cargar_estilos_admin( $hook_suffix ) {
        if ( 'toplevel_page_formulario-contacto' !== $hook_suffix ) {
            return;
        }

        wp_enqueue_style(
            'lpsd-contactos-admin',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin-styles.css',
            array(),
            '1.0.0'
        );
    }

    private function sanitizar_orden( $orden ) {
        return 'no_leidos' === $orden
            ? 'no_leidos'
            : 'recientes';
    }

    private function obtener_orden_post() {
        if ( ! isset( $_POST['orden'] ) ) {
            return 'recientes';
        }

        return $this->sanitizar_orden(
            sanitize_text_field( wp_unslash( $_POST['orden'] ) )
        );
    }

    private function redirigir_a_lista( $orden = 'recientes' ) {
        $url = add_query_arg(
            array(
                'page'  => 'formulario-contacto',
                'orden' => $this->sanitizar_orden( $orden ),
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $url );
        exit;
    }

    public function eliminar_mensaje() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos para realizar esta acción.' );
        }

        if (
            ! isset( $_POST['formulario_contacto_nonce'] ) ||
            ! wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash( $_POST['formulario_contacto_nonce'] )
                ),
                'formulario_contacto_eliminar'
            )
        ) {
            wp_die( 'La solicitud de eliminación no es válida.' );
        }

        if ( ! isset( $_POST['mensaje_id'] ) ) {
            wp_die( 'No se proporcionó un ID de mensaje.' );
        }

        $mensaje_id = absint( wp_unslash( $_POST['mensaje_id'] ) );

        if ( 0 === $mensaje_id ) {
            wp_die( 'El ID del mensaje no es válido.' );
        }

        $db = new Formulario_Contacto_DB();
        $resultado = $db->eliminar_mensaje( $mensaje_id );

        if ( false === $resultado ) {
            wp_die( 'No se pudo eliminar el mensaje de la base de datos.' );
        }

        if ( 0 === $resultado ) {
            wp_die( 'El mensaje no existe o ya fue eliminado.' );
        }

        $this->redirigir_a_lista( $this->obtener_orden_post() );
    }

    public function marcar_leido() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos para realizar esta acción.' );
        }

        if ( ! isset( $_POST['mensaje_id'] ) ) {
            wp_die( 'No se proporcionó un ID de mensaje.' );
        }

        $mensaje_id = absint( wp_unslash( $_POST['mensaje_id'] ) );

        if ( 0 === $mensaje_id ) {
            wp_die( 'El ID del mensaje no es válido.' );
        }

        if (
            ! isset( $_POST['formulario_contacto_nonce'] ) ||
            ! wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash( $_POST['formulario_contacto_nonce'] )
                ),
                'formulario_contacto_estado_lectura'
            )
        ) {
            wp_die( 'La solicitud no es válida.' );
        }

        $db = new Formulario_Contacto_DB();
        $db->actualizar_estado_lectura( $mensaje_id, 1 );

        $url = add_query_arg(
            array(
                'page'       => 'formulario-contacto',
                'mensaje_id' => $mensaje_id,
                'orden'      => $this->obtener_orden_post(),
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $url );
        exit;
    }

    public function marcar_no_leido() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos para realizar esta acción.' );
        }

        if ( ! isset( $_POST['mensaje_id'] ) ) {
            wp_die( 'No se proporcionó un ID de mensaje.' );
        }

        $mensaje_id = absint( wp_unslash( $_POST['mensaje_id'] ) );

        if ( 0 === $mensaje_id ) {
            wp_die( 'El ID del mensaje no es válido.' );
        }

        if (
            ! isset( $_POST['formulario_contacto_nonce'] ) ||
            ! wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash( $_POST['formulario_contacto_nonce'] )
                ),
                'formulario_contacto_estado_lectura'
            )
        ) {
            wp_die( 'La solicitud no es válida.' );
        }

        $db = new Formulario_Contacto_DB();
        $db->actualizar_estado_lectura( $mensaje_id, 0 );

        $this->redirigir_a_lista( $this->obtener_orden_post() );
    }

    public function mostrar_pagina() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos para ver esta página.' );
        }

        $orden = isset( $_GET['orden'] )
            ? $this->sanitizar_orden(
                sanitize_text_field( wp_unslash( $_GET['orden'] ) )
            )
            : 'recientes';

        $db = new Formulario_Contacto_DB();

        if ( isset( $_GET['mensaje_id'] ) ) {
            $mensaje_id = absint( wp_unslash( $_GET['mensaje_id'] ) );
            $mensaje    = $db->obtener_mensaje( $mensaje_id );

            echo '<div class="wrap lpsd-contactos-admin">';

            if ( null === $mensaje ) {
                echo '<h1>Mensaje no encontrado</h1>';
                echo '<p>El mensaje no existe o pudo haber sido eliminado.</p>';
                echo '</div>';
                return;
            }

            if ( 0 === (int) $mensaje->is_read ) {
                $db->actualizar_estado_lectura( $mensaje->id, 1 );
                $mensaje->is_read = 1;
            }

            $url_volver = add_query_arg(
                array(
                    'page'  => 'formulario-contacto',
                    'orden' => $orden,
                ),
                admin_url( 'admin.php' )
            );

            echo '<div class="lpsd-contacto-detalle">';

            echo '<div class="lpsd-contacto-detalle__encabezado">';
            echo '<div>';
            echo '<span class="lpsd-contacto-detalle__eyebrow">LPSD · CONTACTOS</span>';
            echo '<h1>Mensaje recibido</h1>';
            echo '</div>';
            echo '<span class="lpsd-contacto-detalle__estado">Leído</span>';
            echo '</div>';

            echo '<div class="lpsd-contacto-detalle__tarjeta">';

            echo '<div class="lpsd-contacto-detalle__datos">';

            echo '<div class="lpsd-contacto-detalle__dato">';
            echo '<span class="lpsd-contacto-detalle__etiqueta">Nombre</span>';
            echo '<strong>' . esc_html( $mensaje->name ) . '</strong>';
            echo '</div>';

            echo '<div class="lpsd-contacto-detalle__dato">';
            echo '<span class="lpsd-contacto-detalle__etiqueta">Correo</span>';
            echo '<a href="mailto:' . esc_attr( $mensaje->email ) . '">';
            echo esc_html( $mensaje->email );
            echo '</a>';
            echo '</div>';

            echo '<div class="lpsd-contacto-detalle__dato">';
            echo '<span class="lpsd-contacto-detalle__etiqueta">Asunto</span>';
            echo '<strong>' . esc_html( $mensaje->subject ) . '</strong>';
            echo '</div>';

            echo '<div class="lpsd-contacto-detalle__dato">';
            echo '<span class="lpsd-contacto-detalle__etiqueta">Fecha</span>';
            echo '<span>' . esc_html( $mensaje->created_at ) . '</span>';
            echo '</div>';

            echo '</div>';

            echo '<div class="lpsd-contacto-detalle__mensaje">';
            echo '<h2>Mensaje</h2>';
            echo '<div class="lpsd-contacto-detalle__texto">';
            echo esc_html( $mensaje->message );
            echo '</div>';
            echo '</div>';

            echo '<div class="lpsd-contacto-detalle__acciones">';

            echo '<a class="button lpsd-contacto-detalle__volver" href="' .
                esc_url( $url_volver ) .
                '">← Volver a los mensajes</a>';

            echo '<form method="post" action="' .
                esc_url( admin_url( 'admin-post.php' ) ) .
                '">';

            echo '<input type="hidden" name="action" value="formulario_contacto_marcar_no_leido">';
            echo '<input type="hidden" name="mensaje_id" value="' .
                esc_attr( $mensaje->id ) .
                '">';
            echo '<input type="hidden" name="orden" value="' .
                esc_attr( $orden ) .
                '">';

            wp_nonce_field(
                'formulario_contacto_estado_lectura',
                'formulario_contacto_nonce'
            );

            echo '<button type="submit" class="button lpsd-contacto-detalle__no-leido">';
            echo 'Marcar como no leído';
            echo '</button>';

            echo '</form>';
            echo '</div>';

            echo '</div>';
            echo '</div>';

            return;
        }

        $mensajes = $db->obtener_mensajes( $orden );

        echo '<div class="wrap">';
        echo '<h1>Formulario de Contacto</h1>';

        $url_recientes = add_query_arg(
            array(
                'page'  => 'formulario-contacto',
                'orden' => 'recientes',
            ),
            admin_url( 'admin.php' )
        );

        $url_no_leidos = add_query_arg(
            array(
                'page'  => 'formulario-contacto',
                'orden' => 'no_leidos',
            ),
            admin_url( 'admin.php' )
        );

        $clase_recientes = 'button';
        $clase_no_leidos = 'button';

        if ( 'recientes' === $orden ) {
            $clase_recientes .= ' button-primary';
        } else {
            $clase_no_leidos .= ' button-primary';
        }

        echo '<p class="formulario-contacto-filtros">';
        echo '<a class="' . esc_attr( $clase_recientes ) . '" href="' .
            esc_url( $url_recientes ) .
            '">Más recientes</a> ';
        echo '<a class="' . esc_attr( $clase_no_leidos ) . '" href="' .
            esc_url( $url_no_leidos ) .
            '">No leídos primero</a>';
        echo '</p>';

        if ( empty( $mensajes ) ) {
            echo '<p>No hay mensajes registrados.</p>';
            echo '</div>';
            return;
        }

        echo '<table class="widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>Estado</th>';
        echo '<th>Nombre</th>';
        echo '<th>Correo</th>';
        echo '<th>Asunto</th>';
        echo '<th>Fecha</th>';
        echo '<th>Acciones</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ( $mensajes as $mensaje ) {
            $no_leido = 0 === (int) $mensaje->is_read;
            $estilo = $no_leido
                ? 'font-weight: 700;'
                : 'font-weight: 400;';

            $url_ver = add_query_arg(
                array(
                    'page'       => 'formulario-contacto',
                    'mensaje_id' => $mensaje->id,
                    'orden'      => $orden,
                ),
                admin_url( 'admin.php' )
            );

            echo '<tr>';

            echo '<td style="' . esc_attr( $estilo ) . '">';
            echo $no_leido ? 'No leído' : 'Leído';
            echo '</td>';

            echo '<td style="' . esc_attr( $estilo ) . '">';
            echo esc_html( $mensaje->name );
            echo '</td>';

            echo '<td style="' . esc_attr( $estilo ) . '">';
            echo esc_html( $mensaje->email );
            echo '</td>';

            echo '<td style="' . esc_attr( $estilo ) . '">';
            echo esc_html( $mensaje->subject );
            echo '</td>';

            echo '<td>';
            echo esc_html( $mensaje->created_at );
            echo '</td>';

            echo '<td>';
            echo '<a href="' . esc_url( $url_ver ) . '">Ver</a>';
            echo ' | ';

            echo '<form method="post" action="' .
                esc_url( admin_url( 'admin-post.php' ) ) .
                '" style="display:inline;">';

            echo '<input type="hidden" name="action" value="formulario_contacto_eliminar">';
            echo '<input type="hidden" name="mensaje_id" value="' .
                esc_attr( $mensaje->id ) .
                '">';
            echo '<input type="hidden" name="orden" value="' .
                esc_attr( $orden ) .
                '">';

            wp_nonce_field(
                'formulario_contacto_eliminar',
                'formulario_contacto_nonce'
            );

            echo '<button type="submit" class="button-link" ';
            echo 'onclick="return confirm(\'¿Seguro que deseas eliminar este mensaje?\');">';
            echo 'Eliminar</button>';

            echo '</form>';
            echo '</td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }
}