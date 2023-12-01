<?php
/*
  Plugin Name: NHow Lang URL Manager
  Description: Plugin that manages language URLs for NHow
  Version: 1.5.0

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
        if ( is_admin() && !$this::files_are_edited()) {
            add_action( 'admin_notices', [$this,'admin_notices']);
        }

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

    /*
     * Check if wp-config.php and index.php has have been edited as necessary
     */
    function files_are_edited(){
        $f_config = ABSPATH . "wp-config.php";
        $s_config = '/plugins/nhow-lang-urls/reformat_urls.php';
        $f_index = ABSPATH . "index.php";
        $s_index = "nh_replace_urls";
        return self::check_file_adition($f_config, $s_config) && self::check_file_adition($f_index, $s_index);
    }

    static function check_file_adition($file, $string){
        return strpos(file_get_contents($file), $string) != false;
    }

    static function admin_notices(){
        $notice = <<<EOT
    <div class="notice notice-error">
        <p><strong>NHow Lang URL Manager:</strong> No se ha añadido el código necesario en "index.php" y/o "wp-config.php".</p>
    </div>
EOT;
        echo $notice;
    }

}

new NHowLangUrls();

