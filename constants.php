<?php

define("JSON_FILE_PATH",  __DIR__  . '/data/wp-lang-urls-data.json');
$json_data = [];

if (file_exists(JSON_FILE_PATH)) {
    $json_file_object = file_get_contents(JSON_FILE_PATH);
    $json_data = json_decode($json_file_object, true);
}

if (isset($json_data['langs']) && count($json_data['langs'])) {
    define("LANGUAGE_LIST",  $json_data['langs']);
} else {
    /*
     La variable de entorno WP_LANG_LANGUAGE_LIST debe contener la lista de idiomas separados por comas.
     El primero de ellos es el idioma por defecto
     si no existe la variable de entorno, se usa 'en,es,de,nl,it,fr,pt,co,mx,ar'
    */
    $language_list_string = (getenv('WP_LANG_LANGUAGE_LIST')) ? getenv('WP_LANG_LANGUAGE_LIST') : 'en,es,de,nl,it,fr,pt,co,mx,ar';
    define("LANGUAGE_LIST", explode(",", $language_list_string));
}
const DEFAULT_LANGUAGE = LANGUAGE_LIST[0];

if (isset($json_data['slugs']) && count($json_data['slugs'])) {
    define("SITE_SLUGS_LIST", $json_data['slugs']);
} else {
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
}

if (isset($json_data['login_directory']) && $json_data['login_directory']) {
    define("WP_LOGIN_DIRECTORY", $json_data['login_directory']);
} else {
    $wp_login_directory = getenv('WP_LOGIN_DIRECTORY')?: "wp-login";
    define("WP_LOGIN_DIRECTORY", $wp_login_directory);
}

if (isset($json_data['ignore_urls']) && count($json_data['ignore_urls'])) {
    define("URL_IGNORE_LIST", $json_data['ignore_urls']);
} else {
    define("URL_IGNORE_LIST", []);
}
