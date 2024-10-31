<?php
/**
 * Plugin Name: QuadMenu - Total Mega Menu
 * Plugin URI: https://quadmenu.com
 * Description: Integrates QuadMenu with the Total theme.
 * Version: 1.0.2
 * Author: QuadMenu
 * Author URI: https://quadmenu.com
 * License: GPL
* License: GPLv3
 */
if (!defined('ABSPATH')) {
    die('-1');
}

if (!class_exists('QuadMenu_Total')) :

    final class QuadMenu_Total {

        function __construct() {

            // QuadMenu
            // -----------------------------------------------------------------

            add_filter('quadmenu_default_themes', array($this, 'themes'), 10);

            add_filter('quadmenu_developer_options', array($this, 'options'), 10);

            add_filter('quadmenu_default_options', array($this, 'defaults'), 999);

            add_filter('theme_mod_total_font', array($this, 'font'));

            add_filter('theme_mod_total_navbar_font', array($this, 'font'));

            add_filter('theme_mod_total_dropdown_font', array($this, 'font'));

            // Admin
            // -----------------------------------------------------------------

            add_action('wp_ajax_quadmenu_total_compiler', array($this, 'ajax'));

            add_action('admin_enqueue_scripts', array($this, 'compiler'));

            // Front
            // -----------------------------------------------------------------

            add_action('init', array($this, 'main_menu_integration'));

            add_action('wp_head', array($this, 'head'));
        }

        function font($font) {

            $font['letter-spacing'] = '1';

            return $font;
        }

        function ajax() {

            global $quadmenu;

            check_ajax_referer('quadmenu', 'nonce');

            $options = apply_filters('quadmenu_developer_options', $quadmenu);
            $options = apply_filters('quadmenu_default_options', $options);

            $variables = QuadMenu_Compiler::less_variables($options);

            wp_send_json($variables);

            wp_die();
        }

        function compiler() {

            $screen = get_current_screen();

            if (strpos($screen->base, 'wpex-panel-demo-importer') === false)
                return;

            wp_enqueue_script('quadmenu-total', plugin_dir_url(__FILE__) . 'assets/quadmenu-total.js', array(), '', true);

            quadmenu_compiler_enqueue();
        }

        function head() {

            if (!defined('TOTAL_THEME_ACTIVE'))
                return;

            global $quadmenu;
            ?>

            <style>

                #site-navigation-wrap {
                    display: block!important;
                }

                #site-header-inner {
                    padding-top: 0px;
                    padding-bottom: 0px;
                }

                .sticky-header-shrunk #site-header-inner {
                    padding-top: 0px!important;
                    padding-bottom: 0px!important;
                }

                #site-header-sticky-wrapper:not(.is-sticky) #quadmenu.quadmenu-total.one.quadmenu-is-horizontal .quadmenu-navbar-nav > li {
                    height: 90px;
                }

                #site-header-sticky-wrapper:not(.is-sticky) #quadmenu.quadmenu-total.one.quadmenu-is-horizontal .quadmenu-navbar-header .quadmenu-navbar-brand {
                    height: 90px;
                    line-height: 90px;
                }

                #quadmenu.quadmenu-total.two.quadmenu-is-horizontal .quadmenu-navbar-nav > li,
                #quadmenu.quadmenu-total.three.quadmenu-is-horizontal .quadmenu-navbar-nav > li,
                #quadmenu.quadmenu-total.four.quadmenu-is-horizontal .quadmenu-navbar-nav > li {
                    height: 50px;
                    line-height: 50px;
                }

                @media (min-width: 768px) {
                    #site-header #quadmenu .woo-menu-icon a {
                        text-align: center;
                        line-height: <?php echo esc_attr($quadmenu['total_navbar_height']); ?>px;
                        width: <?php echo esc_attr($quadmenu['total_navbar_height']); ?>px;
                    }

                    #site-header #quadmenu.quadmenu-total.two .woo-menu-icon a,
                    #site-header #quadmenu.quadmenu-total.three .woo-menu-icon a,
                    #site-header #quadmenu.quadmenu-total.four .woo-menu-icon a {
                        height: 50px;
                        line-height: 50px;
                    }

                }

            </style>

            <?php
        }

        function main_menu_integration() {

            if (!defined('TOTAL_THEME_ACTIVE'))
                return;

            if (!function_exists('is_quadmenu_location'))
                return;

            if (!is_quadmenu_location('main_menu'))
                return;

            remove_filter('wp_nav_menu_items', 'wpex_add_header_menu_cart_item');

            $hstyle = wpex_header_style();

            remove_filter('wp_nav_menu_items', 'wpex_add_search_to_menu', 11);

            if (in_array($hstyle, array('one', 'five'))) {
                remove_action('wpex_hook_header_inner', 'wpex_header_logo');
            }

            if ($hstyle !== 'six') {
                remove_action('wpex_hook_header_inner', 'wpex_mobile_menu_icons');
            }

            remove_action('wpex_hook_header_top', 'wpex_header_menu');
            remove_action('wpex_hook_header_inner', 'wpex_header_menu');
            remove_action('wpex_hook_header_bottom', 'wpex_header_menu');

            remove_action('wpex_hook_header_bottom', 'wpex_mobile_menu_navbar');
            remove_action('wpex_outer_wrap_before', 'wpex_mobile_menu_navbar');

            add_action('wpex_hook_header_top', array($this, 'main_menu_four'));
            add_action('wpex_hook_header_inner', array($this, 'main_menu_five'));
            add_action('wpex_hook_header_inner', array($this, 'main_menu_one'));
            add_action('wpex_hook_header_inner', array($this, 'main_menu_six'));
            add_action('wpex_hook_header_bottom', array($this, 'main_menu_two'));
            add_action('wpex_hook_header_bottom', array($this, 'main_menu_three'));
        }

        function main_menu_four() {

            $hstyle = wpex_header_style();

            if ($hstyle === 'four') {

                wp_nav_menu(
                        array(
                            'theme_location' => 'main_menu',
                            'layout_align' => 'center',
                            'layout_classes' => 'four',
                            //'layout_width_inner' => 1,
                            //'layout_width_inner_selector' => '.container',
                            'navbar_logo' => false
                        )
                );
            }
        }

        function main_menu_five() {

            $hstyle = wpex_header_style();

            if ($hstyle === 'five') {
                wp_nav_menu(
                        array(
                            'theme_location' => 'main_menu',
                            'layout_align' => 'center',
                            'layout_classes' => 'five',
                            //'layout_width_inner' => 1,
                            //'layout_width_inner_selector' => '.container',
                            'navbar_logo' => false
                        )
                );
            }
        }

        function main_menu_one() {

            $hstyle = wpex_header_style();

            if ($hstyle === 'one') {
                wp_nav_menu(
                        array(
                            'theme_location' => 'main_menu',
                            'layout_align' => 'right',
                            'layout_classes' => 'one',
                            //'layout_width_inner' => 1,
                            //'layout_width_inner_selector' => '.container',
                            'navbar_logo' => array(
                                'url' => wpex_header_logo_img()
                            )
                        )
                );
            }
        }

        function main_menu_two() {

            $hstyle = wpex_header_style();

            if ($hstyle === 'two') {
                ?>
                <div id="site-navigation-wrap" class="<?php echo wpex_header_menu_classes('wrapper'); ?>">
                    <?php
                    wp_nav_menu(
                            array(
                                'theme_location' => 'main_menu',
                                'layout_align' => 'left',
                                'layout_divider' => 'hide',
                                'layout_classes' => 'two',
                                //'layout_width_inner' => 1,
                                //'layout_width_inner_selector' => '.container',
                                'navbar_logo' => false
                            )
                    );
                    ?>
                </div>
                <?php
            }
        }

        function main_menu_three() {

            $hstyle = wpex_header_style();

            if ($hstyle === 'three') {
                ?>
                <div id="site-navigation-wrap" class="<?php echo wpex_header_menu_classes('wrapper'); ?>">
                    <?php
                    wp_nav_menu(
                            array(
                                'theme_location' => 'main_menu',
                                'layout_align' => 'center',
                                'layout_divider' => 'hide',
                                'layout_classes' => 'three',
                                //'layout_width_inner' => 1,
                                //'layout_width_inner_selector' => '.container',
                                'navbar_logo' => false
                            )
                    );
                    ?>
                </div>
                <?php
            }
        }

        function main_menu_six() {

            $hstyle = wpex_header_style();

            if ($hstyle === 'six') {
                wp_nav_menu(
                        array(
                            'theme_location' => 'main_menu',
                            'layout' => 'inherit',
                            'layout_classes' => 'six',
                            'navbar_logo' => false
                        )
                );
            }
        }

        function themes($themes) {

            $themes['total'] = 'Total Theme';

            return $themes;
        }

        function options($options) {

            if (!defined('TOTAL_THEME_ACTIVE'))
                return $options;

            // Custom
            // -----------------------------------------------------------------

            $options['viewport'] = 0;

            // Main Menu
            // -----------------------------------------------------------------
            //$options['main_menu_integration'] = 1;

            $options['main_menu_unwrap'] = 0;

            $options['main_menu_theme'] = 'total';

            // Theme
            // -----------------------------------------------------------------

            $options['total_theme_title'] = 'Total Theme';

            $options['total_layout'] = 'collapse';

            $options['total_layout_breakpoint'] = 980;

            $options['total_layout_width'] = 0;

            $options['total_layout_width_selector'] = '';

            // Sticky
            // -----------------------------------------------------------------

            $options['total_layout_sticky_divider'] = '';

            $options['total_layout_sticky'] = 0;

            $options['total_layout_sticky_offset'] = 0;

            $options['total_layout_hover_effect'] = null;

            $options['total_layout_align'] = 'center';

            $options['total_layout_divider'] = 'hide';

            $options['total_layout_width_inner'] = 0;

            $options['total_layout_width_inner_selector'] = '.container';

            $options['total_mobile_shadow'] = 'hide';

            // Navbar
            // -----------------------------------------------------------------

            $options['total_navbar'] = '';

            $options['total_navbar_height'] = '58';

            $options['total_navbar_width'] = '260';

            /* $options['total_navbar_logo'] = array(
              'url' => wpex_header_logo_img(),
              'id' => '',
              'height' => '',
              'width' => '',
              'thumbnail' => '',
              'title' => '',
              'caption' => '',
              'alt' => '',
              'description' => '',
              ); */

            //$options['total_navbar_logo_height'] = '38';
            // Sticky
            // -----------------------------------------------------------------
            $options['total_sticky_background'] = 'transparent';
            $options['total_sticky_height'] = '60';
            $options['total_sticky_logo_height'] = '25';

            // Typography
            // -----------------------------------------------------------------

            $defaults = array(
                'font-family' => 'Open Sans',
                'font-size' => '13',
                'font-style' => 'normal',
                'font-weight' => '400',
                'letter-spacing' => 'inherit',
            );

            $options['total_font'] = wp_parse_args(array_filter((array) wpex_get_mod('body_typography')), $defaults);

            $options['total_navbar_font'] = wp_parse_args(array_filter((array) wpex_get_mod('menu_typography')), $defaults);

            $options['total_dropdown_font'] = wp_parse_args(array_filter((array) wpex_get_mod('menu_dropdown_typography')), $defaults);

            return $options;
        }

        function defaults($defaults) {

            if (!defined('TOTAL_THEME_ACTIVE'))
                return $defaults;

            $accent = wpex_get_mod('accent_color') ? wpex_get_mod('accent_color') : '#3b86b0';

            $color = wpex_get_mod('menu_link_color') ? wpex_get_mod('menu_link_color') : '#555555';

            $color_hover = wpex_get_mod('menu_link_color_hover') ? wpex_get_mod('menu_link_color_hover') : $accent;

            $background = wpex_get_mod('t_background_color') ? wpex_get_mod('t_background_color') : '#ffffff';

            $background_badge = $color_hover !== $background ? $color_hover : $accent;

            $background_navbar = wpex_get_mod('menu_background') ? wpex_get_mod('menu_background') : $background;

            $background_dropdown = wpex_get_mod('dropdown_menu_background') ? wpex_get_mod('dropdown_menu_background') : $background_navbar;

            $dropdown_link = wpex_get_mod('dropdown_menu_link_color') ? wpex_get_mod('dropdown_menu_link_color') : $color !== $background_dropdown ? $color : '#555555';
            $dropdown_link_hover = wpex_get_mod('dropdown_menu_link_color_hover') ? wpex_get_mod('dropdown_menu_link_color_hover') : $color_hover;
            $dropdown_link_background = wpex_get_mod('dropdown_menu_link_hover_bg') ? wpex_get_mod('dropdown_menu_link_hover_bg') : 'transparent';

            $heading = wpex_get_mod('headings_typography_color') ? wpex_get_mod('headings_typography_color') : '#000000';

            $logo = wpex_header_logo_img() ? wpex_header_logo_img() : QUADMENU_URL . 'assets/frontend/images/logo.png';

            $defaults['total_layout'] = 'collapse';
            $defaults['total_layout_offcanvas_float'] = 'right';
            $defaults['total_layout_align'] = 'right';
            $defaults['total_layout_breakpoint'] = '';
            $defaults['total_layout_width'] = '0';
            $defaults['total_layout_width_selector'] = '';
            $defaults['total_layout_trigger'] = 'hoverintent';
            $defaults['total_layout_current'] = '';
            $defaults['total_layout_animation'] = 'quadmenu_btt';
            $defaults['total_layout_classes'] = '';
            $defaults['total_layout_sticky'] = '0';
            $defaults['total_layout_sticky_offset'] = '90';
            $defaults['total_layout_totalder'] = 'hide';
            $defaults['total_layout_caret'] = 'show';
            $defaults['total_layout_hover_effect'] = '';

            // Navbar
            // -----------------------------------------------------------------

            $defaults['total_navbar_background'] = 'color';
            $defaults['total_navbar_background_color'] = 'transparent';
            $defaults['total_navbar_background_to'] = 'transparent';
            $defaults['total_navbar_background_deg'] = '17';
            $defaults['total_navbar_totalder'] = 'transparent';
            $defaults['total_navbar_text'] = $color;
            $defaults['total_navbar_height'] = '90';
            $defaults['total_navbar_width'] = '260';
            $defaults['total_navbar_logo_height'] = '38';
            $defaults['total_navbar_logo_bg'] = 'transparent';
            $defaults['total_navbar_logo'] = array(
                'url' => $logo,
                'id' => '',
                'height' => '',
                'width' => '',
                'thumbnail' => '',
                'title' => '',
                'caption' => '',
                'alt' => '',
                'description' => '',
            );
            $defaults['total_navbar_link_margin'] = array(
                'border-top' => '0px',
                'border-right' => '0px',
                'border-bottom' => '0px',
                'border-left' => '0px',
                'border-style' => '',
                'border-color' => '',
            );
            $defaults['total_navbar_link_radius'] = array(
                'border-top' => '0px',
                'border-right' => '0px',
                'border-bottom' => '0px',
                'border-left' => '0px',
                'border-style' => '',
                'border-color' => '',
            );
            $defaults['total_navbar_link_transform'] = 'none';
            $defaults['total_navbar_link'] = $color;
            $defaults['total_navbar_link_hover'] = $color_hover;
            $defaults['total_navbar_link_bg'] = 'transparent';
            $defaults['total_navbar_link_bg_hover'] = 'transparent';
            $defaults['total_navbar_link_hover_effect'] = $color_hover;
            $defaults['total_navbar_link_icon'] = $background_badge;
            $defaults['total_navbar_link_icon_hover'] = $background_navbar != $background ? $background : $background_badge;
            $defaults['total_navbar_link_subtitle'] = $color_hover;
            $defaults['total_navbar_link_subtitle_hover'] = $color_hover;

            $defaults['total_navbar_badge'] = $background_badge;
            $defaults['total_navbar_badge_color'] = $background;

            $defaults['total_navbar_button'] = $defaults['total_navbar_badge_color'];
            $defaults['total_navbar_button_bg'] = $defaults['total_navbar_badge'];
            $defaults['total_navbar_button_hover'] = $defaults['total_navbar_button'];
            $defaults['total_navbar_button_bg_hover'] = $color_hover;

            $defaults['total_navbar_scrollbar'] = $color_hover;
            $defaults['total_navbar_scrollbar_rail'] = $background;

            // Mobile
            // -----------------------------------------------------------------   
            $defaults['total_navbar_mobile_border'] = 'transparent';
            $defaults['total_navbar_toggle_open'] = $color;
            $defaults['total_navbar_toggle_close'] = $color_hover;
            $defaults['total_navbar_logo'] = array(
                'url' => wpex_header_logo_img(),
                'id' => '',
                'height' => '',
                'width' => '',
                'thumbnail' => '',
                'title' => '',
                'caption' => '',
                'alt' => '',
                'description' => '',
            );

            // Sticky
            // -----------------------------------------------------------------
            $defaults['total_sticky_background'] = 'transparent';
            $defaults['total_sticky_height'] = '60';
            $defaults['total_sticky_logo_height'] = '25';

            // Dropdown
            // -----------------------------------------------------------------
            $defaults['total_dropdown_shadow'] = 'hide';
            $defaults['total_dropdown_margin'] = '0';
            $defaults['total_dropdown_radius'] = array('border-top' => '0', 'border-right' => '0', 'border-left' => '0', 'border-bottom' => '0');
            $defaults['total_dropdown_border'] = array(
                'border-top' => '3px',
                'border-right' => '',
                'border-bottom' => '',
                'border-left' => '',
                'border-style' => 'solid',
                'border-color' => wpex_get_mod('menu_dropdown_top_border_color') ? wpex_get_mod('menu_dropdown_top_border_color') : $background_badge,
            );
            $defaults['total_dropdown_background'] = $background_dropdown;
            $defaults['total_dropdown_scrollbar'] = $color_hover;
            $defaults['total_dropdown_scrollbar_rail'] = '#ffffff';
            $defaults['total_dropdown_title'] = $heading;
            $defaults['total_dropdown_title_border'] = array(
                'border-top' => '1px',
                'border-right' => '',
                'border-bottom' => '',
                'border-left' => '',
                'border-style' => 'solid',
                'border-color' => $color_hover
            );
            $defaults['total_dropdown_link'] = $dropdown_link;
            $defaults['total_dropdown_link_hover'] = $dropdown_link_hover;
            $defaults['total_dropdown_link_bg_hover'] = $dropdown_link_background;
            $defaults['total_dropdown_link_border'] = array(
                'border-top' => '0px',
                'border-right' => '0px',
                'border-bottom' => '0px',
                'border-left' => '0px',
                'border-style' => 'none',
                'border-color' => '#f4f4f4',
            );
            $defaults['total_dropdown_link_transform'] = 'none';
            $defaults['total_dropdown_button'] = $defaults['total_navbar_badge_color'];
            $defaults['total_dropdown_button_bg'] = $defaults['total_navbar_badge'];
            $defaults['total_dropdown_button_hover'] = $defaults['total_navbar_button'];
            $defaults['total_dropdown_button_bg_hover'] = $color_hover;
            $defaults['total_dropdown_link_icon'] = $color_hover;
            $defaults['total_dropdown_link_icon_hover'] = $color_hover != $background_dropdown ? $color_hover : $background;
            $defaults['total_dropdown_link_subtitle'] = $dropdown_link;
            $defaults['total_dropdown_link_subtitle_hover'] = $dropdown_link;

            // Location
            // -----------------------------------------------------------------

            $defaults['main_menu_integration'] = 1;
            $defaults['main_menu_theme'] = 'total';

            return $defaults;
        }

        static function activation() {

            update_option('_quadmenu_compiler', true);

            if (class_exists('QuadMenu')) {

                QuadMenu_Redux::add_notification('blue', esc_html__('Thanks for install QuadMenu Total. We have to create the stylesheets. Please wait.', 'quadmenu'));

                QuadMenu_Activation::activation();
            }
        }

    }

    new QuadMenu_Total();

    register_activation_hook(__FILE__, array('QuadMenu_Total', 'activation'));

    endif; // End if class_exists check
