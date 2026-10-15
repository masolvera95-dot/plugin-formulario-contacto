
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Formulario_Contacto {

    public function __construct() {

        add_shortcode(
            'formulario_contacto',
            array( $this, 'mostrar_formulario' )
        );

        add_action(
            'init',
            array( $this, 'procesar_formulario' )
        );
    }

    public function mostrar_formulario() {

        ob_start();

        include plugin_dir_path( __DIR__ ) . 'templates/formulario-contacto.php';

        return ob_get_clean();
    }

    public function procesar_formulario() {

        if (
            ! isset( $_SERVER['REQUEST_METHOD'] ) ||
            'POST' !== $_SERVER['REQUEST_METHOD']
        ) {
            return;
        }

        // Ignorar solicitudes que no sean del formulario público.
        if (
            ! isset( $_POST['accion'] ) ||
            'enviar_formulario_contacto' !==
                sanitize_text_field(
                    wp_unslash( $_POST['accion'] )
                )
        ) {
            return;
        }

        // Validar el nonce del formulario público.
        if ( ! isset( $_POST['formulario_contacto_nonce'] ) ) {
            $this->redirigir_con_estado( 'error_nonce' );
        }

        $nonce = sanitize_text_field(
            wp_unslash( $_POST['formulario_contacto_nonce'] )
        );

        if (
            ! wp_verify_nonce(
                $nonce,
                'formulario_contacto_enviar'
            )
        ) {
            $this->redirigir_con_estado( 'error_nonce' );
        }

        // Recoger y limpiar los datos.
        $nombre = isset( $_POST['nombre'] )
            ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) )
            : '';

        $email = isset( $_POST['email'] )
            ? sanitize_email( wp_unslash( $_POST['email'] ) )
            : '';

        $asunto = isset( $_POST['asunto'] )
            ? sanitize_text_field( wp_unslash( $_POST['asunto'] ) )
            : '';

        $mensaje = isset( $_POST['mensaje'] )
            ? sanitize_textarea_field( wp_unslash( $_POST['mensaje'] ) )
            : '';

        // Validar campos obligatorios.
        if (
            '' === $nombre ||
            '' === $email ||
            '' === $asunto ||
            '' === $mensaje
        ) {
            $this->redirigir_con_estado( 'error_campos' );
        }

        // Validar el correo.
        if ( ! is_email( $email ) ) {
            $this->redirigir_con_estado( 'error_email' );
        }

        // Guardar el mensaje.
        $db = new Formulario_Contacto_DB();

        $resultado = $db->guardar_mensaje(
            array(
                'name'    => $nombre,
                'email'   => $email,
                'subject' => $asunto,
                'message' => $mensaje,
            )
        );

        if ( false === $resultado ) {
            $this->redirigir_con_estado( 'error_guardado' );
        }

        $this->redirigir_con_estado( 'enviado' );
    }

    private function redirigir_con_estado( $estado ) {

        $url = home_url(
            '/prueba-de-formulario-de-contacto/'
        );

        $url = remove_query_arg( 'estado', $url );

        $url = add_query_arg(
            'estado',
            $estado,
            $url
        );

        wp_safe_redirect( $url );
        exit;
    }
}
