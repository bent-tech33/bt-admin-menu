<?php

/**
 * Plugin Name: Admin Menu Scroll
 * Version: 1.0
 * Plugin URI: https://codecanyon.net/user/bent-tech33
 * Description: Scrollable Admin Menu
 * Author: Bent Tech
 * Author URI: https://codecanyon.net/user/bent-tech33
 * Text Domain: bt-admin-menu
 * Domain Path: /languages/
 * License: GPL v2
 */
if (is_admin()) {
    DEFINE('BT_ADMIN_MENU_PATH', plugin_dir_path(__FILE__));
    DEFINE('BT_ADMIN_MENU_URL', plugin_dir_url(__FILE__));
    DEFINE('BT_ADMIN_MENU_SLUG', 'bt-menu-scroll-settings');
    DEFINE('BT_ADMIN_MENU_OPTIONS_KEY', 'bt_admin_menu_settings');

    add_filter('admin_init', ['BT_Admin_Menu', 'bt_register_settings']);

    add_action('admin_menu', ['BT_Admin_Menu', 'bt_menu_page']);
    add_action('admin_enqueue_scripts', ['BT_Admin_Menu', 'bt_menu_add_css'], 9999);
    add_filter('menu_order', ['BT_Admin_Menu', 'bt_menu_order'], 9999);
    add_filter('admin_init', ['BT_Admin_Menu', 'bt_register_settings']);

    class BT_Admin_Menu {

        static function bt_menu_add_css() {
            $key = 'bt-admin-menu';
            $time = filemtime(BT_ADMIN_MENU_PATH . '/css/' . $key . '.css');
            wp_enqueue_style(
                    'bt-admin-menu',
                    BT_ADMIN_MENU_URL . 'css/' . $key . '.css', [], $time
            );
            $color = get_theme_mod('my-custom-color', 'blue'); //E.g. #FF0000
            $custom_css = "
                .mycolor{
                        background: {$color};
                }";
            wp_add_inline_style($key, $custom_css);

            $time2 = filemtime(BT_ADMIN_MENU_PATH . '/js/' . $key . '.js');
            wp_enqueue_script(
                    'bt-admin-menu',
                    BT_ADMIN_MENU_URL . 'js/' . $key . '.js', ['jquery'], $time2
            );
        }

        static function bt_menu_order($menu_ord) {

            if (!$menu_ord) {
                return true;
            }
            $options = get_option(BT_ADMIN_MENU_OPTIONS_KEY, []);
            $sticky = isset($options['sticky_menus']) ? $options['sticky_menus'] : 1;
            if (!$sticky) {
                return [];
            }
            if ($sticky) {
                $array = array(
                    'index.php', // this represents the dashboard link
                    'edit.php?post_type=page', // this is the default Page menu
                    'edit.php', // this is the default POST admin menu 
                    'upload.php',
                    'plugins.php',
                    'options-general.php',
                );
                if (is_plugin_active('woocommerce/woocommerce.php')) {
                    $array[] = 'wc-admin&path=/wc-pay-welcome-page';
                    $array[] = 'edit.php?post_type=product';
                    $array[] = 'woocommerce';
                }
                if (is_plugin_active('elementor/elementor.php')) {
                    $array[] = 'elementor';
                    $array[] = 'elementor-pro';
                }
                return $array;
            }
        }

        static function bt_menu_settings() {
            include_once BT_ADMIN_MENU_PATH . '/settings/settings.php';
        }

        static function bt_menu_page() {
            add_submenu_page(
                    'options-general.php',
                    __('WP Menu Scroll', 'bt-admin-menu'),
                    __('WP Menu Scroll', 'bt-admin-menu'),
                    'manage_options',
                    BT_ADMIN_MENU_SLUG,
                    ['BT_Admin_Menu', 'bt_menu_settings'],
                    plugins_url('myplugin/images/icon.png'),
            );
        }

        static function bt_fields($fieldName) {
            $options = get_option(BT_ADMIN_MENU_OPTIONS_KEY, []);

            if ($fieldName === 'sticky_menus') {

                $sticky = isset($options[$fieldName]) ? $options[$fieldName] : 1;

                echo '<div>' .
                '<input type="radio" name="' . BT_ADMIN_MENU_OPTIONS_KEY . '[' . $fieldName . ']" value="1" ' . ($sticky ? 'checked' : '') . ' />' .
                '<label>Yes</label>' .
                '</div>' .
                '<div>' .
                '<input type="radio" name="' . BT_ADMIN_MENU_OPTIONS_KEY . '[' . $fieldName . ']" value="0"  ' . ($sticky ? '' : 'checked') . ' />' .
                '<label>No</label>' .
                '</div>';
            }
        }

        static function bt_settings_section_text() {
            echo '';
        }

        static function bt_register_settings() {
            register_setting(BT_ADMIN_MENU_OPTIONS_KEY, BT_ADMIN_MENU_OPTIONS_KEY);

            add_settings_section(
                    'bt_settings_section', //$id 
                    '', //$title
                    ['BT_Admin_Menu', 'bt_settings_section_text'], //Function that echos out any content at the top of the section (between heading and fields).
                    BT_ADMIN_MENU_SLUG //The slug-name of the settings page on which to show the section
            );

            add_settings_field(
                    'sticky_menus', //field id
                    __('Sticky fields <br><small>Sticks important menus to the top</small>', 'bt-admin-menu'),
                    ['BT_Admin_Menu', 'bt_fields'],
                    BT_ADMIN_MENU_SLUG,
                    'bt_settings_section',
                    'sticky_menus', //field id as an arguement
            );
        }

    }

}

