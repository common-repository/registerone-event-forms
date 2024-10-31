<?php
/*
Plugin Name: RegisterONE Enterprise Event Management & Ticketing
Plugin URI: https://www.registerone.com
Description: RegisterONE is a unified, enterprise level event management, registration and ticketing platform.
Version: 3.7.6
Author: RegisterONE
Author URI: http://www.registerone.com
License: GPL v2
*/

/* exit if accessed directly*/
if (!defined('ABSPATH')) exit;

if(!class_exists('RegisteroneWordpressClient')) {

    define( 'WORDPRESS_ROOT_PATH', site_url());
    define( 'REGISTERONE_WORDPRESS_CLIENT_PATH',     dirname( __FILE__ ) );
    define( 'REGISTERONE_WORDPRESS_CLIENT_URL',      plugins_url( '', __FILE__ ) );
    define( 'REGISTERONE_WORDPRESS_CLIENT_FILE',     plugin_basename( __FILE__ ) );
    define( 'REGISTERONE_WORDPRESS_CLIENT_ASSETS', REGISTERONE_WORDPRESS_CLIENT_URL . '/assets' );
    define( 'REGISTERONE_WORDPRESS_CLIENT_FRONTEND', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end');

    require_once(REGISTERONE_WORDPRESS_CLIENT_PATH . '/template.class.php');

    class RegisteroneWordpressClient {

        public $error_text = '';
        public $short_code = 'registerone_form';
        public $iframe_short_code = 'registerone_iframe';
        public $registerone_form_shortcode;

        private $r1_theme_name = 'registerone-theme-override';
        private $plugin_root;
        private $plugin_url;

        /**
         * A Unique Identifier
         */
        protected $plugin_slug;

        /**
         * A reference to an instance of this class.
         */
        private static $instance;

        /**
         * The array of templates that this plugin tracks.
         */
        protected $templates;

        public function __construct()
        {
            global $post;

            $this->templates = array();

            spl_autoload_register( array( $this, 'autoload' ) );
            $registeroneSettings = new RegisteroneMain();

            add_action('admin_enqueue_scripts', array($this, 'dwc_admin_styles'));
            add_action('wp_enqueue_scripts', array($this, 'dwc_frontend_scripts'));

            $raw_output = false;

            if(!$raw_output) {
                // removes theme styles and scripts before displaying form
                // also disables emoji in header
                add_action('wp_enqueue_scripts', array($this, 'dwc_remove_scripts_and_styles'), 100);
            }

            add_action('wp_head', array($this, 'dwc_inject_scripts_and_styles'), 100);

            if(!$raw_output) {
                ///////////////////////////////////////
                // theme switch setup
                $this->plugin_root = plugin_dir_path(__FILE__);
                $this->plugin_url = plugins_url('', dirname(__FILE__)); // note: '' appends a slash to the url
                add_action('wp_enqueue_scripts', array($this, 'switch_blank_theme'), 100);
                add_action('plugins_loaded', array('RegisterOnePageTemplate', 'get_instance'));
            }

            $this->registerone_form_shortcode = new RegisteroneShortcodes();

            register_activation_hook(__FILE__, array($this, 'dwc_plugin_activation'));
            register_deactivation_hook(__FILE__, array($this, 'dwc_plugin_deactivation'));
        }

        /**
         * @param $key
         * @return bool
         */
        private function _check_screen_key($key)
        {
            return array_key_exists($key, $_GET);
        }

        public function switch_blank_theme() {
            global $post;

            if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $this->short_code)) {
                add_filter('theme_root', array($this, 'switch_theme_root_path'));

                add_filter('template_directory_uri', array($this, 'switch_template_directory_uri'));

                add_filter('stylesheet_uri', array($this, 'switch_template_directory_uri'));
                // stylesheet_uri used by wp-includes/theme.php: get_stylesheet_uri()

                add_filter('pre_option_stylesheet', function () {
                    // return $this->r1_theme_name.'_fixes_css_error';
                    return $this->r1_theme_name;
                });
                add_filter('pre_option_template', function () {
                    // return $this->r1_theme_name.'_fixes_css_error';
                    return $this->r1_theme_name;
                });
            };
        }

        public function switch_theme_root_path( $org_theme_root ) {
            $current_theme = wp_get_theme( $this->r1_theme_name );
            // if theme exists, no point in changing theme root.
            if ( $current_theme->exists() ) {
                return $org_theme_root;
            }
            $new_theme_root = $this->plugin_root  . 'src/assets';
            # Too early to use register_theme_directory()
            if ( ! in_array( $new_theme_root, $GLOBALS['wp_theme_directories'] ) ) {
                $GLOBALS['wp_theme_directories'][] = $new_theme_root;
            }
            return $new_theme_root;
        }

        public function switch_template_directory_uri( $template_dir_uri  ) {
            $new_theme_root_uri = $this->plugin_url . '/registerone-event-forms/src/assets/' . $this->r1_theme_name;
            // die($new_theme_root_uri);
            // https://forms.registerone.net/wp-content/plugins/registerone-event-forms/src/assets/registerone-theme-override
            wp_register_style( 'theme-styles', $new_theme_root_uri . '/site.css');

            return $new_theme_root_uri;
        }

        /**
         *
         */
        public function dwc_admin_styles(){
            wp_register_script('dwc admin js', REGISTERONE_WORDPRESS_CLIENT_URL . '/admin/assets/js/regone-admin.js', array('jquery'), '201610201101');
            wp_enqueue_script('dwc admin js');

            wp_localize_script('dwc admin js', 'dwc_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );

            wp_register_style('dwc admin', REGISTERONE_WORDPRESS_CLIENT_URL . '/admin/assets/css/regone-admin-styles.css' );
            wp_enqueue_style('dwc admin');
        }

        public function dwc_remove_scripts_and_styles() {
            global $wp_scripts;
            global $wp_styles;
            global $post;
            global $wp_query;

            if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $this->short_code)){

                // Runs through the queue scripts
                foreach ($wp_scripts->queue as $handle) :
                    if (!is_numeric(strpos($handle, 'dwc'))) {
                        wp_dequeue_script($handle);
                        wp_deregister_script($handle);
                    }
                endforeach;

                // Runs through the queue styles
                foreach ($wp_styles->queue as $handle) :
                    if (!is_numeric(strpos($handle, 'dwc'))) {
                        wp_dequeue_style($handle);
                        wp_deregister_style($handle);
                    }
                endforeach;

                // disable emojis
                remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
                remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
                remove_action( 'wp_print_styles', 'print_emoji_styles' );
                remove_action( 'admin_print_styles', 'print_emoji_styles' );
            }
        }

        public function dwc_inject_scripts_and_styles() {
            global $wp_scripts;
            global $wp_styles;
            global $post;
            global $wp_query;
            global $attr_tmp;

            if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $this->short_code)){

                if(isset($_SERVER['SERVER_ADDR'])) {
                    $ip_addr = explode(".", $_SERVER['SERVER_ADDR']);
                    if ($ip_addr[0] == '192' && $ip_addr[1] == '168') {
                        $this->base_request_url = "http://api.registerone.net/v1/apply/";
                        $this->base_site_url = "https://app.registerone.net";
                    } else {
                        $this->base_request_url = "https://api.registerone.com/v1/apply/";
                        $this->base_site_url = "https://app.registerone.com";
                    }
                }else{
                    $this->base_request_url = "https://api.registerone.com/v1/apply/";
                    $this->base_site_url = "https://app.registerone.com";
                }

                $this->plugin_api_auth_key = isset($this->plugin_api_auth_key) ? $this->plugin_api_auth_key : '';

                $headers = $this->_set_request_header(['Accept' => "application/json, text/javascript, */*; q=0.01", 'Authorization' => 'Basic ' . $this->plugin_api_auth_key]);
                $request_args = $this->_get_request_args([
                        'method' => 'GET',
                        'headers' => $headers
                    ]
                );

                if ( have_posts() ) : while ( have_posts() ) : the_post();
                    preg_match('/\[registerone_form form_key="(.*?)"\]/i', $post->post_content, $matches);
                endwhile; else:
                    $matches[1] = '';
                endif;

                $form_key = isset($wp_query->query_vars['form_key']) ? $wp_query->query_vars['form_key'] : $matches[1];

                $request_url_event_attributes = $this->base_request_url . $form_key . "/style";

                $this->event_attributes = wp_remote_retrieve_body(wp_remote_request($request_url_event_attributes, $request_args));

                $attr_tmp = json_decode($this->event_attributes, true);

                $custom_css_master = '';

                if ($attr_tmp['bg_img_url'] != null) {
                    $custom_css = "body{ background-image: url('{$attr_tmp['bg_img_url']}'); background-repeat: repeat; }";

                    $custom_css_master .= $custom_css;
                }

                if ($attr_tmp['css_override'] != null) {
                    $custom_css = $attr_tmp['css_override'];

                    $custom_css_master .= $custom_css;
                }

                if(!empty($custom_css_master)) {
                    $custom_css_master = '<style id="r1-custom-css" type="text/css">' . $custom_css_master . '</style>';
                }

                if(!empty($attr_tmp['iframe_ext_js'])) {
                    $custom_css_master = $custom_css_master . "<script src='{$attr_tmp['iframe_ext_js']}'></script>";
                }

                echo wp_unslash($custom_css_master);
            }
        }
        
        /**
         * @param array $headers
         * @return array
         */
        private function _set_request_header($headers = [])
        {
            // $current_headers = $this->default_headers;
            $current_headers = isset($this->default_headers) ? $this->default_headers : [];

            if(!empty($current_headers)) {
                foreach($headers as $key => $val) {
                    $current_headers[$key] = $val;
                }
            }
            return $this->_generate_request_headers($current_headers);
        }

        /**
         * @param array $headers
         * @return array
         */
        private function _generate_request_headers($headers = [])
        {
            $request_headers = [];

            if(!empty($headers)) {
                foreach($headers as $key => $val) {
                    $request_headers[] = $key . ":" . $val;
                }
            }
            return $request_headers;
        }

        /**
         * @param array $args
         * @return array
         */
        private function _set_request_args($args = [])
        {
            $current_args = isset($this->default_args) ? $this->default_args : [];

            if(!empty($args) && is_array($current_args)) {
                foreach($args as $key => $val) {
                    if(array_key_exists($key, $current_args)) {
                        $current_args[$key] = $val;
                    }
                }
            }
            return $current_args;
        }

        /**
         * @param array $args
         * @return array
         */
        private function _get_request_args($args = [])
        {
            return $this->_set_request_args($args);
        }

        /**
         *
         */
        public function dwc_frontend_scripts() {
            global $post;

            $script_rev = '202401220608';

            if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $this->short_code)){

                if (is_numeric(strpos($post->post_content, 'map_key'))) {

                    wp_register_style('dwc frontend font-awesome-min css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/font-awesome.min.css');
                    wp_enqueue_style('dwc frontend font-awesome-min css');

                    wp_enqueue_style('dwc frontend bootstrap css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/bootstrap-3.4.1/css/bootstrap.min.css');
                    wp_enqueue_style('dwc frontend bootstrap-select css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/bootstrap-select/bootstrap-select.min.css');

                    wp_enqueue_style('dwc frontend leaflet css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/leaflet/leaflet.css');

                    wp_enqueue_style('dwc frontend map_custom front css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/map_custom_front.css',array(), time(),'all');

                    wp_register_script('dwc jquery js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery js');

                    wp_register_script('dwc jquery-ui-core js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/core.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-core js');

                    wp_enqueue_script('dwc bootstrap js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/bootstrap-3.4.1/js/bootstrap.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc bootstrap-list js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/bootstrap-3.4.1/js/list.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc bootstrap-select js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/bootstrap-select/bootstrap-select.min.js', array(), $script_rev);

                    wp_register_script('dwc jquery-ui-widget js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/ui/widget.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-widget js');

                    wp_register_script('dwc iframe-resizer-window js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/iframeResizer.contentWindow.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc iframe-resizer-window js');

                    wp_enqueue_script('dwc leaflet js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/leaflet/leaflet.js', array(), $script_rev);

                    wp_enqueue_style('dwc frontend body-bg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/body-bg.css');

                }elseif ($this->_check_screen_key("map")) {

                    wp_register_style('dwc frontend font-awesome-min css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/font-awesome.min.css');
                    wp_enqueue_style('dwc frontend font-awesome-min css');

                    wp_register_style('dwc frontend ace-fonts css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/ace/ace-fonts.css');
                    wp_enqueue_style('dwc frontend ace-fonts css');

                    wp_enqueue_style('dwc frontend leaflet css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/leaflet/leaflet.css');
                    wp_enqueue_style('dwc frontend leaflet-awesome-markers css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/leaflet/leaflet.awesome-markers.css');

                    wp_register_style('dwc frontend sweetalert css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/sweetalert/sweetalert.min.css');
                    wp_enqueue_style('dwc frontend sweetalert css');

                    wp_register_script('dwc jquery js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery js');

                    wp_register_script('dwc jquery-ui-core js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/core.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-core js');

                    wp_register_script('dwc jquery-ui-widget js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/ui/widget.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-widget js');

                    wp_register_style('dwc jquery-ui-structure css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.structure.min.css');
                    wp_enqueue_style('dwc jquery-ui-structure css');

                    wp_register_style('dwc jquery-ui-theme css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.css');
                    wp_enqueue_style('dwc jquery-ui-theme css');

                    wp_register_script('dwc sweetalert js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/sweetalert/sweetalert.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc sweetalert js');

                    wp_enqueue_script('dwc leaflet js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/leaflet/leaflet.js', array(), $script_rev);
                    wp_enqueue_script('dwc leaflet-awesome-markers js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/leaflet/leaflet.awesome-markers.js', array(), $script_rev);

                    wp_register_style('dwc frontend body-bg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/body-bg.css');
                    wp_enqueue_style('dwc frontend body-bg css');

                    wp_register_style('dwc frontend map_custom css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/map_custom.css');
                    wp_enqueue_style('dwc frontend map_custom css');

                    wp_register_style('dwc frontend ace_custom css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/ace_custom.css');
                    wp_enqueue_style('dwc frontend ace_custom css');

                    wp_register_script('dwc iframe-resizer-window js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/iframeResizer.contentWindow.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc iframe-resizer-window js');

                } elseif ($this->_check_screen_key("signature")) {

                    wp_register_style('dwc frontend form-signature-pad css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/signature-pad/css/form-signature-pad.css');
                    wp_enqueue_style('dwc frontend form-signature-pad css');

                    wp_register_style('dwc frontend form-signature-pad-custom css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/signature-pad/css/form-signature-pad-custom.css');
                    wp_enqueue_style('dwc frontend form-signature-pad-custom css');

                    wp_register_script('dwc jquery js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery js');

                    wp_register_script('dwc jquery-ui-core js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/core.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-core js');

                    wp_register_script('dwc jquery-ui-widget js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/ui/widget.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-widget js');

                    wp_register_style('dwc jquery-ui-structure css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.structure.min.css');
                    wp_enqueue_style('dwc jquery-ui-structure css');

                    wp_register_style('dwc jquery-ui-theme css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.css');
                    wp_enqueue_style('dwc jquery-ui-theme css');

                    wp_register_style('dwc frontend body-bg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/body-bg.css');
                    wp_enqueue_style('dwc frontend body-bg css');

                    wp_register_script('dwc form-signature-pad js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/signature-pad/js/form-signature-pad.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-signature-pad js');

                    wp_register_script('dwc iframe-resizer-window js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/iframeResizer.contentWindow.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc iframe-resizer-window js');

                } elseif ($this->_check_screen_key("terms")) {

                    wp_register_style('dwc frontend font-awesome-min css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/font-awesome.min.css');
                    wp_enqueue_style('dwc frontend font-awesome-min css');

                    wp_register_style('dwc frontend form-reset css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-reset.css');
                    wp_enqueue_style('dwc frontend form-reset css');

                    wp_register_style('dwc frontend form-color-blue css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-color-blue.css');
                    wp_enqueue_style('dwc frontend form-color-blue css');

                    wp_register_style('dwc frontend form-base css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-base.css');
                    wp_enqueue_style('dwc frontend form-base css');

                    wp_register_style('dwc frontend form-build css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-build.css');
                    wp_enqueue_style('dwc frontend form-build css');

                    wp_register_script('dwc jquery js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery js');

                    wp_register_script('dwc jquery-ui-core js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/core.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-core js');

                    wp_register_script('dwc jquery-ui-widget js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/ui/widget.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-widget js');

                    wp_register_style('dwc summernote css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/summernote/summernote-lite.css');
                    wp_enqueue_style('dwc summernote css');

                    wp_register_script('dwc summernote js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/summernote/summernote-lite.js', array(), $script_rev);
                    wp_enqueue_script('dwc summernote js');

                    wp_register_style('dwc jquery-ui css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.min.css');
                    wp_enqueue_style('dwc jquery-ui css');

                    wp_register_style('dwc jquery-ui-structure css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.structure.min.css');
                    wp_enqueue_style('dwc jquery-ui-structure css');

                    wp_register_style('dwc jquery-ui-theme css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.css');
                    wp_enqueue_style('dwc jquery-ui-theme css');

                    wp_register_script('dwc form-response js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-response.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-response js');

                    wp_register_script('dwc iframe-resizer-window js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/iframeResizer.contentWindow.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc iframe-resizer-window js');

                    wp_register_style('dwc frontend form-terms css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-terms.css');
                    wp_enqueue_style('dwc frontend form-terms css');

                    wp_register_style('dwc frontend form-custom css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-custom.css');
                    wp_enqueue_style('dwc frontend form-custom css');

                    wp_register_style('dwc frontend body-bg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/body-bg.css');
                    wp_enqueue_style('dwc frontend body-bg css');

                } elseif ($this->_check_screen_key("amenities")) {

                    // http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css

                    wp_register_style('dwc frontend jquery-confirm css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-confirm.min.css');
                    wp_enqueue_style('dwc frontend jquery-confirm css');

                    wp_register_style('dwc frontend form-fileinput css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-fileinput.css');
                    wp_enqueue_style('dwc frontend form-fileinput css');

                    wp_register_style('dwc frontend font-awesome-min css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/font-awesome.min.css');
                    wp_enqueue_style('dwc frontend font-awesome-min css');

                    wp_register_style('dwc frontend form-reset css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-reset.css');
                    wp_enqueue_style('dwc frontend form-reset css');

                    wp_register_style('dwc frontend form-color-blue css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-color-blue.css');
                    wp_enqueue_style('dwc frontend form-color-blue css');

                    wp_register_style('dwc frontend form-base css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-base.css');
                    wp_enqueue_style('dwc frontend form-base css');

                    wp_register_style('dwc frontend form-build css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-build.css');
                    wp_enqueue_style('dwc frontend form-build css');

                    wp_register_style('dwc parsley css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/parsley.css');
                    wp_enqueue_style('dwc parsley css');

                    wp_register_style('dwc jquery-ui-structure css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.structure.min.css');
                    wp_enqueue_style('dwc jquery-ui-structure css');

                    wp_register_style('dwc jquery-ui-theme css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.css');
                    wp_enqueue_style('dwc jquery-ui-theme css');

                    wp_register_style('dwc intltelinput css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/css/intlTelInput.css');
                    wp_enqueue_style('dwc intltelinput css');

                    wp_register_script('dwc jquery js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery js');

                    wp_register_script('dwc jquery-ui-core js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/core.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-core js');

                    wp_register_script('dwc jquery-ui-button js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/button.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-button js');

                    wp_register_script('dwc jquery-ui-spinner js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/spinner.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-spinner js');

                    wp_register_script('dwc form-select2 js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/select2.full.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-select2 js');

                    wp_register_script('dwc form-select2-config js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-select2-config.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-select2-config js');

                    wp_register_script('dwc parsley js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/parsley.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc parsley js');

                    wp_register_script('dwc intltelinput js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/js/intlTelInput.js', array(), $script_rev);
                    wp_enqueue_script('dwc intltelinput js');

                    wp_register_script('dwc intltelinput-jq js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/js/intlTelInput-jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc intltelinput-jq js');

                    wp_register_script('dwc intltelinput-utils js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/js/utils.js', array(), $script_rev);
                    wp_enqueue_script('dwc intltelinput-utils js');

                    wp_register_script('dwc form-purify js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-purify.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-purify js');

                    wp_register_script('dwc form-fileinput js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-fileinput.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-fileinput js');

                    wp_register_script('dwc form-inputmask-bundle js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/jquery.inputmask.bundle.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-inputmask-bundle js');

                    wp_register_script('dwc jquery-ui-widget js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/ui/widget.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-widget js');

                    wp_register_script('dwc form-response js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-response.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-response js');

                    wp_register_script('dwc jbox js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jbox/jBox.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jbox js');

                    wp_register_style('dwc frontend jbox css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jbox/jBox.css');
                    wp_enqueue_style('dwc frontend jbox css');

                    wp_register_script('dwc query-confirm js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-confirm.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc query-confirm js');

                    wp_register_script('dwc iframe-resizer-window js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/iframeResizer.contentWindow.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc iframe-resizer-window js');

                    wp_register_script('dwc form-main js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-main.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-main js');
                    wp_localize_script('dwc form-main js', 'dwc_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

                    wp_register_style('dwc frontend form-custom css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-custom.css');
                    wp_enqueue_style('dwc frontend form-custom css');

                    wp_register_script('dwc form-custom js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-custom.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-custom js');

                    wp_register_style('dwc frontend body-bg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/body-bg.css');
                    wp_enqueue_style('dwc frontend body-bg css');

                } elseif ($this->_check_screen_key("payment")) {

                    wp_register_style('dwc frontend form-color-blue css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-color-blue.css');
                    wp_enqueue_style('dwc frontend form-color-blue css');

                    wp_register_style('dwc frontend jquery-confirm css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-confirm.min.css');
                    wp_enqueue_style('dwc frontend jquery-confirm css');

                    wp_register_style('dwc frontend form-cc-mask css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-cc-mask.css');
                    wp_enqueue_style('dwc frontend form-cc-mask css');

                    wp_register_style('dwc frontend form-fileinput css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-fileinput.css');
                    wp_enqueue_style('dwc frontend form-fileinput css');

                    wp_register_style('dwc frontend font-awesome-min css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/font-awesome.min.css');
                    wp_enqueue_style('dwc frontend font-awesome-min css');

                    wp_register_style('dwc frontend form-reset css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-reset.css');
                    wp_enqueue_style('dwc frontend form-reset css');

                    wp_register_style('dwc frontend form-base css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-base.css');
                    wp_enqueue_style('dwc frontend form-base css');

                    wp_register_style('dwc frontend form-build css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-build.css');
                    wp_enqueue_style('dwc frontend form-build css');

                    wp_register_script('dwc jquery js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery js');

                    wp_register_script('dwc jquery-ui-core js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/core.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-core js');

                    wp_register_script('dwc jquery-ui-button js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/button.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-button js');

                    wp_register_script('dwc jquery-ui-widget js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/ui/widget.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-widget js');

                    wp_register_script('dwc jquery-ui-spinner js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/spinner.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-spinner js');

                    wp_register_style('dwc jquery-ui-structure css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.structure.min.css');
                    wp_enqueue_style('dwc jquery-ui-structure css');

                    wp_register_style('dwc jquery-ui-theme css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.css');
                    wp_enqueue_style('dwc jquery-ui-theme css');

                    wp_register_script('dwc form-cc_mask js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-cc_mask.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-cc_mask js');

                    wp_localize_script('dwc form-cc_mask js', 'registerOne', array(
                        'pluginsUrl' => plugins_url(),
                    ));

                    wp_register_script('dwc savy-store-local js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/savy.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc savy-store-local js');
                    
                    wp_register_script('dwc form-purify js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-purify.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-purify js');

                    wp_register_script('dwc form-fileinput js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-fileinput.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-fileinput js');

                    wp_register_script('dwc form-inputmask-bundle js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/jquery.inputmask.bundle.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-inputmask-bundle js');

                    wp_register_script('dwc form-response js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-response.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-response js');

                    wp_register_script('dwc jbox js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jbox/jBox.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jbox js');

                    wp_register_style('dwc frontend jbox css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jbox/jBox.css');
                    wp_enqueue_style('dwc frontend jbox css');

                    wp_register_style('dwc parsley css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/parsley.css');
                    wp_enqueue_style('dwc parsley css');

                    wp_register_script('dwc parsley js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/parsley.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc parsley js');

                    wp_localize_script('dwc parsley js', 'siteBase', array(
                        'pluginsUrl' => plugins_url(),
                    ));

                    wp_register_style('dwc frontend form-custom css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-custom.css');
                    wp_enqueue_style('dwc frontend form-custom css');

                    wp_register_style('dwc frontend body-bg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/body-bg.css');
                    wp_enqueue_style('dwc frontend body-bg css');

                    wp_register_script('dwc stripe js', 'https://js.stripe.com/v2/', array(), $script_rev);
                    wp_enqueue_script('dwc stripe js');

                    wp_register_script('dwc stripe-listener js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/stripe-listener.js', array(), $script_rev);
                    wp_enqueue_script('dwc stripe-listener js');

                    wp_register_script('dwc query-confirm js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-confirm.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc query-confirm js');

                    wp_register_script('dwc iframe-resizer-window js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/iframeResizer.contentWindow.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc iframe-resizer-window js');

                    wp_register_script('dwc cart-custom js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/cart-custom.js', array(), $script_rev);
                    wp_enqueue_script('dwc cart-custom js');

                    wp_register_script('dwc form-main js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-main.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-main js');
                    wp_localize_script('dwc form-main js', 'dwc_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

                    wp_register_script('dwc form-custom js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-custom.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-custom js');

                } else {

                    wp_register_style('dwc frontend jquery-confirm css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-confirm.min.css');
                    wp_enqueue_style('dwc frontend jquery-confirm css');

                    wp_register_style('dwc frontend form-fileinput css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-fileinput.css');
                    wp_enqueue_style('dwc frontend form-fileinput css');

                    wp_register_style('dwc frontend form-trumbowyg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-trumbowyg.css');
                    wp_enqueue_style('dwc frontend form-trumbowyg css');

                    wp_register_style('dwc frontend form-select2 css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/select2.min.css');
                    wp_enqueue_style('dwc frontend form-select2 css');

                    wp_register_style('dwc frontend form-select2-addl css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/select2-addl.css');
                    wp_enqueue_style('dwc frontend form-select2-addl css');

                    wp_register_style('dwc frontend kv-widgets css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/kv-widgets.css');
                    wp_enqueue_style('dwc frontend kv-widgets css');

                    wp_register_style('dwc frontend font-awesome-min css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/font-awesome.min.css');
                    wp_enqueue_style('dwc frontend font-awesome-min css');

                    wp_register_style('dwc frontend form-reset css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-reset.css');
                    wp_enqueue_style('dwc frontend form-reset css');

                    wp_register_style('dwc frontend form-color-blue css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-color-blue.css');
                    wp_enqueue_style('dwc frontend form-color-blue css');

                    wp_register_style('dwc frontend form-base css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-base.css');
                    wp_enqueue_style('dwc frontend form-base css');

                    wp_register_style('dwc frontend form-build css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-build.css');
                    wp_enqueue_style('dwc frontend form-build css');

                    wp_register_style('dwc summernote css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/summernote/summernote-lite.css');
                    wp_enqueue_style('dwc summernote css');

                    wp_register_style('dwc intltelinput css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/css/intlTelInput.css');
                    wp_enqueue_style('dwc intltelinput css');

                    wp_register_script('dwc jquery js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery js');

                    wp_register_script('dwc jquery-ui-core js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/core.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-core js');

                    wp_register_script('dwc jquery-ui-button js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/button.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-button js');

                    wp_register_script('dwc jquery-ui-spinner js', WORDPRESS_ROOT_PATH . '/wp-includes/js/jquery/ui/spinner.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-spinner js');

                    wp_register_script('dwc form-select2 js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/select2.full.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-select2 js');

                    wp_register_script('dwc form-select2-config js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-select2-config.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-select2-config js');

                    wp_register_script('dwc parsley js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/parsley.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc parsley js');

                    wp_register_script('dwc jquery-ui-widget js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/ui/widget.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jquery-ui-widget js');

                    wp_register_style('dwc jquery-ui-structure css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.structure.min.css');
                    wp_enqueue_style('dwc jquery-ui-structure css');

                    wp_register_style('dwc jquery-ui-theme css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-ui-1.12.1/jquery-ui.css');
                    wp_enqueue_style('dwc jquery-ui-theme css');

                    wp_register_script('dwc form-trumbowyg js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-trumbowyg.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-trumbowyg js');

                    wp_register_script('dwc summernote js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/summernote/summernote-lite.js', array(), $script_rev);
                    wp_enqueue_script('dwc summernote js');

                    wp_register_script('dwc intltelinput js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/js/intlTelInput.js', array(), $script_rev);
                    wp_enqueue_script('dwc intltelinput js');

                    wp_register_script('dwc intltelinput-jq js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/js/intlTelInput-jquery.js', array(), $script_rev);
                    wp_enqueue_script('dwc intltelinput-jq js');

                    wp_register_script('dwc intltelinput-utils js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/intlTelInput/build/js/utils.js', array(), $script_rev);
                    wp_enqueue_script('dwc intltelinput-utils js');

                    wp_register_script('dwc form-trumbowyg-config js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-trumbowyg-config.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-trumbowyg-config js');

                    wp_register_script('dwc form-select2 js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/select2.full.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-select2 js');

                    wp_register_script('dwc form-select2-config js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-select2-config.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-select2-config js');

                    wp_register_script('dwc form-sortable js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-sortable.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-sortable js');

                    wp_register_script('dwc form-purify js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-purify.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-purify js');

                    wp_register_script('dwc form-fileinput js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-fileinput.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-fileinput js');

                    wp_register_script('dwc kv-widgets js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/kv-widgets.js', array(), $script_rev);
                    wp_enqueue_script('dwc kv-widgets js');

                    wp_register_script('dwc form-inputmask-bundle js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/jquery.inputmask.bundle.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-inputmask-bundle js');

                    wp_register_script('dwc form-response js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-response.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-response js');

                    wp_register_script('dwc jbox js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jbox/jBox.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc jbox js');

                    wp_register_style('dwc frontend jbox css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jbox/jBox.css');
                    wp_enqueue_style('dwc frontend jbox css');

                    wp_register_script('dwc autogrow js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/autogrow/autogrow.js', array(), $script_rev);
                    wp_enqueue_script('dwc autogrow js');

                    wp_register_style('dwc parsley css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/parsley.css');
                    wp_enqueue_style('dwc parsley css');

                    wp_register_script('dwc parsley js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/parsley.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc parsley js');

                    wp_register_style('dwc frontend form-custom css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/form-custom.css');
                    wp_enqueue_style('dwc frontend form-custom css');

                    wp_register_style('dwc frontend body-bg css', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/css/body-bg.css');
                    wp_enqueue_style('dwc frontend body-bg css');

                    wp_register_script('dwc iframe-resizer-window js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/iframeResizer.contentWindow.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc iframe-resizer-window js');

                    wp_register_script('dwc query-confirm js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/jquery-confirm.min.js', array(), $script_rev);
                    wp_enqueue_script('dwc query-confirm js');

                    wp_register_script('dwc form-main js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-main.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-main js');
                    wp_localize_script('dwc form-main js', 'dwc_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

                    wp_register_script('dwc form-custom js', REGISTERONE_WORDPRESS_CLIENT_URL . '/front-end/assets/js/form-custom.js', array(), $script_rev);
                    wp_enqueue_script('dwc form-custom js');
                }
            }
        }

        /**
         * @param $class
         */
        public function autoload($class)
        {
            $name = explode( '_', $class );

            if(isset( $name[1] ) || isset( $name[2] )){
                if(sizeof( $name ) > 2 ) {
                    $class_name = strtolower( $name[1] ) . '-' . strtolower( $name[2] );
                    $file_name = dirname( __FILE__ ) . '/admin/classes/' . $class_name . '.php';
                } else {
                    $class_name = strtolower( $name[1] );
                    $file_name = dirname( __FILE__ ) . '/admin/classes/' . $class_name . '.php';
                }

                if(file_exists( $file_name )){
                    require_once $file_name;
                }
            }else{

                $class_name = $name[0];
                $file_name = dirname(__FILE__) . '/admin/classes/' . $class_name . '.php';

                if(file_exists($file_name)) {

                    require_once $file_name;
                }
            }
        }

        public function dwc_version_require( $error_text ) {
            if( current_user_can( 'manage_options' )) {
                echo '<div class="error"><p>' . $error_text . '</p></div>';
            }
        }

        /**
         * Merge the blank template into existing WP template array.
         */
        public function register_project_templates( $atts ) {

            // Get theme object
            $theme = wp_get_theme();

            // Create the key used for the themes cache
            $cache_key = 'page_templates-' . md5( $theme->get_theme_root() . '/' . $theme->get_stylesheet() );

            // Retrieve existing page templates
            $templates = $theme->get_page_templates();

            // Add our template(s) to the list of existing templates by merging the arrays
            $templates = array_merge( $templates, $this->templates );

            // Replace existing value in cache
            wp_cache_set( $cache_key, $templates, 'themes', 300 );

            add_filter( 'theme_page_templates', function( $page_templates ) use ( $templates ) {
                return $templates;
            });

            return $atts;
        }

        /**
         * Checks if the template is assigned to the page
         */
        public function view_project_template($template) {

            global $post;

            if(!isset( $this->templates[get_post_meta($post->ID, '_wp_page_template', true)])){
                return $template;
            }

            $file = plugin_dir_path( __FILE__ ) . get_post_meta($post->ID, '_wp_page_template', true);

            // Just to be safe, we check if the file exist first
            if(file_exists( $file )){
                return $file;
            }else{
                echo $file;
            }
            return $template;
        }

        /**
         *
         */
        public function dwc_plugin_activation()
        {
            if( version_compare( PHP_VERSION, '5.2.6', '<')){
                $this->error_text = 'The RegisterONE For WordPress plugin requires at least PHP 5.2.6.';
            }
            if( version_compare( get_bloginfo( 'version' ), '3.1', '<')){
                $this->error_text = 'The RegisterONE For WordPress plugin requires at least WordPress version 3.1.';
            }

            if($this->error_text !== ''){
                add_action('admin_notices', array($this, 'dwc_version_require'), $this->error_text);
                // add_option(RegisteroneAccountInformation::$key, RegisteroneAccountInformation::get_settings_defaults(), '', 'yes');
            }
        }

        public function dwc_plugin_deactivation()
        {
            if(is_multisite()) {
                if(is_plugin_active_for_network(REGISTERONE_WORDPRESS_CLIENT_FILE)) {
                    $sites = get_sites(['fields' => 'ids']);
                    foreach ($sites as $site) {
                        delete_blog_option($site, 'dwc_account_information');
                        delete_blog_option($site, 'dwc_advanced_settings');
                        delete_blog_option($site, 'dwc_form_custom');
                        delete_blog_option($site, 'dwc_form_setup');
                    }
                }else {
                    delete_blog_option(get_current_blog_id(), 'dwc_account_information');
                    delete_blog_option(get_current_blog_id(), 'dwc_advanced_settings');
                    delete_blog_option(get_current_blog_id(), 'dwc_form_custom');
                    delete_blog_option(get_current_blog_id(), 'dwc_form_setup');
                }
            }else {

                delete_option('dwc_account_information');
                delete_option('dwc_advanced_settings');
                delete_option('dwc_form_custom');
                delete_option('dwc_form_setup');
            }
        }
    }

    $GLOBALS['registerone_wordpress_client'] = new RegisteroneWordpressClient();
}

/*
function registerone_flush_rewrite_rules() {

    global $wp_rewrite;

    $wp_rewrite->flush_rules();

} // End flush_rewrite_rules()
add_action( 'init', 'registerone_flush_rewrite_rules' );
*/

/*
 * Dynamic form support
 * ref. https://codex.wordpress.org/Rewrite_API/add_rewrite_rule#Using_Custom_Templates_with_custom_querystring
 * Usage: [registerone_form form_key="dynamic"]
 * domain.com/form/{form_key}/
 * re-writes internal query as: /?pagename=form&form_key={form_key}
 *
 * Dynamic form_key with url slug
 * $attributes['form_key'] = $attributes['form_key'] == 'dynamic' ? $wp_query->query_vars['form_key'] : $attributes['form_key'];
 * */
function registerone_rewrite_tag() {
    add_rewrite_tag('%form_key%', '([^&]+)');
    add_rewrite_tag('%id_hash%', '([^&]+)');
}

function registerone_rewrite_rule() {
    // writes internal query as: /?pagename=form&form_key={form_key}
    add_rewrite_rule('([^/]*)/([^/]*)/?', 'index.php?pagename=form&id_hash=$matches[1]&form_key=$matches[2]', 'top');
}

$doc_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';

if(is_string(stristr($doc_root, 'forms.registerone'))){
    add_action('init', 'registerone_rewrite_tag', 10, 0);
    add_action('init', 'registerone_rewrite_rule', 10, 0);
}

add_action('wp_ajax_get_booth_details', 'get_booth_details_callback');
add_action('wp_ajax_nopriv_get_booth_details', 'get_booth_details_callback');

function get_booth_details_callback(){
    $base_request_url = esc_url($_POST["base_request_url"]);
    $event_code = sanitize_text_field($_POST["event_code"]);
    $amenity_id = sanitize_text_field($_POST["amenity_id"]);
    $href_id = sanitize_text_field($_POST["href_id"]);

    if(isset( $_POST["is_aid"] )){
        $api_url = esc_url($base_request_url.'vendordetail/'.$event_code.'/'.$amenity_id.'/'.$href_id);
    }else{
        $api_url = esc_url($base_request_url.'boothdetail/'.$event_code.'/'.$amenity_id.'/'.$href_id);
    }

    $plugin_base_name = 'dwc';
    $account_information = get_option($plugin_base_name . '_account_information');
    $api_auth_key = (isset($account_information['privateAPIkey'])) ? $account_information['privateAPIkey'] . ':' : '';
    $plugin_api_auth_key = base64_encode($api_auth_key);
    $headers = array(
        'Accept' => "application/json, text/javascript, */*; q=0.01",
        'Authorization' => 'Basic ' . $plugin_api_auth_key
    );
    $request_args = array(
        'method' => 'GET',
        'headers' => $headers,
        'timeout' => 30
    );

    //$result = wp_remote_request( $api_url, $request_args );
    $result = wp_remote_retrieve_body( wp_remote_request( $api_url, $request_args ) );

    wp_send_json($result);
    wp_die();
}

add_action('wp_ajax_get_map_view_areas', 'get_map_view_areas_callback');
add_action('wp_ajax_nopriv_get_map_view_areas', 'get_map_view_areas_callback');

function get_map_view_areas_callback(){
    $base_request_url = esc_url($_POST["base_request_url"]);
    $event_code = sanitize_text_field($_POST["event_code"]);
    $amenity_id = sanitize_text_field($_POST["amenity_id"]);

    $api_url = esc_url($base_request_url.'mapview/'.$event_code.'/'.$amenity_id);

    $plugin_base_name = 'dwc';
    $account_information = get_option($plugin_base_name . '_account_information');
    $api_auth_key = (isset($account_information['privateAPIkey'])) ? $account_information['privateAPIkey'] . ':' : '';
    $plugin_api_auth_key = base64_encode($api_auth_key);
    $headers = array(
        'Accept' => "application/json, text/javascript, */*; q=0.01",
        'Authorization' => 'Basic ' . $plugin_api_auth_key
    );
    $request_args = array(
        'method' => 'GET',
        'headers' => $headers,
        'timeout' => 30
    );

    //$result = wp_remote_request( $api_url, $request_args );
    $result = wp_remote_retrieve_body( wp_remote_request( $api_url, $request_args ) );

    wp_send_json($result);
    wp_die();
}

add_action('wp_ajax_get_map_areas_by_kid', 'get_map_areas_by_kid_callback');
add_action('wp_ajax_nopriv_get_map_areas_by_kid', 'get_map_areas_by_kid_callback');

function get_map_areas_by_kid_callback(){
    $base_request_url = esc_url($_POST["base_request_url"]);
    $event_code = sanitize_text_field($_POST["event_code"]);
    $amenity_id = sanitize_text_field($_POST["amenity_id"]);
    $kid = sanitize_text_field($_POST["kid"]);

    $api_url = esc_url($base_request_url.'mapview/'.$event_code.'/'.$amenity_id.'/'.$kid);

    $plugin_base_name = 'dwc';
    $account_information = get_option($plugin_base_name . '_account_information');
    $api_auth_key = (isset($account_information['privateAPIkey'])) ? $account_information['privateAPIkey'] . ':' : '';
    $plugin_api_auth_key = base64_encode($api_auth_key);
    $headers = array(
        'Accept' => "application/json, text/javascript, */*; q=0.01",
        'Authorization' => 'Basic ' . $plugin_api_auth_key
    );
    $request_args = array(
        'method' => 'GET',
        'headers' => $headers,
        'timeout' => 30
    );

    //$result = wp_remote_request( $api_url, $request_args );
    $result = wp_remote_retrieve_body( wp_remote_request( $api_url, $request_args ) );

    wp_send_json($result);
    wp_die();
}

add_action('wp_ajax_get_map_areas', 'get_map_areas_callback');
add_action('wp_ajax_nopriv_get_map_areas', 'get_map_areas_callback');

function get_map_areas_callback(){
    $base_request_url = esc_url($_POST["base_request_url"]);
    $id_hash = sanitize_text_field($_POST["id_hash"]);
    $form_key = sanitize_text_field($_POST["form_key"]);
    $pk = sanitize_text_field($_POST["pk"]);
    $map = sanitize_text_field($_POST["map"]);

    if($pk != null){
        $api_url = esc_url($base_request_url.$id_hash.'/'.$form_key.'/map'.'/'.$pk.'/'.$map);
    }else{
        $api_url = esc_url($base_request_url.$id_hash.'/'.$form_key.'/map');
    }

    $plugin_base_name = 'dwc';
    $account_information = get_option($plugin_base_name . '_account_information');
    $api_auth_key = (isset($account_information['privateAPIkey'])) ? $account_information['privateAPIkey'] . ':' : '';
    $plugin_api_auth_key = base64_encode($api_auth_key);
    $headers = array(
        'Accept' => "application/json, text/javascript, */*; q=0.01",
        'Authorization' => 'Basic ' . $plugin_api_auth_key
    );
    $request_args = array(
        'method' => 'GET',
        'headers' => $headers,
        'timeout' => 30
    );

    // wp_send_json($request_args); die;
    // headers: Object { Accept: "application/json, text/javascript, */*; q=0.01", Authorization: "Basic " }

    //$result = wp_remote_request( $api_url, $request_args );
    $result = wp_remote_retrieve_body( wp_remote_request( $api_url, $request_args ));
    // echo $api_url;
    // print_r($request_args);
    wp_send_json($result);
    wp_die();
}

/*
 * hold header output until after any wp_redirects()
 * */
function app_output_buffer_r1() {
    ob_clean();
    ob_start();
}
add_action('init', 'app_output_buffer_r1');

function get_screen()
{
    if(isset( $_GET['profile'])){
        return "profile";

    }elseif(isset( $_GET['session'])){
        return "session";

    }elseif(isset( $_GET['uploads'])){
        return "uploads";

    }elseif(isset( $_GET['amenities'])) {
        if(isset( $_GET['map']) && $_GET['map'] != '') {
            return "map";
        }else{
            return "amenities";
        }

    }elseif(isset( $_GET['terms'])) {
        if(isset( $_GET['signature']) && $_GET['signature'] != '') {
            return "signature";
        }else{
            return "terms";
        }

    }elseif(isset( $_GET['payment'])){
        return "payment";

    }elseif(isset( $_GET['mapview'])){
        return "mapview";

    }elseif(isset( $_GET['login'])){
        return "login";

    }elseif(isset( $_GET['register'])){
        return "register";

    }elseif(isset( $_GET['passreset'])){
        return "passreset";

    }elseif(isset( $_GET['privacysign'])){
        return "privacysign";

    }elseif(isset( $_GET['clearsession'])){
        return "clearsession";

    }else{
        return "login";
    }
}

function set_logout()
{
    $current_screen = get_screen();

    if(
        ($current_screen == 'login' || $current_screen == 'logout')
        && (!isset($_SESSION['r1_book_code']) && !isset($_GET['book']))
        && (!isset($_SESSION['ok']) && !isset($_GET['ok']))
        || ($current_screen == 'clearsession')
    ) {

        // echo "set_logout() - clear_all";

        setcookie('clear_all', 1, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);

        if(isset($_COOKIE['rfc'])){
            unset($_COOKIE['rfc']);
            setcookie('rfc', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_COOKIE['rpk'])){
            unset($_COOKIE['rpk']);
            setcookie('rpk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_COOKIE['pk'])){
            unset($_COOKIE['pk']);
            setcookie('pk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_SESSION['pk'])){
            unset($_SESSION['pk']);
            setcookie('pk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_SESSION['current_screen'])){
            unset($_SESSION['current_screen']);
            setcookie('current_screen', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_COOKIE['temp_pk'])){
            //unset($_COOKIE['temp_pk']);
            //setcookie('temp_pk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_SESSION['temp_pk'])){
            //unset($_SESSION['temp_pk']);
            //setcookie('temp_pk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }

        if(1 == 1){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Unset all of the session variables.
            $_SESSION = [];

            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Finally, destroy the session.
            session_destroy();
        }

    }else{
        setcookie('clear_all', 0, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
    }
}
add_action('init', 'set_logout');

function set_login_reg()
{
    if(array_key_exists('register', $_GET) || array_key_exists('login', $_GET)) {

        // echo "set_login_reg() - clear_all";

        setcookie('clear_all', 1, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);

        if(isset($_SESSION['api_token'])){
            unset($_SESSION['api_token']);
            setcookie('api_token', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_COOKIE['rfc'])){
            unset($_COOKIE['rfc']);
            setcookie('rfc', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_COOKIE['rpk'])){
            unset($_COOKIE['rpk']);
            setcookie('rpk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }

    }else{
        setcookie('clear_all', 0, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
    }
}
add_action('init', 'set_login_reg');

function add_query_vars_filter( $vars ) {
    $vars[] = "book";
    return $vars;
}

add_filter( 'query_vars', 'add_query_vars_filter' );

function set_book_code() {

    if(isset($_GET['book'])) {
        if ($book_code = sanitize_text_field($_GET['book'])) {

            //echo "<pre>";
            //echo $book_code;
            //echo "</pre>";

            if ($book_code == 'added') {
                // de-activate booth book
                unset($_COOKIE['r1_book_code']);
                setcookie('r1_book_code', 0, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);

                if (isset($_SESSION['r1_book_code'])) {
                    unset($_SESSION['r1_book_code']);
                }
            } else {
                setcookie('r1_book_code', $book_code, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);
                $_SESSION['r1_book_code'] = $book_code;
                session_write_close();

                //echo "<pre>";
                //echo $_SESSION['r1_book_code'];
                //echo "</pre>";
            }
        }
    }
}
add_action('init', 'set_book_code');

function set_pk()
{
    if(isset($_GET['pk'])) {
        $current_screen = get_screen();

        if (PHP_VERSION_ID < 70300) {
            setcookie('pk', sanitize_text_field($_GET['pk']), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
            $_SESSION['pk'] = sanitize_text_field($_GET['pk']);

            if(!empty($current_screen)){
                setcookie('current_screen', sanitize_text_field($current_screen), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
                $_SESSION['current_screen'] = sanitize_text_field($current_screen);
            }
            session_write_close();
        }else{
            setcookie('pk', sanitize_text_field($_GET['pk']), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
            $_SESSION['pk'] = sanitize_text_field($_GET['pk']);

            if(!empty($current_screen)){
                setcookie('current_screen', sanitize_text_field($current_screen), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
                $_SESSION['current_screen'] = sanitize_text_field($current_screen);
            }
            session_write_close();
        }

        /*
        echo "<pre>";
        echo "set_pk()";
        echo "SESSION['pk']: {$_SESSION['pk']}";
        echo "SESSION['current_screen']: {$_SESSION['current_screen']}";
        // echo $current_screen;
        echo "</pre>";
        */

        if(isset($_COOKIE['rpk'])){
            unset($_COOKIE['rpk']);
            setcookie('rpk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_COOKIE['rfc'])){
            unset($_COOKIE['rfc']);
            setcookie('rfc', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
        if(isset($_SESSION['temp_pk'])){
            //unset($_SESSION['temp_pk']);
            //setcookie('temp_pk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
    }
}

add_action('init', 'set_pk');

function set_ok()
{
    if(isset($_GET['ok'])) {
        $current_screen = get_screen();

        if (PHP_VERSION_ID < 70300) {
            setcookie('ok', sanitize_text_field($_GET['ok']), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
            $_SESSION['ok'] = sanitize_text_field($_GET['ok']);

            if(!empty($current_screen)){
                setcookie('current_screen', sanitize_text_field($current_screen), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
                $_SESSION['current_screen'] = sanitize_text_field($current_screen);
            }
            session_write_close();
        }else{
            setcookie('ok', sanitize_text_field($_GET['ok']), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
            $_SESSION['ok'] = sanitize_text_field($_GET['ok']);

            if(!empty($current_screen)){
                setcookie('current_screen', sanitize_text_field($current_screen), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
                $_SESSION['current_screen'] = sanitize_text_field($current_screen);
            }
            session_write_close();
        }

        /*
        echo "<pre>";
        echo "set_ok()";
        echo "SESSION['ok']: {$_SESSION['ok']}";
        echo "SESSION['current_screen']: {$_SESSION['current_screen']}";
        // echo $current_screen;
        echo "</pre>";
        */
    }
}

add_action('init', 'set_ok');

function set_temp_pk()
{
    $temp_pk = wp_generate_password(20, false);

    /*
    if($_SESSION['temp_pk']){
        echo "session::temp_pk: ";
        echo $_SESSION['temp_pk'];
        echo "<br>";
    }

    if($_SESSION['pk']){
        echo "session::pk: ";
        echo $_SESSION['pk'];
        echo "<br>";
    }
    */

    $current_screen = get_screen();

    if(($current_screen == 'amenities' || $current_screen == 'login') && (!isset($_SESSION['temp_pk']) && !isset($_SESSION['pk']))) {
        // echo "write temp_pk"; die;

        if (PHP_VERSION_ID < 70300) {
            setcookie('temp_pk', sanitize_text_field($temp_pk), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);
            $_SESSION['temp_pk'] = sanitize_text_field($temp_pk);

            if (!empty($current_screen)) {
                setcookie('current_screen', sanitize_text_field($current_screen), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);
                $_SESSION['current_screen'] = sanitize_text_field($current_screen);
            }
            session_write_close();
        } else {
            setcookie('temp_pk', sanitize_text_field($temp_pk), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);
            $_SESSION['temp_pk'] = sanitize_text_field($temp_pk);

            if (!empty($current_screen)) {
                setcookie('current_screen', sanitize_text_field($current_screen), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);
                $_SESSION['current_screen'] = sanitize_text_field($current_screen);
            }
            session_write_close();
        }

    }elseif((isset($_SESSION['pk']) && isset($_SESSION['temp_pk']))) {
        list($pk_prefix,) = explode(".", $_SESSION['pk'],2);

        //echo "isset:pk ({$_SESSION['pk']}) && isset:temp_pk ({$_SESSION['temp_pk']}) <br>";

        if($pk_prefix !== $_SESSION['temp_pk']) {
            //echo "pk !== temp_pk, CLEAR temp_pk <br>";
            if (isset($_COOKIE['temp_pk'])) {
                unset($_COOKIE['temp_pk']);
                setcookie('temp_pk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);
            }
            if (isset($_SESSION['temp_pk'])) {
                unset($_SESSION['temp_pk']);
                setcookie('temp_pk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true, true);
            }
        }else{
            //echo "isset:pk == isset:temp_pk <br>";
        }
    }

    /*
    echo "<pre>";
    echo "set_pk()";
    echo "SESSION['pk']: {$_SESSION['pk']}";
    echo "SESSION['current_screen']: {$_SESSION['current_screen']}";
    // echo $current_screen;
    echo "</pre>";
    */
}

add_action('init', 'set_temp_pk');

function set_rpk()
{
    if(isset( $_GET['rpk'])) {
        $rpk = sanitize_text_field($_GET['rpk']);
        if(!isset( $_COOKIE['rpk'])) {
            setcookie('rpk', $rpk, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
    }elseif(isset( $_COOKIE['pk'])) {
        if(isset($_COOKIE['rpk'])){
            unset($_COOKIE['rpk']);
            setcookie('rpk', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
    }
}
add_action('init', 'set_rpk');

function set_rfc()
{
    if(isset( $_GET['rfc'])) {
        $rfc = sanitize_text_field($_GET['rfc']);
        if(!isset( $_COOKIE['rfc'])) {
            setcookie('rfc', $rfc, 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
    }elseif(isset( $_COOKIE['pk'])) {
        if(isset($_COOKIE['rfc'])){
            unset($_COOKIE['rfc']);
            setcookie('rfc', '', 0 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
        }
    }
}
add_action('init', 'set_rfc');

function set_redirect()
{
    if(isset($_GET['ref'])) {
        if (PHP_VERSION_ID < 70300) {
            setcookie('redirect', sanitize_text_field($_GET['ref']), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
            $_SESSION['redirect'] = sanitize_text_field($_GET['ref']);
            session_write_close();
        }else{
            setcookie('redirect', sanitize_text_field($_GET['ref']), 30 * DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN,true,true);
            $_SESSION['redirect'] = sanitize_text_field($_GET['ref']);
            session_write_close();
        }
    }
}
add_action('init', 'set_redirect');
?>