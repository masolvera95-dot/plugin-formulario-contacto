
# Formulario de Contacto

Plugin de WordPress para recibir, almacenar y administrar mensajes enviados mediante un formulario de contacto.

## Descripción

Formulario de Contacto permite integrar un formulario público en una página de WordPress y gestionar los mensajes recibidos desde el panel de administración.

El proyecto está desarrollado en PHP y utiliza la API de WordPress y una tabla de base de datos para almacenar los mensajes.

## Características

- Formulario público con campos para nombre, correo electrónico, asunto y mensaje.
- Validación de campos obligatorios y formato del correo electrónico.
- Protección de los envíos mediante nonce.
- Almacenamiento de mensajes en la base de datos.
- Panel de administración para consultar los mensajes recibidos.
- Vista individual para revisar el contenido de cada mensaje.
- Eliminación de mensajes desde el panel administrativo.
- Notificaciones sobre el resultado del envío del formulario.

## Requisitos

- WordPress.
- Una versión de PHP compatible con la versión de WordPress instalada.
- Una base de datos compatible con WordPress.

Se recomienda desarrollar y probar el plugin en un entorno local antes de instalarlo en un sitio en producción.

## Instalación

### Instalación manual

1. Copia la carpeta `formulario-contacto` en el directorio `wp-content/plugins/` de tu instalación de WordPress.
2. Accede al panel de administración de WordPress.
3. Entra en **Plugins**.
4. Busca **Formulario de Contacto** y selecciona **Activar**.
5. Comprueba que el plugin se haya activado sin errores.

Durante la activación, el plugin ejecuta la función encargada de crear o actualizar la estructura de su tabla mediante la API de WordPress.

## Uso

### Insertar el formulario

Crea o edita una página de WordPress e introduce el siguiente shortcode:

`[formulario_contacto]`

Guarda la página y visita su URL para comprobar que el formulario se muestre correctamente.

### Administrar mensajes

Desde el panel de administración de WordPress, entra en **Formulario de Contacto**.

Desde esa sección podrás:

- Consultar la lista de mensajes recibidos.
- Abrir un mensaje para ver sus detalles.
- Eliminar un mensaje cuando sea necesario.

La interfaz administrativa no incluye una función para editar mensajes.

## Estructura del proyecto

```text
formulario-contacto/
├── assets/
│   ├── css/
│   └── js/
├── includes/
│   ├── class-formulario-contacto.php
│   ├── class-formulario-contacto-db.php
│   └── class-formulario-contacto-admin.php
├── templates/
│   └── formulario-contacto.php
├── formulario-contacto.php
├── .gitignore
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE
└── README.md
```

### Responsabilidad de los archivos

| Archivo o directorio | Responsabilidad |
|---|---|
| `formulario-contacto.php` | Inicialización y carga de las clases del plugin. |
| `includes/class-formulario-contacto.php` | Shortcode y procesamiento del formulario público. |
| `includes/class-formulario-contacto-db.php` | Operaciones de base de datos. |
| `includes/class-formulario-contacto-admin.php` | Panel administrativo y gestión de mensajes. |
| `templates/formulario-contacto.php` | Plantilla HTML del formulario público. |
| `assets/css/` | Estilos del formulario. |
| `assets/js/` | Código JavaScript del plugin. |

## Base de datos

El plugin utiliza una tabla cuyo nombre se construye con el prefijo configurado en WordPress y el nombre `lpsd_contact_messages`.

La tabla almacena los siguientes datos:

- Identificador del mensaje.
- Nombre del remitente.
- Correo electrónico.
- Asunto.
- Contenido del mensaje.
- Fecha de recepción.

El prefijo de la tabla puede variar según la configuración de WordPress.

## Seguridad y manejo de datos

El plugin incorpora medidas como:

- Sanitización de datos recibidos.
- Escape de datos antes de mostrarlos.
- Verificación de nonce en operaciones relevantes.
- Comprobación de permisos para las operaciones administrativas.
- Validación del correo electrónico y de los campos obligatorios.

Estas medidas forman parte de la implementación actual; el plugin debe someterse a pruebas y revisión de seguridad antes de utilizarse en producción.

Los mensajes pueden contener datos personales. Quien administre el sitio debe establecer las medidas de privacidad, conservación y eliminación de datos que correspondan.

## Desarrollo

Para contribuir al proyecto, consulta [`CONTRIBUTING.md`](CONTRIBUTING.md).

Los cambios relevantes deben registrarse en [`CHANGELOG.md`](CHANGELOG.md).

El repositorio utiliza Git para llevar el control de versiones del código.

## Estado del proyecto

Versión: `1.0.0` — en desarrollo.

La versión debe considerarse estable una vez que se hayan completado y verificado las pruebas funcionales y de integración.

## Licencia

Este proyecto está destinado a distribuirse bajo la licencia GNU General Public License v2 o, a elección del usuario, cualquier versión posterior.

Consulta el archivo [`LICENSE`](LICENSE) para obtener información sobre los términos de distribución.
