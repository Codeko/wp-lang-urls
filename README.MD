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


## Proyectos donde lo usamos:

- NHow
- NHPro
- NH-Corporate


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


