<?php
/*
  Plugin Name: WP Lang URL Manager
  Description: Plugin that manages language URLs
  Version: 2.0.0
 */

require_once("constants.php");

class WPLangUrls {

    function __construct()
    {

        add_action( 'init', function() {
            global $wp_rewrite;
            $wp_rewrite->set_permalink_structure( '/%postname%/' );
        } );

        if ( is_admin() ) {
            if (!$this::files_are_edited()) {
                add_action( 'admin_notices', [$this,'files_admin_notices']);
            }

            if (!$this::environment_is_defined()) {
                add_action( 'admin_notices', [$this,'env_admin_notices']);
            }

            # Auto-update from GitHub
            add_action( 'init', [$this,'auto_update_plugin']);
        }
        else {
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
        if (count($path_parts) > 1 && strlen($path_parts[1]) == 2 && in_array($path_parts[0],SITE_SLUGS_LIST)) {
            $prefix = $url_head . "/" . $path_parts[1] . "/" . $path_parts[0];
            $url = $prefix . substr($url, strlen($prefix));
        }
        return  rtrim($url, "/");
    }

    public static function reformat_urls_on_page($page_text) {
        if (!is_admin()) {

            $separator = "~";

            $hotels_pattern = "(" . implode("|", SITE_SLUGS_LIST) . ")";
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

    function auto_update_plugin()
    {
        include_once('updater.php');
        define( 'WP_GITHUB_FORCE_UPDATE', true );

        if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
            $config = array(
                'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
                'proper_folder_name' => 'wp-lang-urls', // this is the name of the folder your plugin lives in
                'api_url' => 'https://api.github.com/repos/Codeko/wp-lang-urls', // the GitHub API url of your GitHub repo
                'raw_url' => 'https://raw.github.com/Codeko/wp-lang-urls/master', // the GitHub raw url of your GitHub repo
                'github_url' => 'https://github.com/Codeko/wp-lang-urls', // the GitHub url of your GitHub repo
                'zip_url' => 'https://github.com/Codeko/wp-lang-urls/zipball/master', // the zip url of the GitHub repo
                'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
                'requires' => '6.4.1', // which version of WordPress does your plugin require?
                'tested' => '6.4.1', // which version of WordPress is your plugin tested up to?
                'readme' => 'README.md', // which file to use as the readme for the version number
            );
            new WP_GitHub_Updater($config);
        }
    }

    /*
     * Check if wp-config.php and index.php has have been edited as necessary
     */
    function files_are_edited(){
        $f_config = ABSPATH . "wp-config.php";
        $s_config = '/plugins/wp-lang-urls/reformat_urls.php';
        $f_index = ABSPATH . "index.php";
        $s_index = "wp_replace_urls";
        return self::check_file_adition($f_config, $s_config) && self::check_file_adition($f_index, $s_index);
    }

    function environment_is_defined(): bool
    {
        if (!empty(SITE_SLUGS_LIST)) {
            return true;
        } else {
            return false;
        }
    }

    static function check_file_adition($file, $string){
        return strpos(file_get_contents($file), $string) != false;
    }

    static function files_admin_notices(){
        $notice = <<<EOT
    <div class="notice notice-error">
        <p><strong>WP Lang URL Manager:</strong> No se ha añadido el código necesario en "index.php" y/o "wp-config.php".</p>
    </div>
EOT;
        echo $notice;
    }

    static function env_admin_notices(){
        $notice = <<<EOT
    <div class="notice notice-error">
        <p><strong>WP Lang URL Manager:</strong> No se ha definido la variable de entorno "SITE_SLUGS_LIST".</p>
    </div>
EOT;
        echo $notice;
    }

}

new WPLangUrls();

