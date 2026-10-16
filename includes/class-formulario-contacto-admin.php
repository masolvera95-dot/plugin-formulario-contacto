
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

    // Cargar estilos solo en la página del plugin.
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

        // Verificar permisos.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos para realizar esta acción.' );
        }

        // Validar el nonce de eliminación.
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

        // Validar el ID del mensaje.
        if ( ! isset( $_POST['mensaje_id'] ) ) {
            wp_die( 'No se proporcionó un ID de mensaje.' );
        }

        $mensaje_id = absint(
            wp_unslash( $_POST['mensaje_id'] )
        );

        if ( 0 === $mensaje_id ) {
            wp_die( 'El ID del mensaje no es válido.' );
        }

        // Eliminar el mensaje.
        $db = new Formulario_Contacto_DB();

        $resultado = $db->eliminar_mensaje( $mensaje_id );

        if ( false === $resultado ) {
            wp_die( 'No se pudo eliminar el mensaje de la base de datos.' );
        }

        if ( 0 === $resultado ) {
            wp_die( 'El mensaje no existe o ya fue eliminado.' );
        }

        // Volver al listado del administrador.
        wp_safe_redirect(
            admin_url( 'admin.php?page=formulario-contacto' )
        );

        exit;
    }

    public function mostrar_pagina() {

        $db = new Formulario_Contacto_DB();

        // Mostrar detalle de un mensaje.
        if ( isset( $_GET['mensaje_id'] ) ) {

            $mensaje_id = absint(
                wp_unslash( $_GET['mensaje_id'] )
            );

            $mensaje = $db->obtener_mensaje( $mensaje_id );

            echo '<div class="wrap">';
            echo '<h1>Ver mensaje</h1>';

            if ( null === $mensaje ) {

                echo '<p>El mensaje no existe.</p>';

            } else {

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

                echo '<p><a href="' .
                    esc_url(
                        admin_url( 'admin.php?page=formulario-contacto' )
                    ) .
                    '">← Volver a los mensajes</a></p>';
            }

            echo '</div>';

            return;
        }

        // Mostrar la lista de mensajes.
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

                echo '<tr>';

                echo '<td>' . esc_html( $mensaje->name ) . '</td>';
                echo '<td>' . esc_html( $mensaje->email ) . '</td>';
                echo '<td>' . esc_html( $mensaje->subject ) . '</td>';
                echo '<td>' . esc_html( $mensaje->created_at ) . '</td>';

                echo '<td>';

                // Ver el mensaje.
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

                // Formulario para eliminar.
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
