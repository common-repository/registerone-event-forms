<?php

include_once('RegisteroneSettings.php');

class RegisteroneMain
{
   /*
   * the constructor
   * Fired during plugins_loaded (very very early),
   * only actions and filters,
   *
   */
    function __construct() {

        $regone_settings = new RegisteroneSettings();

        // Add shortcode support for widgets
    }
}