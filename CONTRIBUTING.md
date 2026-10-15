
# Guía de contribución

Gracias por tu interés en contribuir al proyecto **Formulario de Contacto** para WordPress.

Este documento establece las pautas básicas para desarrollar, corregir errores y proponer mejoras al plugin.

## 1. Objetivo del proyecto

El plugin permite recibir mensajes mediante un formulario público y administrarlos desde el panel de WordPress.

Sus funcionalidades principales son:

- Captura de mensajes de contacto.
- Validación y almacenamiento de los datos.
- Consulta de mensajes desde el panel de administración.
- Visualización del detalle de cada mensaje.
- Eliminación de mensajes por usuarios autorizados.

## 2. Requisitos de desarrollo

Para trabajar en el proyecto se recomienda contar con:

- WordPress instalado en un entorno local o de pruebas.
- PHP compatible con la versión de WordPress utilizada.
- Un editor de código, como Visual Studio Code.
- Git para el control de versiones.
- Una base de datos compatible con WordPress.

Las pruebas deben realizarse preferentemente en un entorno local, evitando modificar sitios en producción durante el desarrollo.

## 3. Estructura del proyecto

La estructura principal del plugin es:

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
└── CONTRIBUTING.md
```

Cada archivo debe mantener una responsabilidad clara:

- `formulario-contacto.php`: inicialización del plugin y carga de clases.
- `class-formulario-contacto.php`: formulario público y procesamiento de envíos.
- `class-formulario-contacto-db.php`: operaciones de base de datos.
- `class-formulario-contacto-admin.php`: administración de mensajes.
- `templates/formulario-contacto.php`: estructura del formulario público.
- `assets/`: archivos de estilos y JavaScript.

## 4. Convenciones de código

Se recomienda seguir estas pautas:

- Utilizar nombres descriptivos para clases, funciones y variables.
- Mantener una indentación consistente.
- Evitar duplicar funcionalidades existentes.
- Separar la lógica de negocio de la presentación cuando sea posible.
- Utilizar las funciones y API de WordPress cuando correspondan.
- Documentar las funciones cuyo propósito no sea evidente.
- Evitar cambios innecesarios en archivos que no estén relacionados con la tarea.

## 5. Seguridad

Toda contribución debe considerar la seguridad del plugin.

- Validar y sanitizar los datos recibidos.
- Escapar los datos antes de mostrarlos en HTML.
- Verificar nonces en las solicitudes que lo requieran.
- Comprobar las capacidades del usuario antes de realizar operaciones administrativas.
- Utilizar consultas preparadas cuando se construyan consultas SQL con datos variables.
- No incluir contraseñas, tokens, credenciales ni datos personales reales en el repositorio.

## 6. Proceso de trabajo con Git

Antes de realizar cambios:

1. Revisar el estado del repositorio con `git status`.
2. Identificar los archivos que se modificarán.
3. Realizar los cambios de forma controlada.
4. Revisar las diferencias con `git diff`.
5. Ejecutar las pruebas correspondientes.
6. Crear un commit con un mensaje descriptivo.

Ejemplos de mensajes de commit:

- `feat: agregar validación del correo electrónico`
- `fix: corregir la eliminación de mensajes`
- `docs: actualizar las instrucciones de instalación`
- `style: mejorar los estilos del formulario`

No se deben incluir archivos temporales, copias de seguridad ni credenciales en los commits.

## 7. Pruebas

Antes de considerar terminado un cambio, se recomienda verificar:

- Que el plugin se active sin errores.
- Que el formulario público se muestre correctamente.
- Que los campos obligatorios y el correo se validen.
- Que los mensajes se guarden correctamente.
- Que el administrador pueda consultar los mensajes.
- Que la eliminación funcione y regrese al panel administrativo.
- Que las solicitudes inválidas no ejecuten operaciones no autorizadas.

Las pruebas deben utilizar datos ficticios y realizarse en un entorno seguro.

## 8. Cambios en la base de datos

Los cambios relacionados con la base de datos deben revisarse cuidadosamente.

- Evitar eliminar o alterar información existente sin una justificación clara.
- No modificar el esquema de la tabla sin evaluar las consecuencias.
- Mantener la compatibilidad con los mensajes ya almacenados.
- Documentar los cambios relevantes en `CHANGELOG.md`.

## 9. Documentación

Cuando una contribución modifique el comportamiento del plugin, actualizar la documentación correspondiente.

Los cambios funcionales importantes deben registrarse en `CHANGELOG.md`.

Las instrucciones de instalación, configuración y uso deben mantenerse en `README.md`.

## 10. Propuestas de mejora

Las propuestas de nuevas funcionalidades, correcciones o mejoras deben describir:

- El problema que se desea resolver.
- El comportamiento esperado.
- Los archivos o componentes involucrados, si se conocen.
- Las pruebas necesarias para comprobar el resultado.

Se recomienda mantener cada cambio enfocado en un objetivo concreto.

## 11. Principios del proyecto

El desarrollo debe priorizar:

- Seguridad.
- Claridad y mantenibilidad del código.
- Compatibilidad con WordPress.
- Protección de los datos almacenados.
- Cambios pequeños, verificables y documentados.

Gracias por contribuir a mejorar el proyecto.
