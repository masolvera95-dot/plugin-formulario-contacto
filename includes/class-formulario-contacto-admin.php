<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Formulario_Contacto_Admin {

    private $mensajes_por_pagina = 10;

    public function __construct() {
        add_action(
            'admin_menu',
            array( $this, 'registrar_menu' )
        );

        add_action(
            'admin_init',
            array( $this, 'registrar_ajustes_correo' )
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

        add_submenu_page(
            'formulario-contacto',
            'Ajustes de correo',
            'Ajustes',
            'manage_options',
            'formulario-contacto-ajustes',
            array( $this, 'mostrar_ajustes_correo' )
        );
    }

    public function registrar_ajustes_correo() {
        register_setting(
            'formulario_contacto_ajustes',
            'formulario_contacto_email_destino',
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitizar_email_destino' ),
                'default'           => get_option( 'admin_email' ),
            )
        );
    }

    public function sanitizar_email_destino( $valor ) {
        $email = sanitize_email( $valor );

        if ( ! is_email( $email ) ) {
            add_settings_error(
                'formulario_contacto_ajustes',
                'email_invalido',
                'Introduce una dirección de correo electrónico válida.',
                'error'
            );

            return get_option(
                'formulario_contacto_email_destino',
                get_option( 'admin_email' )
            );
        }

        return $email;
    }

    public function mostrar_ajustes_correo() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos para ver esta página.' );
        }

        $email = get_option(
            'formulario_contacto_email_destino',
            get_option( 'admin_email' )
        );
        ?>
        <div class="wrap lpsd-mail-settings">
            <h1 class="lpsd-mail-settings__page-title">
                Ajustes de correo
            </h1>

            <?php settings_errors( 'formulario_contacto_ajustes' ); ?>

            <form
                method="post"
                action="options.php"
                class="lpsd-mail-card"
            >
                <?php settings_fields( 'formulario_contacto_ajustes' ); ?>

                <div class="lpsd-mail-card__header">
                    <span class="lpsd-mail-card__eyebrow">
                        LPSD · CONTACTOS
                    </span>

                    <h2>Notificaciones por correo</h2>

                    <p>
                        Elige dónde recibir el aviso cuando alguien envíe
                        el formulario de contacto.
                    </p>
                </div>

                <div class="lpsd-mail-card__body">
                    <label
                        class="lpsd-mail-card__label"
                        for="formulario_contacto_email_destino"
                    >
                        Correo destinatario
                    </label>

                    <input
                        type="email"
                        id="formulario_contacto_email_destino"
                        name="formulario_contacto_email_destino"
                        value="<?php echo esc_attr( $email ); ?>"
                        class="lpsd-mail-card__input"
                        placeholder="nombre@dominio.com"
                        required
                    >

                    <p class="lpsd-mail-card__description">
                        Las notificaciones de nuevos mensajes se enviarán
                        a esta dirección.
                    </p>
                </div>

                <div class="lpsd-mail-card__footer">
                    <button
                        type="submit"
                        class="button button-primary lpsd-mail-card__button"
                    >
                        Guardar correo
                    </button>

                    <span class="lpsd-mail-card__hint">
                        Puedes cambiar esta dirección cuando quieras.
                    </span>
                </div>
            </form>
        </div>
        <?php
    }

    public function cargar_estilos_admin( $hook_suffix ) {
        unset( $hook_suffix );

        if ( ! isset( $_GET['page'] ) ) {
            return;
        }

        $pagina = sanitize_key( wp_unslash( $_GET['page'] ) );

        $paginas_permitidas = array(
            'formulario-contacto',
            'formulario-contacto-ajustes',
        );

        if ( ! in_array( $pagina, $paginas_permitidas, true ) ) {
            return;
        }

        $ruta_css = dirname( __FILE__, 2 ) . '/assets/css/admin-styles.css';

        if ( ! file_exists( $ruta_css ) ) {
            return;
        }

        $archivo_principal = dirname( __FILE__, 2 ) . '/formulario-contacto.php';

        $url_css = plugins_url(
            'assets/css/admin-styles.css',
            $archivo_principal
        );

        wp_enqueue_style(
            'lpsd-contactos-admin',
            $url_css,
            array(),
            (string) filemtime( $ruta_css )
        );
    }

    private function sanitizar_orden( $orden ) {
        return 'no_leidos' === $orden
            ? 'no_leidos'
            : 'recientes';
    }

    private function sanitizar_filtro_estado( $filtro_estado ) {
        $filtros_validos = array( 'todos', 'no_leidos', 'leidos' );

        return in_array( $filtro_estado, $filtros_validos, true )
            ? $filtro_estado
            : 'todos';
    }

    private function obtener_busqueda_get() {
        if ( ! isset( $_GET['busqueda'] ) ) {
            return '';
        }

        return trim(
            sanitize_text_field(
                wp_unslash( $_GET['busqueda'] )
            )
        );
    }

    private function obtener_busqueda_post() {
        if ( ! isset( $_POST['busqueda'] ) ) {
            return '';
        }

        return trim(
            sanitize_text_field(
                wp_unslash( $_POST['busqueda'] )
            )
        );
    }

    private function obtener_orden_post() {
        if ( ! isset( $_POST['orden'] ) ) {
            return 'recientes';
        }

        return $this->sanitizar_orden(
            sanitize_text_field(
                wp_unslash( $_POST['orden'] )
            )
        );
    }

    private function obtener_filtro_estado_post() {
        if ( ! isset( $_POST['filtro_estado'] ) ) {
            return 'todos';
        }

        return $this->sanitizar_filtro_estado(
            sanitize_text_field(
                wp_unslash( $_POST['filtro_estado'] )
            )
        );
    }

    private function obtener_pagina_post() {
        if ( ! isset( $_POST['pagina'] ) ) {
            return 1;
        }

        return max( 1, absint( wp_unslash( $_POST['pagina'] ) ) );
    }

    private function crear_url_lista(
        $orden = 'recientes',
        $busqueda = '',
        $filtro_estado = 'todos',
        $pagina = 1,
        $mensaje_id = 0
    ) {
        $argumentos = array(
            'page'          => 'formulario-contacto',
            'orden'         => $this->sanitizar_orden( $orden ),
            'filtro_estado' => $this->sanitizar_filtro_estado( $filtro_estado ),
            'pagina'        => max( 1, absint( $pagina ) ),
        );

        if ( '' !== $busqueda ) {
            $argumentos['busqueda'] = $busqueda;
        }

        if ( 0 < absint( $mensaje_id ) ) {
            $argumentos['mensaje_id'] = absint( $mensaje_id );
        }

        return add_query_arg(
            $argumentos,
            admin_url( 'admin.php' )
        );
    }

    private function redirigir_a_lista(
        $orden,
        $busqueda,
        $filtro_estado,
        $pagina
    ) {
        $db = new Formulario_Contacto_DB();

        $total_mensajes = $db->contar_mensajes(
            $busqueda,
            $filtro_estado
        );

        $total_paginas = max(
            1,
            (int) ceil( $total_mensajes / $this->mensajes_por_pagina )
        );

        $pagina = min(
            max( 1, absint( $pagina ) ),
            $total_paginas
        );

        wp_safe_redirect(
            $this->crear_url_lista(
                $orden,
                $busqueda,
                $filtro_estado,
                $pagina
            )
        );
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

        $db        = new Formulario_Contacto_DB();
        $resultado = $db->eliminar_mensaje( $mensaje_id );

        if ( false === $resultado ) {
            wp_die( 'No se pudo eliminar el mensaje de la base de datos.' );
        }

        if ( 0 === $resultado ) {
            wp_die( 'El mensaje no existe o ya fue eliminado.' );
        }

        $this->redirigir_a_lista(
            $this->obtener_orden_post(),
            $this->obtener_busqueda_post(),
            $this->obtener_filtro_estado_post(),
            $this->obtener_pagina_post()
        );
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

        wp_safe_redirect(
            $this->crear_url_lista(
                $this->obtener_orden_post(),
                $this->obtener_busqueda_post(),
                $this->obtener_filtro_estado_post(),
                $this->obtener_pagina_post(),
                $mensaje_id
            )
        );
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

        $this->redirigir_a_lista(
            $this->obtener_orden_post(),
            $this->obtener_busqueda_post(),
            $this->obtener_filtro_estado_post(),
            $this->obtener_pagina_post()
        );
    }

    private function mostrar_paginacion(
        $pagina_actual,
        $total_paginas,
        $orden,
        $busqueda,
        $filtro_estado
    ) {
        if ( $total_paginas <= 1 ) {
            return;
        }

        echo '<nav class="lpsd-paginacion" aria-label="Paginación de mensajes">';

        if ( $pagina_actual > 1 ) {
            $url_anterior = $this->crear_url_lista(
                $orden,
                $busqueda,
                $filtro_estado,
                $pagina_actual - 1
            );

            echo '<a class="lpsd-paginacion__boton" href="' .
                esc_url( $url_anterior ) .
                '">← Anterior</a>';
        } else {
            echo '<span class="lpsd-paginacion__boton is-disabled">';
            echo '← Anterior';
            echo '</span>';
        }

        echo '<span class="lpsd-paginacion__estado">';
        echo 'Página ' . esc_html( $pagina_actual );
        echo ' de ' . esc_html( $total_paginas );
        echo '</span>';

        if ( $pagina_actual < $total_paginas ) {
            $url_siguiente = $this->crear_url_lista(
                $orden,
                $busqueda,
                $filtro_estado,
                $pagina_actual + 1
            );

            echo '<a class="lpsd-paginacion__boton" href="' .
                esc_url( $url_siguiente ) .
                '">Siguiente →</a>';
        } else {
            echo '<span class="lpsd-paginacion__boton is-disabled">';
            echo 'Siguiente →';
            echo '</span>';
        }

        echo '</nav>';
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

        $busqueda = $this->obtener_busqueda_get();

        $filtro_estado = isset( $_GET['filtro_estado'] )
            ? $this->sanitizar_filtro_estado(
                sanitize_text_field( wp_unslash( $_GET['filtro_estado'] ) )
            )
            : 'todos';

        $pagina = isset( $_GET['pagina'] )
            ? max( 1, absint( wp_unslash( $_GET['pagina'] ) ) )
            : 1;

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

            $url_volver = $this->crear_url_lista(
                $orden,
                $busqueda,
                $filtro_estado,
                $pagina
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
            echo '<input type="hidden" name="busqueda" value="' .
                esc_attr( $busqueda ) .
                '">';
            echo '<input type="hidden" name="filtro_estado" value="' .
                esc_attr( $filtro_estado ) .
                '">';
            echo '<input type="hidden" name="pagina" value="' .
                esc_attr( $pagina ) .
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

        $total_mensajes = $db->contar_mensajes(
            $busqueda,
            $filtro_estado
        );

        $total_paginas = max(
            1,
            (int) ceil( $total_mensajes / $this->mensajes_por_pagina )
        );

        $pagina = min( $pagina, $total_paginas );

        $mensajes = $db->obtener_mensajes(
            $orden,
            $busqueda,
            $filtro_estado,
            $pagina,
            $this->mensajes_por_pagina
        );

        echo '<div class="wrap lpsd-contactos-lista">';
        echo '<h1 class="lpsd-contactos-lista__titulo">LPSD - Contactos</h1>';

        echo '<form method="get" action="' .
            esc_url( admin_url( 'admin.php' ) ) .
            '" class="lpsd-busqueda-mensajes">';

        echo '<input type="hidden" name="page" value="formulario-contacto">';
        echo '<input type="hidden" name="orden" value="' . esc_attr( $orden ) . '">';
        echo '<input type="hidden" name="filtro_estado" value="' .
            esc_attr( $filtro_estado ) .
            '">';

        echo '<label class="screen-reader-text" for="lpsd-busqueda">';
        echo 'Buscar mensajes';
        echo '</label>';

        echo '<input
            type="search"
            id="lpsd-busqueda"
            name="busqueda"
            value="' . esc_attr( $busqueda ) . '"
            placeholder="Buscar por nombre, correo o asunto"
        >';

        submit_button( 'Buscar', 'secondary', '', false );

        if ( '' !== $busqueda ) {
            $url_limpiar = $this->crear_url_lista(
                $orden,
                '',
                $filtro_estado,
                1
            );

            echo ' <a class="button" href="' . esc_url( $url_limpiar ) . '">';
            echo 'Limpiar búsqueda';
            echo '</a>';
        }

        echo '</form>';

        $url_recientes = $this->crear_url_lista(
            'recientes',
            $busqueda,
            $filtro_estado,
            1
        );

        $url_no_leidos_primero = $this->crear_url_lista(
            'no_leidos',
            $busqueda,
            $filtro_estado,
            1
        );

        $clase_recientes = 'button';
        $clase_no_leidos_primero = 'button';

        if ( 'recientes' === $orden ) {
            $clase_recientes .= ' button-primary';
        } else {
            $clase_no_leidos_primero .= ' button-primary';
        }

        echo '<div class="lpsd-contactos-lista__controles">';

        echo '<div class="lpsd-contactos-lista__orden">';
        echo '<a class="' . esc_attr( $clase_recientes ) . '" href="' .
            esc_url( $url_recientes ) .
            '">Más recientes</a> ';
        echo '<a class="' . esc_attr( $clase_no_leidos_primero ) . '" href="' .
            esc_url( $url_no_leidos_primero ) .
            '">No leídos primero</a>';
        echo '</div>';

        $filtros = array(
            'todos'      => 'Todos',
            'no_leidos'  => 'No leídos',
            'leidos'     => 'Leídos',
        );

        echo '<nav class="lpsd-filtro-estado" aria-label="Filtrar mensajes por estado">';

        foreach ( $filtros as $valor => $etiqueta ) {
            $url_filtro = $this->crear_url_lista(
                $orden,
                $busqueda,
                $valor,
                1
            );

            $clase_filtro = 'lpsd-filtro-estado__boton';

            if ( $filtro_estado === $valor ) {
                $clase_filtro .= ' is-active';
            }

            echo '<a class="' . esc_attr( $clase_filtro ) .
                '" href="' . esc_url( $url_filtro ) . '"';

            if ( $filtro_estado === $valor ) {
                echo ' aria-current="page"';
            }

            echo '>' . esc_html( $etiqueta ) . '</a>';
        }

        echo '</nav>';
        echo '</div>';

        if ( empty( $mensajes ) ) {
            echo '<p class="lpsd-contactos-lista__sin-resultados">';
            echo 'No hay mensajes que coincidan con los filtros seleccionados.';
            echo '</p>';
            echo '</div>';
            return;
        }

        echo '<table class="widefat fixed striped lpsd-contactos-tabla">';
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
            $estilo   = $no_leido
                ? 'font-weight: 700;'
                : 'font-weight: 400;';

            $url_ver = $this->crear_url_lista(
                $orden,
                $busqueda,
                $filtro_estado,
                $pagina,
                $mensaje->id
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
            echo '<input type="hidden" name="busqueda" value="' .
                esc_attr( $busqueda ) .
                '">';
            echo '<input type="hidden" name="filtro_estado" value="' .
                esc_attr( $filtro_estado ) .
                '">';
            echo '<input type="hidden" name="pagina" value="' .
                esc_attr( $pagina ) .
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

        echo '<p class="lpsd-contactos-lista__resumen">';
        echo 'Mostrando ' . esc_html( count( $mensajes ) );
        echo ' de ' . esc_html( $total_mensajes ) . ' mensajes.';
        echo '</p>';

        $this->mostrar_paginacion(
            $pagina,
            $total_paginas,
            $orden,
            $busqueda,
            $filtro_estado
        );

        echo '</div>';
    }
}