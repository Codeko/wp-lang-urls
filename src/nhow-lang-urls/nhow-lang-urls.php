<?php
/*
  Plugin Name: NHow Lang URL Manager
  Description: Plugin that manages language URLs for NHow
  Version: 1.0.0

Es necesario incluir en wp-config.php la siguiente línea:

include_once(WP_CONTENT_FOLDERNAME . '/plugins/nhow-lang-urls/reformat_urls.php');

Esto debe estar después de definir WP_CONTENT_FOLDERNAME,
pero antes de la línea "require_once(ABSPATH . 'wp-settings.php');"


 */

require_once("constants.php");
require_once("ar_is_argentine.php");

class NHowLangUrls {

    function __construct()
    {
        if (AR_IS_ARGENTINE == true){
            ar_is_argentine_filter();
        }
        if ( !is_admin() ) {

            add_filter('wpml_ls_language_url', [ $this, 'alternate_language_url' ], 10, 2);
            add_filter('wpml_alternate_hreflang', [ $this,  'alternate_language_url'], 10, 2);

            # Remove feeds
            remove_action( 'wp_head', 'feed_links_extra', 3 );
            remove_action( 'wp_head', 'feed_links', 2 );

            # Remove API REST headers
            remove_action( 'wp_head', 'rest_output_link_wp_head' );
            remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );

            # Remove oembed headers
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

            //Añadimos el slash al final de la url del hide login
            add_filter( 'wps_hide_login_home_url', [$this, 'add_final_slash_to_url'], 11, 1);
        }
    }


    /*
     * generic filter for reformat URLs
    */
    public function alternate_language_url($url, $language=null) {
        return $this::city_lang_to_lang_city($url);
    }


    /*
     * Helper function that converts URLs
     * from type SCHEMA://DOMAIN/XXXX/YY
     *   to type SCHEMA://DOMAIN/YY/XXXX
     * , if YY is two characters long.
     */
    public static function city_lang_to_lang_city($url)
    {
        $url_component = parse_url($url);
        $url_head = $url_component["scheme"] . "://" . $url_component["host"];

        $path_parts = explode("/", ltrim($url_component["path"], "/"));
        if (count($path_parts) > 1 && strlen($path_parts[1]) == 2 && in_array($path_parts[0],HOTEL_SLUGS_LIST)) {
            $prefix = $url_head . "/" . $path_parts[1] . "/" . $path_parts[0];
            $url = $prefix . substr($url, strlen($prefix));
        }
        return  rtrim($url, "/");
    }

    public static function reformat_urls_on_page($page_text) {
        if (!is_admin()) {

            $separator = "~";

            $hotels_pattern = "(" . implode("|", HOTEL_SLUGS_LIST) . ")";
            $languages_pattern = "(" . implode("|", LANGUAGE_LIST) . ")";

            $pattern = $separator . $_SERVER['HTTP_HOST'] . "/" . $hotels_pattern . "/" . $languages_pattern . $separator;
            $replacement = $_SERVER['HTTP_HOST'] . "/\\2/\\1\\3";
            return preg_replace($pattern, $replacement, $page_text);
        }
        return $page_text;
    }

    //Añadimos el slash al final de una URL
    function add_final_slash_to_url($url) {
        if (!str_ends_with($url, "/")) {
            return $url . "/";
        }
        return $url;
    }

}

new NHowLangUrls();
