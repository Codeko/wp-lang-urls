<?php

/*
 La variable de entorno WP_LANG_LANGUAGE_LIST debe contener la lista de idiomas separados por comas.
 El primero de ellos es el idioma por defecto
 si no existe la variable de entorno, se usa 'en,es,de,nl,it,fr,pt,co,mx,ar'
*/
$language_list_string = (getenv('WP_LANG_LANGUAGE_LIST')) ? getenv('WP_LANG_LANGUAGE_LIST') : 'en,es,de,nl,it,fr,pt,co,mx,ar';
define("DEFAULT_LANGUAGE_LIST", explode(",", $language_list_string));

/*
 La variable de entorno WP_LANG_SITE_SLUGS_LIST debe contener la lista de sitios separados por comas.
 Si la variable de entorno no está definida, dará errores 404 al acceder al frontend, y se mostrará un texto de alerta en el backend
*/
$hotel_slug_list_string = getenv('WP_LANG_SITE_SLUGS_LIST');
if ($hotel_slug_list_string) {
    define("DEFAULT_SITE_SLUGS_LIST", explode(",", $hotel_slug_list_string));
} else {
    // ERROR
    define("DEFAULT_SITE_SLUGS_LIST", []);
}

$wp_login_directory = getenv('WP_LOGIN_DIRECTORY')?: "wp-login";
define("DEFAULT_WP_LOGIN_DIRECTORY", $wp_login_directory);

define("DEFAULT_URL_IGNORE_LIST", []);

define("JSON_DIR_PATH",  __DIR__  . '/data');
define("JSON_FILE_PATH", JSON_DIR_PATH  . '/wp-lang-urls-data.json');

$permalink_structure = getenv('WP_LANG_PERMALINK_STRUCTURE');
if ($permalink_structure) {
    define("DEFAULT_PERMALINK_STRUCTURE", $hotel_slug_list_string);
} else {
    define("DEFAULT_PERMALINK_STRUCTURE", '/%postname%/');
}

$json_data = [];

# Se extraen valores del JSON; si no existe, se usan los valores por defecto

if (file_exists(JSON_FILE_PATH)) {
    $json_file_object = file_get_contents(JSON_FILE_PATH);
    $json_data = json_decode($json_file_object, true);
}

if (isset($json_data['langs']) && count($json_data['langs'])) {
    define("LANGUAGE_LIST",  $json_data['langs']);
} else {
    define("LANGUAGE_LIST", DEFAULT_LANGUAGE_LIST);
}
const DEFAULT_LANGUAGE = LANGUAGE_LIST[0];

if (isset($json_data['slugs']) && count($json_data['slugs'])) {
    define("SITE_SLUGS_LIST", $json_data['slugs']);
} else {
    define("SITE_SLUGS_LIST", DEFAULT_SITE_SLUGS_LIST);
}

if (isset($json_data['login_directory']) && $json_data['login_directory']) {
    define("WP_LOGIN_DIRECTORY", $json_data['login_directory']);
} else {
    define("WP_LOGIN_DIRECTORY", DEFAULT_WP_LOGIN_DIRECTORY);
}

if (isset($json_data['ignore_urls']) && count($json_data['ignore_urls'])) {
    define("URL_IGNORE_LIST", $json_data['ignore_urls']);
} else {
    define("URL_IGNORE_LIST", DEFAULT_URL_IGNORE_LIST);
}

if (isset($json_data['permalink_structure']) && $json_data['permalink_structure']) {
    define("PERMALINK_STRUCTURE", $json_data['permalink_structure']);
} else {
    define("PERMALINK_STRUCTURE", DEFAULT_PERMALINK_STRUCTURE);
}
