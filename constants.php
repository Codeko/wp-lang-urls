<?php

define("WP_LANG_URL_JSON_DIR_PATH",  WP_CONTENT_DIR . "/uploads/wp-lang-urls-data");
define("WP_LANG_URL_JSON_FILE_PATH", WP_LANG_URL_JSON_DIR_PATH  . '/wp-lang-urls-data.json');

wp_lang_urls_prepare_config_directory(WP_LANG_URL_JSON_DIR_PATH);

# Harcoded configuration values
define("WP_LANG_URL_HARDCODED_CONFIG", [
    "langs" => ['en','es','de','nl','it','fr','pt','co','mx','ar'],
    "slugs" => [],
    "login_directory" => "wp-login",
    "ignore_urls" => [],
    "permalink_structure" => "/%postname%/"]);

# Environment configuration values
define("WP_LANG_URL_ENV_CONFIG", [
    "langs" => (getenv('WP_LANG_LANGUAGE_LIST'))? explode(",", getenv('WP_LANG_LANGUAGE_LIST')) : [],
    "slugs" => (getenv('WP_LANG_SITE_SLUGS_LIST'))? explode(",", getenv('WP_LANG_SITE_SLUGS_LIST')) : [],
    "login_directory" => getenv('WP_LANG_LOGIN_DIRECTORY'),
    "ignore_urls" => (getenv('WP_LANG_URL_IGNORE_LIST'))? explode(",", getenv('WP_LANG_URL_IGNORE_LIST')) : [],
    "permalink_structure" => getenv('WP_LANG_PERMALINK_STRUCTURE')]);

# JSON configuration values
define("WP_LANG_URL_JSON_CONFIG", wp_lang_urls_get_data_from_json());

# Joined configuration values
define("WP_LANG_URL_CONFIG", wp_lang_urls_get_config_values());

function wp_lang_urls_prepare_config_directory($path) {
    if (!file_exists($path . "/.htaccess")) {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        file_put_contents($path . "/.htaccess", "order deny,allow\ndeny from all");
    }
}

function wp_lang_urls_get_data_from_json($json_path=null) {
    $json_path = ($json_path)?: WP_LANG_URL_JSON_FILE_PATH;
    $json_data = [];
    if (file_exists($json_path)) {
        $json_file_object = file_get_contents($json_path);
        $json_data = json_decode($json_file_object, true);
    }
    return [
        "langs" => (isset($json_data['langs']) && count($json_data['langs']))? $json_data['langs'] : [],
        "slugs" => (isset($json_data['slugs']) && count($json_data['slugs']))? $json_data['slugs'] : [],
        "login_directory" => (isset($json_data['login_directory']))? $json_data['login_directory'] : "",
        "ignore_urls" => (isset($json_data['ignore_urls']) && count($json_data['ignore_urls']))? $json_data['ignore_urls'] : [],
        "permalink_structure" => (isset($json_data['permalink_structure']))? $json_data['permalink_structure'] : ""
    ];
}

function wp_lang_urls_get_config_values($json_data=null, $env_data=null, $hardcoded_data=null) {
    $json_data = ($json_data)?: WP_LANG_URL_JSON_CONFIG;
    $env_data = ($env_data)?: WP_LANG_URL_ENV_CONFIG;
    $hardcoded_data = ($hardcoded_data)?: WP_LANG_URL_HARDCODED_CONFIG;
    $total = [];
    foreach ($json_data as $key => $value) {
        if ($value) {
            $total[$key] = $value;
        } elseif ($env_data[$key]) {
            $total[$key] = $env_data[$key];
        } else {
            $total[$key] = $hardcoded_data[$key];
        }
    }
    return $total;
}
