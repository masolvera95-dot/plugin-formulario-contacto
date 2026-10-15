<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Obtener el estado de la solicitud.
$estado = isset( $_GET['estado'] )
    ? sanitize_text_field( wp_unslash( $_GET['estado'] ) )
    : '';

// Definir los mensajes que se pueden mostrar.
$notificaciones = array(
    'enviado' => array(
        'clase' => 'notice-success',
        'texto' => 'Mensaje enviado correctamente.',
    ),
    'error_nonce' => array(
        'clase' => 'notice-error',
        'texto' => 'No se pudo validar la solicitud. Recarga la página e inténtalo de nuevo.',
    ),
    'error_campos' => array(
        'clase' => 'notice-error',
        'texto' => 'Todos los campos son obligatorios.',
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

// Mostrar una notificación solo si el estado está contemplado.
if ( isset( $notificaciones[ $estado ] ) ) {

    $notificacion = $notificaciones[ $estado ];

    echo '<p class="notice ' . esc_attr( $notificacion['clase'] ) . '">';
    echo esc_html( $notificacion['texto'] );
    echo '</p>';

}

?>

<form method="post" class="formulario-contacto">

    <?php
    wp_nonce_field(
        'formulario_contacto_enviar',
        'formulario_contacto_nonce'
    );
    ?>

    <p>
        <label for="nombre">Nombre</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            required
        >
    </p>

    <p>
        <label for="email">Correo electrónico</label>
        <input
            type="email"
            id="email"
            name="email"
            required
        >
    </p>

    <p>
        <label for="asunto">Asunto</label>
        <input
            type="text"
            id="asunto"
            name="asunto"
            required
        >
    </p>

    <p>
        <label for="mensaje">Mensaje</label>
        <textarea
            id="mensaje"
            name="mensaje"
            rows="6"
            required
        ></textarea>
    </p>

    <button type="submit">
        Enviar mensaje
    </button>

</form>