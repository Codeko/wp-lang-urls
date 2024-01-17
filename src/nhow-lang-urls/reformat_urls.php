<?php

require_once("constants.php");

class ReformatUrls {

    function __construct()
    {
        if (IS_MAIN_SITE == true){
            self::process_url_main_site();
        } else {
            self::process_url_subsite();
        }
    }

    public static function process_url_subsite() {

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
        $is_login = strpos($url_path, "wp-login");
        $is_wp_json = strpos($url_path, "wp-json");

        $path_is_hotel_and_lang = count($path_parts) > 1 && in_array($path_parts[0], HOTEL_SLUGS_LIST) && in_array($path_parts[1], LANGUAGE_LIST);
        $path_is_lang_and_hotel = count($path_parts) > 1 && in_array($path_parts[0], LANGUAGE_LIST) && in_array($path_parts[1],HOTEL_SLUGS_LIST);
        $path_is_only_hotel = count($path_parts) == 1 && in_array($path_parts[0], HOTEL_SLUGS_LIST);

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

                header("Location: " . rtrim($complete_url, "/"), true, 301);
                exit();
            } else if($path_is_hotel_and_lang) {
                self::redirect_to_lang_hotel($path_parts, $url_path);

            } elseif ($path_is_lang_and_hotel) {
                /**
                 * Si la variable de entorno $_SERVER["REQUEST_URI"]  tiene el formato /XX/YYYY...
                 * la sobrescribimos para que WP crea que es /YYYY/XX...
                 */
                $path_prefix = "/" . $path_parts[1] . "/" . $path_parts[0];
                $_SERVER["REQUEST_URI"] = $path_prefix . substr($url_path, strlen($path_prefix)) . "/";
            }

            # Si contiene /? y el metodo es GET, se le quita esa barra
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && strpos($url_path, "/?") !== false) {
                header("Location: " .  str_replace("/?", "?", $url_path), true, 301);
                exit();
            }

            # En cualquier caso, si acaba en / y el metodo es GET
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && str_ends_with($url_path, "/")) {
                header("Location: " . rtrim($url_path, "/"), true, 301);
                exit();
            }
        }
    }


    public static function process_url_main_site()
    {
        /**
         * Si la URL tiene el idioma antes que el sitio, la dejamos como está,
         * pero sobrescribimos la variable de entorno $_SERVER["REQUEST_URI"] para que WPML la intereprete correctamente.
         *
         * Si el idioma va detrás del sitio, redireccionamos con un 301 a la URL modificada (con el idioma delante del sitio).
         *
         */
        $url_path = $_SERVER["REQUEST_URI"];
        $path_parts = explode("/",ltrim(parse_url($url_path,PHP_URL_PATH),"/"));

        $path_is_hotel_and_lang = count($path_parts) > 1 && in_array($path_parts[0], HOTEL_SLUGS_LIST) && in_array($path_parts[1], LANGUAGE_LIST);
        $path_is_lang_and_hotel = count($path_parts) > 1 && in_array($path_parts[0], LANGUAGE_LIST) && in_array($path_parts[1],HOTEL_SLUGS_LIST);

        if($path_is_hotel_and_lang) {
            self::redirect_to_lang_hotel($path_parts, $url_path);

        } elseif ($path_is_lang_and_hotel) {
            /**
             * Si la variable de entorno $_SERVER["REQUEST_URI"]  tiene el formato /XX/YYYY...
             * la sobrescribimos para que WP crea que es /YYYY/XX...
             */
            $path_prefix = "/" . $path_parts[1] . "/" . $path_parts[0];
            $_SERVER["REQUEST_URI"] = $path_prefix . substr($url_path, strlen($path_prefix)) . "/";

        }

        # En cualquier caso, si acaba en /, el metodo es GET y no estamos en wp-admin
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && $url_path != "/" && str_ends_with($url_path, "/") && ! strpos($url_path, "wp-admin")) {
            header("Location: " . rtrim($url_path, "/"), true, 301);
            exit();
        }
    }


    private static function redirect_to_lang_hotel(array $path_parts, mixed $url_path): void
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
            header("Location: " . rtrim($complete_url, "/"), true, 301);
            exit();
        }
    }

}

new ReformatUrls();
