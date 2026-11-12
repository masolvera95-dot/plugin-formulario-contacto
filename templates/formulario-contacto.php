<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$estado = isset( $_GET['estado'] )
    ? sanitize_text_field( wp_unslash( $_GET['estado'] ) )
    : '';

$notificaciones = array(
    'enviado' => array(
        'clase' => 'notice-success',
        'texto' => 'Mensaje enviado correctamente.',
    ),
    'enviado_sin_notificacion' => array(
        'clase' => 'notice-warning',
        'texto' => 'Tu mensaje se guardó correctamente, pero no se pudo enviar el aviso por correo. No es necesario enviarlo de nuevo.',
    ),
    'error_nonce' => array(
        'clase' => 'notice-error',
        'texto' => 'No se pudo validar la solicitud. Recarga la página e inténtalo de nuevo.',
    ),
    'error_campos' => array(
        'clase' => 'notice-error',
        'texto' => 'Completa todos los campos y acepta el aviso de privacidad.',
    ),
    'error_email' => array(
        'clase' => 'notice-error',
        'texto' => 'El correo electrónico no es válido.',
    ),
    'error_guardado' => array(
        'clase' => 'notice-error',
        'texto' => 'No se pudo guardar el mensaje. Inténtalo de nuevo.',
    ),
);

if ( isset( $notificaciones[ $estado ] ) ) {
    $notificacion = $notificaciones[ $estado ];
    ?>
    <div class="formulario-contacto-notice <?php echo esc_attr( $notificacion['clase'] ); ?>">
        <?php echo esc_html( $notificacion['texto'] ); ?>
    </div>
    <?php
}
?>

<form method="post" class="formulario-contacto">
    <?php
    wp_nonce_field(
        'formulario_contacto_enviar',
        'formulario_contacto_nonce'
    );
    ?>

    <input
        type="hidden"
        name="accion"
        value="enviar_formulario_contacto"
    >

    <input
        type="hidden"
        name="url_retorno"
        value="<?php echo esc_url( get_permalink() ); ?>"
    >

    <div class="formulario-contacto-contenido">
        <h2 class="formulario-contacto-titulo">ESCRÍBENOS</h2>

        <p class="formulario-contacto-introduccion">
            Si te apasiona el trabajo comunitario, la investigación, la incidencia política o simplemente quieres aportar tu experiencia para construir alternativas más justas e informadas para las juventudes, ¡únete a nuestro equipo y transformemos esta realidad juntos!
        </p>

        <div class="formulario-contacto-campo">
            <label for="nombre">Nombre</label>
            <input
                type="text"
                id="nombre"
                name="nombre"
                required
            >
        </div>

        <div class="formulario-contacto-campo">
            <label for="email">Correo electrónico</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >
        </div>

        <div class="formulario-contacto-campo">
            <label for="asunto">Asunto</label>
            <input
                type="text"
                id="asunto"
                name="asunto"
                required
            >
        </div>

        <div class="formulario-contacto-campo">
            <label for="mensaje">Mensaje</label>
            <textarea
                id="mensaje"
                name="mensaje"
                required
            ></textarea>
        </div>

        <label class="formulario-contacto-privacidad" for="privacidad">
            <input
                type="checkbox"
                id="privacidad"
                name="privacidad"
                value="1"
                required
            >
            <span>He leído y acepto el aviso de privacidad</span>
        </label>

        <button type="submit" class="formulario-contacto-enviar">
            Enviar
        </button>
    </div>
</form>