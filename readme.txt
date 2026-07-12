=== WebPagesPassworded ===
Contributors: rafamm-glocalsaino
Tags: password, protected pages, child pages, access control, shortcode
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 4.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Página de acceso centralizada para páginas hijo protegidas con contraseña. El shortcode [wppw] muestra un formulario que redirige al visitante a la página correcta.

== Description ==

**WebPagesPassworded** permite crear una página de acceso única que actúa como puerta de entrada a un conjunto de páginas hijo protegidas con contraseña. Basta con colocar el shortcode `[wppw]` en la página padre: el plugin genera un formulario de contraseña y, al introducir la correcta, redirige automáticamente al visitante a la página hijo que tenga esa contraseña asignada.

= Cómo funciona =

1. En WordPress se crea una página padre (p. ej. "Acceso privado") y se marcan como hijo suyas las páginas que se quieren proteger.
2. Cada página hijo lleva su propia contraseña configurada desde **Publicar → Visibilidad → Protegido con contraseña** en el editor de WordPress.
3. El shortcode `[wppw]` se inserta en la página padre.
4. Cuando un visitante llega a esa página, ve el formulario de contraseña del plugin. Si la contraseña coincide con la de alguna página hijo, es redirigido a ella automáticamente.

= Características =

* **Un único punto de acceso** — una sola página con el shortcode da entrada a todas las páginas hijo protegidas.
* **Redirección automática** — el visitante llega directamente a la página correcta sin pasos adicionales.
* **Cookie de sesión** — la contraseña se almacena en una cookie durante 10 días para que el visitante no tenga que volver a introducirla en visitas sucesivas.
* **Nonce de seguridad** — el formulario incluye un nonce de WordPress para proteger el envío contra ataques CSRF.
* **Mensaje de error claro** — si la contraseña es incorrecta se muestra un aviso en el mismo formulario.
* **Protección contra fuerza bruta** — tras 5 intentos fallidos desde la misma IP, el acceso queda bloqueado durante 15 minutos. El bloqueo se almacena mediante transients de WordPress, sin tablas adicionales.
* **Cookie segura** — la cookie de sesión se establece con los flags `HttpOnly` y `SameSite=Strict` para impedir su lectura desde JavaScript y su envío en peticiones cruzadas.
* **Sin dependencias externas** — el plugin no carga ninguna librería ni recurso externo.

= Uso =

Inserta el shortcode en la página padre que actuará como formulario de acceso:

`[wppw]`

Parámetros opcionales del shortcode:

* `label` — texto del botón de envío. Por defecto: `Enter`.
* `id` — atributo `id` del elemento `<form>`. Por defecto: `wppwLogin`.
* `parent` — ID de la página padre cuyos hijos se buscarán. Por defecto: ID de la página actual.

Ejemplo con parámetros:

`[wppw label="Acceder" id="mi-formulario"]`

= Requisitos =

* WordPress 5.0 o superior.
* PHP 7.4 o superior.
* Las páginas hijo deben estar publicadas y tener contraseña configurada desde el editor de WordPress.

== Installation ==

1. Sube la carpeta `wp-webpagespassworded` a `/wp-content/plugins/`.
2. Activa el plugin desde **Plugins → Plugins instalados**.
3. Crea una página padre y añade las páginas hijo con sus contraseñas desde **Publicar → Visibilidad → Protegido con contraseña**.
4. Inserta `[wppw]` en la página padre.

== Frequently Asked Questions ==

= ¿Puedo tener varias páginas padre con sus propios grupos de páginas hijo? =

Sí. El shortcode `[wppw]` colocado en cada página padre buscará únicamente entre sus páginas hijo directas. Cada grupo es independiente.

= ¿Qué ocurre si dos páginas hijo tienen la misma contraseña? =

El visitante es redirigido a la página hijo más reciente (por fecha de publicación) que coincida con la contraseña introducida.

= ¿Cuánto tiempo dura la cookie de acceso? =

10 días. Pasado ese tiempo, o si el visitante borra las cookies del navegador, tendrá que introducir la contraseña de nuevo.

= ¿Funciona con HTTPS? =

Sí. Si el sitio usa HTTPS la cookie se marca automáticamente como `Secure`.

= Introduje la contraseña correcta pero me aparece el mensaje de bloqueo. ¿Qué hago? =

El bloqueo dura 15 minutos y está ligado a la IP. Si eres el administrador y necesitas desbloquearte antes de que expire, ve a **Herramientas → Site Health → Info** para confirmar tu IP y luego elimina manualmente los transients con prefijo `wppw_lock_` y `wppw_fail_` desde la base de datos o con un plugin de gestión de transients.

= ¿Es compatible con plugins de exclusión de páginas? =

Sí. Si el sitio tiene instalado un plugin con las funciones `pause_exclude_pages()` / `resume_exclude_pages()` (patrón habitual en plugins de exclusión de menús y listados), WebPagesPassworded las llama antes y después de consultar las páginas hijo para garantizar que las páginas protegidas sean siempre encontradas.

= La contraseña es correcta pero no se redirige al visitante. ¿Qué puede pasar? =

Comprueba que:

* La página hijo está publicada (no en borrador).
* La página hijo es hija directa de la página que contiene el shortcode, no nieta.
* La contraseña en WordPress se guarda sin espacios adicionales al principio o al final.

== Privacy Policy ==

WebPagesPassworded no recopila, almacena ni transmite ningún dato personal.

* El plugin lee la contraseña introducida en el formulario únicamente para compararla con las contraseñas de las páginas hijo almacenadas en la base de datos de WordPress. Esa comparación ocurre íntegramente en el servidor y el dato no se guarda ni se envía a ningún tercero.
* Se almacena una cookie (`wp-postpass_*`) en el navegador del visitante para mantener el acceso activo durante 10 días. Esta cookie es estándar de WordPress y solo contiene el hash de la contraseña, nunca la contraseña en texto claro.
* No se realizan conexiones externas de ningún tipo.

== Upgrade Notice ==

= 4.2.0 =
Nueva funcionalidad premium: login links para autenticar usuarios de WordPress sin contraseña. No requiere ninguna acción de migración.

= 4.1.0 =
Nueva funcionalidad premium: enlaces mágicos para dar acceso directo sin contraseña. No requiere ninguna acción de migración.

= 4.0.0 =
Versión mayor con integración Freemius y funcionalidades premium. Compatible con versiones anteriores: el shortcode `[wppw]` sigue funcionando igual.

== Changelog ==

= 4.2.0 =
* Nueva funcionalidad premium: login links. Genera enlaces firmados que autentican a un usuario de WordPress directamente, sin necesidad de introducir contraseña.
* El token del login link es un valor aleatorio de 256 bits; nunca contiene ni expone las credenciales del usuario.
* Caducidad y límite de usos configurables (por defecto: 1 día, 1 uso).
* URL de redirección post-login configurable por enlace.
* Panel de administración para crear, listar y revocar login links, con copia al portapapeles en un clic.

= 4.1.0 =
* Nueva funcionalidad premium: enlaces mágicos. Genera enlaces que dan acceso directo a una página protegida sin pedir la contraseña.
* Los enlaces mágicos usan un token aleatorio de 256 bits (no la contraseña real), con caducidad y límite de usos configurables.
* Panel de administración para crear, listar y revocar enlaces mágicos, con copia al portapapeles de un clic.
* Corregido el enqueue de Font Awesome: se cargaba demasiado tarde dentro de wp_head y nunca llegaba a imprimirse.
* Sustituida la detección por has_shortcode() (fallaba con Elementor y otros maquetadores) por una comprobación basada en is_singular().
* Añadido !important a los valores de CSS generados para evitar que los estilos del tema activo sobrescriban el diseño configurado.

= 4.0.0 =
* Integración con Freemius para gestión de licencias y versión premium.
* Nuevo panel en Ajustes → WebPagesPassworded con secciones gratuitas y premium.
* Premium: texto del botón personalizable desde el panel de ajustes.
* Premium: mensajes de error personalizables (contraseña incorrecta y bloqueo por intentos).
* Premium: diseño del formulario configurable (colores de fondo, texto y borde del campo; colores, tamaño y fuente del botón).
* Premium: icono personalizable dentro del botón, seleccionado desde la biblioteca de medios.
* Premium: espaciado configurable entre el campo de contraseña y el botón.
* Código refactorizado en clases separadas (`class-wppw-core.php`, `class-wppw-admin.php`, `class-wppw-styles.php`).
* Botón cambiado de `<input type="submit">` a `<button type="submit">` para permitir contenido HTML (icono).
* CSS del formulario inyectado únicamente en páginas que contienen el shortcode `[wppw]`.

= 3.1.0 =
* Añadida protección contra fuerza bruta: bloqueo de 15 minutos tras 5 intentos fallidos por IP.
* Cookie de sesión establecida con flags `HttpOnly` y `SameSite=Strict`.
* Añadida guarda de acceso directo al archivo (`defined('ABSPATH') || exit`).
* Eliminada comparación `CheckPassword` incorrecta en el procesamiento del formulario.
* Versión bump a 3.1.0.

= 3.0.0 =
* Refactorización completa para compatibilidad con PHP 7.4+ y WordPress 5.0+.
* Eliminado el uso de `extract()`.
* Eliminados los bloques de compatibilidad con versiones de WordPress anteriores a 3.6.
* Añadida visibilidad explícita a todos los métodos de la clase.
* Añadidos type hints de retorno en los métodos principales.
* Saneamiento mejorado de los datos recibidos por POST.
* Nuevos nombres de shortcode y campos de formulario con prefijo `wppw`.

= 3.1.0 =
Actualización de seguridad recomendada. Añade protección contra fuerza bruta y mejora los flags de la cookie de sesión.

= 3.0.0 =
El shortcode ha cambiado de `[smartpwpages]` a `[wppw]`. Si actualizas desde una versión anterior, sustituye el shortcode en todas las páginas donde esté insertado.
