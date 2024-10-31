<?php

include_once( 'RegisteroneAccountInformation.php' );
include_once( 'RegisteroneFormSetup.php' );
include_once( 'RegisteroneMapSetup.php' );
include_once( 'RegisteroneHelp.php' );

class RegisteroneSettings {

    private static $_plugin_options_key = 'registerone_plugin_options';
    private $_plugin_settings_tabs = array();

    function __construct() {

        // make sure the admin menu gets hooked up in the admin menu
        add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );

        // add action link
        add_filter( 'plugin_action_links', array( &$this, 'add_action_links' ), 10, 2 );

        // Register settings
        $this->register_settings();
    }

    function register_settings() {
        $this->_plugin_settings_tabs[ RegisteroneAccountInformation::$key ]   = 'API Settings';
        $this->_plugin_settings_tabs[ RegisteroneFormSetup::$key ]            = 'Event Form Shortcodes';
        $this->_plugin_settings_tabs[ RegisteroneMapSetup::$key ]            = 'Event Map Shortcodes';
        $this->_plugin_settings_tabs[ RegisteroneIframes::$key ]                 = 'Forms with your Theme';
        $this->_plugin_settings_tabs[ RegisteroneHelp::$key ]                 = 'Setup Notes & Help';

        $account_information = new RegisteroneAccountInformation();
        $form_setup = new RegisteroneFormSetup();
        $map_setup = new RegisteroneMapSetup();
        $help = new RegisteroneHelp();
        $iframes = new RegisteroneIframes();
    }

    /**
     * Add action links to installed plugins page
     * @param $links
     * @param $file
     * @return array
     */
    function add_action_links($links, $file) {
        static $this_plugin;
        if (!$this_plugin) $this_plugin = REGISTERONE_WORDPRESS_CLIENT_FILE;
        if ($file == $this_plugin) {
            /**
             * The "page" query string value must be equal to the slug
             * of the Settings admin page we defined earlier,
             * the $_plugin_options_key property of this class which in
             * this case equals "regone_plugin_options".
             */
            $settings_link = '<a href="' . esc_url(get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . self::$_plugin_options_key) . '">Settings</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /*
      * Called during admin_menu, adds an options
      * page under Settings called Registerone Settings, rendered
      * using the plugin_options_page method.
      */
    function add_admin_menus() {
        // add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback );
        add_options_page( 'RegisterONE', 'RegisterONE', 'manage_options', self::$_plugin_options_key, array( &$this, 'plugin_options_page' ) );
    }

    /*
      * Plugin Options page rendering goes here, checks
      * for active tab and replaces key with the related
      * settings key. Uses the plugin_options_tabs method
      * to render the tabs.
      */
    function plugin_options_page() {
        $tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : RegisteroneAccountInformation::$key;
        ?>
        <div class="wrap">
            <?php $this->plugin_options_tabs(); ?>
            <form method="post" action="options.php">
                <?php
                wp_nonce_field( 'update-options' );
                settings_fields( $tab );
                do_settings_sections( $tab );

                // display form buttons for specific tabs...
                if ( $tab == 'dwc_account_information' ) {
                    echo '<p class="submit regone-submit">';
                    submit_button( 'Save', 'primary', $tab . '[submit]', false, array( 'id' => 'submit' ) );
                    submit_button( 'Reset', 'primary', $tab . '[reset]', false, array( 'id' => 'reset' ) );
                    echo '</p>';
                }
                ?>
            </form>
            <?php if ( $tab !== 'regone_help' ) {
            echo '';
            } ?>
        </div>
    <?php }

    /*
      * Renders our tabs in the plugin options page,
      * walks through the object's tabs array and prints
      * them one by one. Provides the heading for the
      * plugin_options_page method.
      */
    function plugin_options_tabs() {
        $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : RegisteroneAccountInformation::$key;
        screen_icon();
        // echo '<img class = "regone-settings-logo" style = "margin:5px 20px 0 0; float: left; width:150px; height:auto;" src="' . REGISTERONE_WORDPRESS_CLIENT_URL .'/admin/assets/img/registerone_logo.png"/>';
        echo '<h2 class="nav-tab-wrapper" style="padding-top:40px">';
        foreach ( $this->_plugin_settings_tabs as $tab_key => $tab_caption ) {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . self::$_plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        echo '</h2>';

        // output buffering to prevent errors from displaying at top of the page.
        ob_start();

        // settings_errors();
        $errors = ob_get_contents();
        ob_end_clean();
        echo $errors;
    }
}