# Plugin para modificar la estructura de URLs

En algunos sitios se usa un multisite con el plugn WPML, que crea URLs por idiomas del tipo:

```
http://dominio/sitio/es/ruta/a/la/pagina
```

pero necesitamos que sea del tipo:

```
http://dominio/es/sitio/ruta/a/la/pagina
```

con el idioma delante.

(Además, queremos quitar las barras finales de las URLs)


## Componentes

- WP (multisite)
- Plugin WPML
- Plugin WPLangUrls (este)


## Funcionamiento

Este plugin hace las siguientes cosas:

Modifica la variable de entorno `REQUEST_URI`, para que, cuando accedemos a una direccion como `http://dominio/es/sitio`, WP (y el resto de plugins) crea que estamos usando `http://dominio/sitio/es/` (ojo a esa barra final, ver nota más abajo).

Redirecciona las llamadas a URLs incorrectas a su versión correcta (por ejemplo, si se accede a`http://dominio/sitio/es`, redirecciona a: `http://dominio/es/sitio`).

Redirecciona las llamadas a URLs que no contienen el idioma a esa misma url, pero con el idioma por defecto (por ejemplo, si se accede a`http://dominio/sitio` y el idioma por defecto es el inglés, redirecciona a: `http://dominio/en/sitio`).

Sobreescribe el contenido de la página que se está mostrando, para que las URLs generadas por WP tengan el formato http://dominio/sitio/es.


### Nota:

Además, el plugin sobreescribe la estructura de permalinks de WP a '/%postname%/' porque parece que, por alguna razón, WPML necesita que los permalinks acaben en barra (si no se hace así, entramos en un bule infinito de redirecciones entre nuestro plugin y WPML).


## Instalación


### 1

Definir las siguientes variables de entorno:

`WP_LANG_SITE_SLUGS_LIST` (necesaria): Debe contener la lista de sitios separados por comas.

Si esta variable de entorno no está definida, dará errores 404 al acceder al frontend y se mostrará un texto de alerta en el backend.

`WP_LANG_LANGUAGE_LIST` (opcional): Debe contener la lista de idiomas separados por comas.

si no existe esta variable de entorno, se usa la cadena por defecto 'en,es,de,nl,it,fr,pt,co,mx,ar'

El primero de los idomas de la lista se considerará el idioma por defecto.


### 2

Configurar WPML:

En WPML -> Idiomas, deben estar marcadas las opciones "Distintos idiomas en los directorios" y "Usar directorio para idioma predeterminado".


### 3

Activar este Plugin


### 4

Incluir el siguiente snippet al principio del index.php de WP:


```
function wp_replace_urls($output){
    if($output && class_exists('WPLangUrls')) {
        return WPLangUrls::reformat_urls_on_page($output);
    } else {
        return $output;
    }
}
// Set output callback to make the url replacement on it. Set flags to 0 to avoid any code to flush/clean this and
// force it to be the "main" and last ob_start/end.
ob_start("wp_replace_urls", 0, 0);
```

Esto invoca al código que se ocupa de buscar y reemplazar las URLs en la página que se está mostrando.

Como el archivo index.php se sobreescribe cada vez que se actualiza WP, hay que volver a incluir este código en cada actualización.


### 5

En wp-config.php (después de definir `WP_CONTENT_FOLDERNAME` pero antes de la línea `require_once(ABSPATH . 'wp-settings.php');`) hay que añadir lo siguiente:

```
include_once(WP_CONTENT_FOLDERNAME . '/plugins/wp-lang-urls/reformat_urls.php');
```

Esta es la llamada al código que afecta a la estructura de URLs, modificando la variable de entorno `REQUEST_URI` y redireccionando a una nueva URL si es necesario.

(Si no se incluye el código anterior en los archivos index.php y wp-config.php, se mosrtará un mensaje de alerta en el backend.)
