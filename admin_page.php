<?php

class AdminPage
{
    function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu_page']);
    }

    function add_admin_menu_page()
    {
        //add_menu_page(
        add_submenu_page(
            'options-general.php',
            __('WP Lang URLs', 'wp_lang_urls'),
            __('WP Lang URLs', 'wp_lang_urls'),
            'manage_options',
            'wp-lang-url-config',
            [$this, 'form_page'],
            'dashicons-media-text',
            100
        );
    }

    function form_page()
    {
        $this->save_data();
        $this->show_form();
    }

    function show_form()
    {

        $slugs_default = implode("\n",  WP_LANG_URL_HARDCODED_CONFIG["slugs"]);
        $langs_default = implode("\n", WP_LANG_URL_HARDCODED_CONFIG["langs"]);
        $ignore_urls_default = implode("\n", WP_LANG_URL_HARDCODED_CONFIG["ignore_urls"]);
        $login_directory_default = WP_LANG_URL_HARDCODED_CONFIG["login_directory"];
        $permalink_structure_default = WP_LANG_URL_HARDCODED_CONFIG["permalink_structure"];

        $slugs_env = implode("\n",  WP_LANG_URL_ENV_CONFIG["slugs"]);
        $langs_env = implode("\n", WP_LANG_URL_ENV_CONFIG["langs"]);
        $ignore_urls_env = implode("\n", WP_LANG_URL_ENV_CONFIG["ignore_urls"]);
        $login_directory_env = WP_LANG_URL_ENV_CONFIG["login_directory"];
        $permalink_structure_env = WP_LANG_URL_ENV_CONFIG["permalink_structure"];

        # Estos valores son ANTES DE GUARDAR el formulario
        /*
        $slugs_json = implode("\n",  WP_LANG_URL_JSON_CONFIG["slugs"]);
        $langs_json = implode("\n", WP_LANG_URL_JSON_CONFIG["langs"]);
        $ignore_urls_json = implode("\n", WP_LANG_URL_JSON_CONFIG["ignore_urls"]);
        $login_directory_json = WP_LANG_URL_JSON_CONFIG["login_directory"];
        $permalink_structure_json = WP_LANG_URL_JSON_CONFIG["permalink_structure"];
        */

        $json_data =  wp_lang_urls_get_data_from_json();
        $end_config_values = wp_lang_urls_get_config_values($json_data);

        $slugs_end = implode("\n",  $end_config_values["slugs"]);
        $langs_end = implode("\n", $end_config_values["langs"]);
        $ignore_urls_end = implode("\n", $end_config_values["ignore_urls"]);
        $login_directory_end = $end_config_values["login_directory"];
        $permalink_structure_end = $end_config_values["permalink_structure"];

        $slugs_value = (isset($json_data['slugs'])) ? implode("\n", $json_data['slugs']) : "";
        $langs_value = (isset($json_data['langs'])) ? implode("\n", $json_data['langs']) : "";
        $ignore_urls_value = (isset($json_data['ignore_urls'])) ? implode("\n", $json_data['ignore_urls']) : "";
        $login_directory_value = (isset($json_data['login_directory'])) ? $json_data['login_directory'] : "";
        $permalink_structure_value = (isset($json_data['permalink_structure'])) ? $json_data['permalink_structure'] : "";

        if ($this->is_wps_hideLogin_active()) {
            $whl_page = (get_option('whl_page')) ? "<br><small>(" . __("must be", 'wp_lang_urls') . " " . get_option('whl_page') . ")</small>" : "";
        } else {
            $whl_page = "<br><small>(" . __("WPS Hide Login plugin not active", 'wp_lang_urls') . ")</small>";
        }
        ?>

        <div class="wrap">
            <h1><?php _e('WP Lang URLs configuration', 'wp_lang_urls'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('wp_lang_urls_plugin_form'); ?>
                <input type="hidden" name="wp_lang_urls_plugin_configuration"
                       value="wp_lang_urls_plugin_configuration"/>
                <table class="form-table">
                    <tr>
                        <th></th>
                        <th>JSON Values<br><small>Stored in the file system. Do not use on multi-instance systems.</small></th>
                        <th>Environment Values</th>
                        <th>Default Values</th>
                        <th>Applied Values</th>
                    </tr>
                    <tr>
                        <th scope="row"><label for="slugs"><?php _e('Slugs:', 'wp_lang_urls'); ?></label></th>
                        <td><textarea id="slugs" name="slugs" rows="5" cols="33"><?php echo $slugs_value; ?></textarea>
                        </td>
                        <td><small><em>WP_LANG_SITE_SLUGS_LIST</em> (string, separated by commas)</small><br><textarea readonly id="slugs_en" name="slugs_en" rows="5"
                                      cols="33"><?php echo $slugs_env; ?></textarea></td>
                        <td><textarea readonly id="slugs_ro" name="slugs_ro" rows="5"
                                      cols="33"><?php echo $slugs_default; ?></textarea></td>
                        <td><textarea class="value_in_use" readonly id="slugs_f" name="slugs_f" rows="5"
                                      cols="33"><?php echo $slugs_end; ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="langs"><?php _e('Langs:', 'wp_lang_urls'); ?></label></th>
                        <td><textarea id="langs" name="langs" rows="5" cols="33"><?php echo $langs_value; ?></textarea>
                        </td>
                        <td><small><em>WP_LANG_LANGUAGE_LIST</em> (string, separated by commas)</small><br><textarea readonly id="langs_en" name="langs_en" rows="5"
                                      cols="33"><?php echo $langs_env; ?></textarea></td>
                        <td><textarea readonly id="langs_ro" name="langs_ro" rows="5"
                                      cols="33"><?php echo $langs_default; ?></textarea></td>
                        <td><textarea class="value_in_use" readonly id="langs_f" name="langs_f" rows="5"
                                      cols="33"><?php echo $langs_end; ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ignore_urls"><?php _e('Ignore URLs:', 'wp_lang_urls'); ?></label><br>
                            <small><?php _e('(currently not in use)', 'wp_lang_urls'); ?></small>
                        </th>
                        <td><textarea id="ignore_urls" name="ignore_urls" rows="5"
                                      cols="33"><?php echo $ignore_urls_value; ?></textarea></td>
                        <td><small><em>WP_LANG_URL_IGNORE_LIST</em> (string, separated by commas)</small><br><textarea readonly id="ignore_urls_en" name="ignore_urls_en" rows="5"
                                      cols="33"><?php echo $ignore_urls_env; ?></textarea></td>
                        <td><textarea readonly id="ignore_urls_ro" name="ignore_urls_ro" rows="5"
                                      cols="33"><?php echo $ignore_urls_default; ?></textarea></td>
                        <td><textarea class="value_in_use" readonly id="ignore_urls_e" name="ignore_urls_e" rows="5"
                                      cols="33"><?php echo $ignore_urls_end; ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label
                                    for="login_directory"><?php _e('Login directory:', 'wp_lang_urls'); ?><?php echo $whl_page; ?></label>
                        </th>
                        <td><input type="text" id="login_directory"
                                   name="login_directory"
                                   value="<?php echo esc_attr($login_directory_value); ?>" class="regular-text"/></td>
                        <td><small><em>WP_LANG_LOGIN_DIRECTORY</em> (string)</small><br><input type="text" id="login_directory_en"
                                   name="login_directory_en"
                                   readonly
                                   value="<?php echo esc_attr($login_directory_env); ?>" class="regular-text"/></td>
                        <td><input type="text" id="login_directory_ro"
                                   name="login_directory_ro"
                                   readonly
                                   value="<?php echo esc_attr($login_directory_default); ?>" class="regular-text"/></td>
                        <td><input type="text" id="login_directory_e"
                                   name="login_directory_e"
                                   class="value_in_use"
                                   readonly
                                   value="<?php echo esc_attr($login_directory_end); ?>" class="regular-text"/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label
                                    for="permalink_structure"><?php _e('Permalink structure:', 'wp_lang_urls'); ?></label><br>
                            <small><?php _e('(must end with /)', 'wp_lang_urls'); ?></small>
                        </th>
                        <td><input type="text" id="permalink_structure"
                                   name="permalink_structure"
                                   value="<?php echo esc_attr($permalink_structure_value); ?>" class="regular-text"/></td>
                        <td><small><em>WP_LANG_PERMALINK_STRUCTURE</em> (string)</small><br><input type="text" id="permalink_structure_en"
                                   name="permalink_structure_en"
                                   readonly
                                   value="<?php echo esc_attr($permalink_structure_env); ?>" class="regular-text"/></td>
                        <td><input type="text" id="permalink_structure_ro"
                                   name="permalink_structure_ro"
                                   readonly
                                   value="<?php echo esc_attr($permalink_structure_default); ?>" class="regular-text"/></td>
                        <td><input type="text" id="permalink_structure_e"
                                   name="permalink_structure_e"
                                   class="value_in_use"
                                   readonly
                                   value="<?php echo esc_attr($permalink_structure_end); ?>" class="regular-text"/></td>
                    </tr>
                </table>
                <?php submit_button(__('Save Changes', 'wp_lang_urls')); ?>
            </form>
        </div>
        <?php
    }

    function get_data_from_json()
    {
        if (file_exists(WP_LANG_URL_JSON_FILE_PATH)) {
            $json_file_object = file_get_contents(WP_LANG_URL_JSON_FILE_PATH);
            return json_decode($json_file_object, true);
        }
        return [];
    }

    function is_wps_hideLogin_active()
    {
        return defined("WPS_HIDE_LOGIN_VERSION");
    }

    // Guardar el texto en un archivo JSON
    function save_data()
    {

        if (!file_exists(WP_LANG_URL_JSON_FILE_PATH) && !is_writable(WP_LANG_URL_JSON_DIR_PATH)) {
            echo '<div class="error"><p>' . __('Data directory not writable. The JSON file will not be created.', 'wp_lang_urls') . '</p></div>';
            return;
        }

        if (file_exists(WP_LANG_URL_JSON_FILE_PATH) && !is_writable(WP_LANG_URL_JSON_FILE_PATH)) {
            echo '<div class="error"><p>' . __('JSON file not writable.', 'wp_lang_urls') . '</p></div>';
            return;
        }

        if (isset($_POST['wp_lang_urls_plugin_configuration'])) {

            $slugs = self::filter_array_input($_POST['slugs']);
            $langs = self::filter_array_input($_POST['langs']);
            $ignore_urls = self::filter_array_input($_POST['ignore_urls']);
            $login_directory = self::filter_input($_POST['login_directory']);
            $permalink_structure = ($_POST['permalink_structure']? rtrim(self::filter_input($_POST['permalink_structure']), "/") . "/" : "");

            $data = [
                'slugs' => $slugs,
                'langs' => $langs,
                'ignore_urls' => $ignore_urls,
                'login_directory' => $login_directory,
                'permalink_structure' => $permalink_structure,
                'timestamp' => time()
            ];

            $result = file_put_contents(WP_LANG_URL_JSON_FILE_PATH, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if ($result === false) {
                echo '<div class="error"><p>' . __('Error saving data', 'wp_lang_urls') . '</p></div>';
            } else {
                echo '<div class="updated"><p>' . __('Data updated', 'wp_lang_urls') . '</p></div>';
            }
        }
    }

    static function filter_array_input($input){
        $input = str_replace("\r\n", "\n", $input);
        $clean_array = array_filter(explode("\n", $input), function($value) {
            return !is_null($value) && $value !== '';
        });
        return $clean_array;
    }

    static function filter_input($input)
    {
        $input = str_replace("\r\n", "\n", $input);
        return $input;
        //return htmlspecialchars($input, ENT_QUOTES);
    }

}

new AdminPage();
