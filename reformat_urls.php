<?php

require_once("constants.php");

class ReformatUrls {

    function __construct()
    {
        self::process_url_site();
    }

    public static function process_url_site() {
        $debug = self::is_debug_active();

        /**
         * Si la URL tiene el idioma antes que el sitio, la dejamos como está,
         * pero sobrescribimos la variable de entorno $_SERVER["REQUEST_URI"] para que WPML la interprete correctamente.
         *
         * Si el idioma va detrás del sitio, redireccionamos con un 301 a la URL modificada (con el idioma delante del sitio).
         *
         * Sabemos qué fragmento de la ruta corresponde al idioma porque tiene dos carateres de largo.
         */
        $url_path = $_SERVER["REQUEST_URI"];
        $path_parts = explode("/",ltrim(parse_url($url_path,PHP_URL_PATH),"/"));
        $is_admin = strpos($url_path, "wp-admin");
        $is_login = strpos($url_path, WP_LOGIN_DIRECTORY);
        $is_wp_json = strpos($url_path, "wp-json");

        $path_is_hotel_and_lang = count($path_parts) > 1 && in_array($path_parts[0], SITE_SLUGS_LIST) && in_array($path_parts[1], LANGUAGE_LIST);
        $path_is_lang_and_hotel = count($path_parts) > 1 && in_array($path_parts[0], LANGUAGE_LIST) && in_array($path_parts[1],SITE_SLUGS_LIST);
        $path_is_only_hotel = count($path_parts) == 1 && in_array($path_parts[0], SITE_SLUGS_LIST);
        if($debug){
            print_r([
                "url_path"=>$url_path,
                "path_parts"=>$path_parts,
                "path_is_hotel_and_lang"=>$path_is_hotel_and_lang,
                "path_is_lang_and_hotel"=>$path_is_lang_and_hotel,
                "path_is_only_hotel"=>$path_is_only_hotel
            ]);
        }
        if (!$is_admin && !$is_login && !$is_wp_json)  {
            if($path_is_only_hotel) {
                /**
                 * Si no hay idioma incluido en la url
                 * Incluimos idioma por defecto y redireccionamos a esta con formato /YY/XXXX...
                 */
                array_unshift($path_parts, DEFAULT_LANGUAGE);
                $path_prefix = "/" . $path_parts[0] . "/" . $path_parts[1];
                $new_url_path = $path_prefix . '/' . substr($url_path, strlen($path_prefix) - 2);
                $complete_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$new_url_path";
                if($debug){
                    echo "path_is_only_hotel redirect to → ". rtrim($complete_url, "/");
                    exit();
                }
                header("Location: " . rtrim($complete_url, "/"), true, 301);
                exit();
            } elseif($path_is_hotel_and_lang) {
                self::redirect_to_lang_hotel($path_parts, $url_path, $debug);

            } elseif ($path_is_lang_and_hotel) {
                /**
                 * Si la variable de entorno $_SERVER["REQUEST_URI"]  tiene el formato /XX/YYYY...
                 * la sobrescribimos para que WP crea que es /YYYY/XX...
                 */
                $path_prefix = "/" . $path_parts[1] . "/" . $path_parts[0];
                $path_tail = (strlen($url_path) == strlen($path_prefix)) ? "/" : substr($url_path, strlen($path_prefix));
                # Si es el path raiz (no es post ni página) se le añade "/" al final, para que WP no lo redireccione
                # Si no, se le añade el resto del path original /el post, página, o lo que sea)
                // Add slash to not query part url
                $path_tail_parts = explode("?", $path_tail,2);
                $_SERVER["REQUEST_URI"] = $path_prefix . rtrim($path_tail_parts[0], "/")."/";
                // If hast query string add it

                if(count($path_tail_parts)>1){
                    $_SERVER["REQUEST_URI"] .="?".$path_tail_parts[1];
                }

                // $_SERVER["REQUEST_URI"] =  rtrim($_SERVER["REQUEST_URI"] , "/");

                if($debug){
                    echo "path_is_lang_and_hotel set REQUEST_URI → ". $_SERVER["REQUEST_URI"]."<br/>";
                }
            }

            # Si contiene /? y el metodo es GET, se le quita esa barra
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($url_path, "/?") !== false) {
                if($debug){
                    echo "remove end slash /? redirect to → ". str_replace("/?", "?", $url_path);
                    exit();
                }
                header("Location: " .  str_replace("/?", "?", $url_path), true, 301);
                exit();
            }

            # En cualquier caso, si acaba en / y el metodo es GET
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && str_ends_with($url_path, "/")) {
                if($debug){
                    echo "remove end slash / redirect to → ". rtrim($url_path, "/");
                    exit();
                }
                header("Location: " . rtrim($url_path, "/"), true, 301);
                exit();
            }
            if($debug){
                echo "request: " . $_SERVER["REQUEST_URI"] . "<br>";
                echo "Not redirect, go to WP";
            }
        }
        if($debug){
            echo "<br>Is admin, login or wp_json:";
            if ($is_admin) {
                echo "is admin<br>";
            }
            if ($is_login) {
                echo "is login <br>";
            }
            if ($is_wp_json) {
                echo "is wp_json<br>";
            }

            echo "request: " . $_SERVER["REQUEST_URI"] . "<br>";
            echo "Not redirect, go to WP";
        }
    }


    private static function redirect_to_lang_hotel(array $path_parts, mixed $url_path,bool $debug): void
    {
        /**
         * Solo en GET:
         * Si la variable de entorno $_SERVER["REQUEST_URI"]  tiene el formato /XXXX/YY...
         * con YY de dos carateres de largo (porque YY es el idioma)
         * redireccionamos con un 301 a la URL correcta, que debe ser del formato /YY/XXXX...
         */
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $path_prefix = "/" . $path_parts[1] . "/" . $path_parts[0];
            $new_url_path = $path_prefix . substr($url_path, strlen($path_prefix));
            $complete_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$new_url_path";
            if($debug){
                echo "path_is_hotel_and_lang redirect to → ". rtrim($complete_url, "/");
                exit();
            }
            header("Location: " . rtrim($complete_url, "/"), true, 301);
            exit();
        }
    }


    private static function is_debug_active(): bool
    {
        // return true;
        return !empty($_GET['DEBUG6839']);
    }

}

new ReformatUrls();
