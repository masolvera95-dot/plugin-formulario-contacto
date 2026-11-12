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

        add_action(
            'wp_enqueue_scripts',
            array( $this, 'enqueue_estilos' )
        );
    }

    public function enqueue_estilos() {
        wp_enqueue_style(
            'formulario-contacto-estilos',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/styles.css',
            array(),
            '1.2.0'
        );
    }

    public function mostrar_formulario() {
        ob_start();

        include plugin_dir_path( dirname( __FILE__ ) ) . 'templates/formulario-contacto.php';

        return ob_get_clean();
    }

    public function procesar_formulario() {
        if (
            ! isset( $_SERVER['REQUEST_METHOD'] ) ||
            'POST' !== $_SERVER['REQUEST_METHOD']
        ) {
            return;
        }

        if (
            ! isset( $_POST['accion'] ) ||
            'enviar_formulario_contacto' !==
                sanitize_text_field( wp_unslash( $_POST['accion'] ) )
        ) {
            return;
        }

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

        $privacidad_aceptada = isset( $_POST['privacidad'] )
            && '1' === sanitize_text_field( wp_unslash( $_POST['privacidad'] ) );

        if (
            '' === $nombre ||
            '' === $email ||
            '' === $asunto ||
            '' === $mensaje ||
            ! $privacidad_aceptada
        ) {
            $this->redirigir_con_estado( 'error_campos' );
        }

        if ( ! is_email( $email ) ) {
            $this->redirigir_con_estado( 'error_email' );
        }

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

        $correo_enviado = $this->enviar_notificacion_por_correo(
            $nombre,
            $email,
            $asunto,
            $mensaje
        );

        if ( ! $correo_enviado ) {
            $this->redirigir_con_estado( 'enviado_sin_notificacion' );
        }

        $this->redirigir_con_estado( 'enviado' );
    }

    private function enviar_notificacion_por_correo(
        $nombre,
        $email,
        $asunto,
        $mensaje
    ) {
        $destinatario = sanitize_email(
            get_option(
                'formulario_contacto_email_destino',
                get_option( 'admin_email' )
            )
        );

        if ( ! is_email( $destinatario ) ) {
            return false;
        }

        $cuerpo = "Se recibió un nuevo mensaje de contacto.\n\n";
        $cuerpo .= 'Nombre: ' . $nombre . "\n";
        $cuerpo .= 'Correo: ' . $email . "\n";
        $cuerpo .= 'Asunto: ' . $asunto . "\n\n";
        $cuerpo .= "Mensaje:\n" . $mensaje . "\n";

        $cabeceras = array(
            'Reply-To: ' . $email,
        );

        return wp_mail(
            $destinatario,
            'Nuevo mensaje de contacto',
            $cuerpo,
            $cabeceras
        );
    }

    private function redirigir_con_estado( $estado ) {
        $url = isset( $_POST['url_retorno'] )
            ? esc_url_raw( wp_unslash( $_POST['url_retorno'] ) )
            : '';

        $url = wp_validate_redirect( $url, home_url( '/' ) );
        $url = remove_query_arg( 'estado', $url );
        $url = add_query_arg( 'estado', $estado, $url );

        wp_safe_redirect( $url );
        exit;
    }
}