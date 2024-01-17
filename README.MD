# Plugin para modificar la estructura de URLs

En algunos sitios de NH se usa un multisite con el plugn WPML, que crea URLs por idiomas del tipo:

```
http://dominio/sitio/es/ruta/a/la/pagina
```

pero necesitamos que sea del tipo:

```
http://dominio/es/sitio/ruta/a/la/pagina
```

con el idioma delante.

(Además, queremos quitar las barras finales de las URLs)

## Proyectos donde lo usamos

### Sitios principlaes:

- NHow
- promos.minor-hotels

### Sitios secundarios:

- NH-Corporate
- NHPro
- NH Coming Soon


## Componetnes:

- WP (multisite)
- Plugin WPML
- Plugin NHowLangUrls
- El siguiente snippet al principio del index.php de WP:


```
function nh_replace_urls($output){
    if($output && class_exists('NHowLangUrls')) {
        return NHowLangUrls::reformat_urls_on_page($output);
    } else {
        return $output;
    }
}
// Set output callback to make the url replacement on it. Set flags to 0 to avoid any code to flush/clean this and
// force it to be the "main" and last ob_start/end.
ob_start("nh_replace_urls", 0, 0);
```

En wp-config.php (después de definir WP_CONTENT_FOLDERNAME pero antes de la línea "require_once(ABSPATH . 'wp-settings.php');") hay que añadir lo siguiente:

```
include_once(WP_CONTENT_FOLDERNAME . '/plugins/nhow-lang-urls/reformat_urls.php');
```

En el archivo "constats.php" (en el directorio del plugin), hay que editar las siguientes constantes:

IS_MAIN_SITE (bool)

Defien si es un sitipo principal, como NHow o promos.minor-hotels (true), o un sitio que está incluído en el path de otro, como NHPro o NH-Corporate (false).

HOTEL_SLUGS_LIST (array)

Lista de strings con los slugs de los hoteles (o de lo que sea), como "nhow-berlin" o "nhpro".

LANGUAGE_LIST (array)

Lista de strings con los códigos de idioma que se usan en la página, como, por ejemplo,"es" o "en".

DEFAULT_LANGUAGE (str)

código del idioma por defecto del sitio, para que las URLs sin idioma se redirijan a este. Solo es necesario definirla en los sitios secundarios, porque WPML puede hacerlo en los sitios principales.

AR_IS_ARGENTINE (bool)

Si el sitio usa "ar" como argentino en lugar de árabe hay que filtrarlo en WPML para que no sea LTR.



