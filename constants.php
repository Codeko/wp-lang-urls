<?php

/*
 La variable de entorno WP_LANG_LANGUAGE_LIST debe contener la lista de idiomas separados por comas.
 El primero de ellos es el idioma por defecto
 si no existe la variable de entorno, se usa 'en,es,de,nl,it,fr,pt,co,mx,ar'
*/
$language_list_string = (getenv('WP_LANG_LANGUAGE_LIST')) ? getenv('WP_LANG_LANGUAGE_LIST') : 'en,es,de,nl,it,fr,pt,co,mx,ar';
define("LANGUAGE_LIST", explode(",", $language_list_string));
const DEFAULT_LANGUAGE = LANGUAGE_LIST[0];

/*
 La variable de entorno WP_LANG_SITE_SLUGS_LIST debe contener la lista de sitios separados por comas.
 Si la variable de entorno no está definida, dará errores 404 al acceder al frontend, y se mostrará un texto de alerta en el backend
*/
$hotel_slug_list_string = getenv('WP_LANG_SITE_SLUGS_LIST');
if ($hotel_slug_list_string) {
    define("SITE_SLUGS_LIST", explode(",", $hotel_slug_list_string));
} else {
    // ERROR
    define("SITE_SLUGS_LIST", []);
}
