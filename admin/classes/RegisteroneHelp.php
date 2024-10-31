<?php

class RegisteroneHelp {
	
	public static $key = 'registerone_help';
	
	function __construct() {

		add_action( 'admin_init', array( &$this, 'register_settings' ) );
	}
	
	function register_settings() {
		// register_setting( $option_group, $option_name, $sanitize_callback );
		register_setting( self::$key, self::$key, array( &$this, 'sanitize_help_settings' ) );
		
		// add_settings_section( $id, $title, $callback, $page );
		add_settings_section( 'section_help', 'Help and Setup Information', array( &$this, 'section_help_desc' ), self::$key );	
	}
	
	function section_help_desc() { ?>

        <p>Need help? Please contact us at <a href="mailto:support@registerone.com">support@registerone.com</a> with any setup questions you have.</p>
        <h3><strong>API SETUP</strong></h3>
        <p><strong>Using shortcodes to setup online applications:</strong></p>
        <p style="padding-left: 20px;">Log into your <a
                href="https://www.registerone.com" target="_blank"
                rel="noopener">RegisterONE account</a> and click on
            Settings &gt; WordPress API. From this screen, click the Generate
            button to create your API key. If one already exists, you may use it or
            Regenerate a new one if you need to. Your API key is meant to be kept
            private, so you may regenerate it at any time.</p>
        <p style="padding-left: 20px;">Once you create your key,
            copy and save it to the plugin API Settings screen. Your events and
            shortcodes should now be listed in the Events tab.</p>
        <h3><strong>FORM & MAP PAGE SETUP</strong></h3>
        <h3>1) Add a registration form or venue map to your website:</h3>
        <p style="padding-left: 20px;">Create a new blank PAGE
            (not a Post!) and paste the corresponding Shortcode into the page.</p>
        <p style="padding-left: 20px;">
            <img style="width: 331px; height: 119px;"
                 src="<?php echo REGISTERONE_WORDPRESS_CLIENT_URL; ?>/admin/assets/img/wp_shortcode.jpg"
                 alt=""></p>
        <p style="padding-left: 20px;">TIP: Use the Text tab on
            the editor to make sure there’s no extra formatting added to it.</p>
        <h3>2) Use the RegisterONE template (important!):</h3>
        <p style="padding-left: 20px;">Some WP theme templates
            contain formatting that may conflict with RegisterONE forms, so
            it’s important to select the RegisterONE template so it displays
            properly and is fully mobile responsive.
            The RegisterONE template contains
            minimal formatting and will display the form properly.
        </p>
        <p style="padding-left: 20px;"><img
                style="width: 284px; height: 195px;"
                src="<?php echo REGISTERONE_WORDPRESS_CLIENT_URL; ?>/admin/assets/img/form_page_template1.jpg"
                alt=""></p>

	<?php }
	
	function sanitize_help_settings() {
		// nothing to sanitize here folks, move along...
	}
	
}