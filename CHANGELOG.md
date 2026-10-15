
# Changelog

Todos los cambios importantes de este proyecto se documentarán en este archivo.

El formato está inspirado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el proyecto utiliza versionado semántico.

## [1.0.0] - En desarrollo

### Añadido
- Formulario público de contacto mediante el shortcode `[formulario_contacto]`.
- Campos para nombre, correo electrónico, asunto y mensaje.
- Validación de campos obligatorios y formato del correo electrónico.
- Protección mediante nonce para validar los envíos del formulario.
- Guardado de mensajes en la base de datos de WordPress.
- Panel de administración para consultar los mensajes recibidos.
- Vista individual para consultar los detalles de cada mensaje.
- Funcionalidad para eliminar mensajes desde el panel de administración.
- Notificaciones para informar el resultado del envío del formulario.

### Seguridad
- Verificación de permisos para las operaciones administrativas.
- Validación de solicitudes antes de procesar o eliminar mensajes.
- Sanitización de los datos recibidos y escape de los datos mostrados.

### Cambios
- Se establece la consulta y eliminación de mensajes como funciones administrativas.
- Se elimina la edición de mensajes desde la interfaz administrativa.

### Pendiente
- Completar las pruebas de integración del formulario público y el panel administrativo.
- Verificar las redirecciones y el manejo de errores.
- Revisar la presentación y la compatibilidad con distintos dispositivos.
- Documentar los resultados de las pruebas antes de considerar estable la versión.
