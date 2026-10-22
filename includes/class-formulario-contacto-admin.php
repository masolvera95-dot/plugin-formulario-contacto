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

        wp_safe_redirect(
            admin_url( 'admin.php?page=formulario-contacto' )
        );

        exit;
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

        $redirect = add_query_arg(
            array(
                'page'       => 'formulario-contacto',
                'mensaje_id' => $mensaje_id,
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $redirect );
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

        $redirect = add_query_arg(
            array(
                'page' => 'formulario-contacto',
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $redirect );
        exit;
    }

    public function mostrar_pagina() {

        $db = new Formulario_Contacto_DB();

        if ( isset( $_GET['mensaje_id'] ) ) {

            $mensaje_id = absint(
                wp_unslash( $_GET['mensaje_id'] )
            );

            $mensaje = $db->obtener_mensaje( $mensaje_id );

            if ( null === $mensaje ) {
                echo '<div class="wrap">';
                echo '<h1>Ver mensaje</h1>';
                echo '<p>El mensaje no existe.</p>';
                echo '</div>';
                return;
            }

            if ( (int) $mensaje->is_read === 0 ) {
                $db->actualizar_estado_lectura( $mensaje->id, 1 );
                $mensaje->is_read = 1;
            }

            echo '<div class="wrap">';
            echo '<h1>Ver mensaje</h1>';

            echo '<p><strong>Nombre:</strong> ';
            echo esc_html( $mensaje->name );
            echo '</p>';

            echo '<p><strong>Correo:</strong> ';
            echo esc_html( $mensaje->email );
            echo '</p>';

            echo '<p><strong>Asunto:</strong> ';
            echo esc_html( $mensaje->subject );
            echo '</p>';

            echo '<p><strong>Mensaje:</strong></p>';
            echo '<p>';
            echo nl2br( esc_html( $mensaje->message ) );
            echo '</p>';

            echo '<p><strong>Fecha:</strong> ';
            echo esc_html( $mensaje->created_at );
            echo '</p>';

            echo '<form method="post" action="' .
                esc_url( admin_url( 'admin-post.php' ) ) .
                '">';
            echo '<input type="hidden" name="action" value="formulario_contacto_marcar_no_leido">';
            echo '<input type="hidden" name="mensaje_id" value="' .
                esc_attr( $mensaje->id ) .
                '">';
            wp_nonce_field(
                'formulario_contacto_estado_lectura',
                'formulario_contacto_nonce'
            );
            echo '<button type="submit" class="button">Marcar como no leído</button>';
            echo '</form>';

            echo '<p><a href="' .
                esc_url(
                    admin_url( 'admin.php?page=formulario-contacto' )
                ) .
                '">← Volver a los mensajes</a></p>';

            echo '</div>';

            return;
        }

        $mensajes = $db->obtener_mensajes();

        echo '<div class="wrap">';
        echo '<h1>Formulario de Contacto</h1>';

        if ( empty( $mensajes ) ) {
            echo '<p>No hay mensajes registrados.</p>';
        } else {
            echo '<table class="widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>Nombre</th>';
            echo '<th>Correo</th>';
            echo '<th>Asunto</th>';
            echo '<th>Fecha</th>';
            echo '<th>Acciones</th>';
            echo '</tr></thead>';
            echo '<tbody>';

            foreach ( $mensajes as $mensaje ) {
                $estilo = (int) $mensaje->is_read === 0
                    ? 'font-weight: 700;'
                    : 'font-weight: 400;';

                echo '<tr>';

                echo '<td style="' . esc_attr( $estilo ) . '">' .
                    esc_html( $mensaje->name ) .
                    '</td>';

                echo '<td style="' . esc_attr( $estilo ) . '">' .
                    esc_html( $mensaje->email ) .
                    '</td>';

                echo '<td style="' . esc_attr( $estilo ) . '">' .
                    esc_html( $mensaje->subject ) .
                    '</td>';

                echo '<td>' . esc_html( $mensaje->created_at ) . '</td>';

                echo '<td>';

                echo '<a href="' .
                    esc_url(
                        add_query_arg(
                            array(
                                'page'       => 'formulario-contacto',
                                'mensaje_id' => $mensaje->id,
                            ),
                            admin_url( 'admin.php' )
                        )
                    ) .
                    '">Ver</a>';

                echo ' | ';

                echo '<form method="post" action="' .
                    esc_url( admin_url( 'admin-post.php' ) ) .
                    '" style="display:inline;">';

                echo '<input type="hidden" name="action" value="formulario_contacto_eliminar">';
                echo '<input type="hidden" name="mensaje_id" value="' .
                    esc_attr( $mensaje->id ) .
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
        }

        echo '</div>';
    }
}