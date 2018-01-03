<?php

/**
 * @package Pendel
 * @version 1.1
 */
/*
  Plugin Name: Pedel Image Wallpaper
  Plugin URI:
  Description: Show Image thumbnails of gps tracked imaged on a wallpaper
  Author: Christian Schulzendorff
  Version: 1.0
  Author URI:
 *
 */

/*
 * TODO Im Title u. Beschreibung duerfen keine HTML-relavanten Zeichen vorkommen, wie z.B. '
 * TODO Viewerladezeit zu lange und ruckelt
 */
require_once( 'pendel_main.php' );

/*
 * Switch for pendel plugin. Has to be hooked in 'the_content'.
 * If pendel configuration is part of the page, all content will be replaced by
 * the canvas. For all other pages, page content will be returned.
 */

function on_content() {
//    $configstr = '[pendel: name="ffm", x="4000", y="2000", tile="80", ' .
//            'start_lon="8.615272", start_lat="50.2031599",' .
//            'end_lon="9.1137766", end_lat="50.0544646"]';

    if (!is_pendel_content(get_the_content())) {
//        Nothing to do
//        echo "No pendel page <br>";
        return get_the_content();
    }


    $id = extract_pendel_id(get_the_content());
    if ($id == false or strlen($id) == 0) {
        trigger_error("Leaving pendel because of missing id.", E_USER_NOTICE);
        return;
    }
    trigger_error("Set Pendel id=$id as global.");
    $GLOBALS['pendel_id'] = $id;

//    echo "2Pendel page id=$id<br>";
    // All content must stored in subdirectory 'pende/<pende_id> of uploads
    $wpdir = wp_upload_dir();
    $subdir = '/pendel/' . $id;
    trigger_error("Directory=" . $wpdir['path'] . $subdir, E_USER_NOTICE);

    if (!(new ConfigurationFacade($id))->exists()) {
        trigger_error("Pendel: Calling first time after activation, init_config will be called now for id=$id", E_USER_NOTICE);
        init_config(get_the_content());
        $config = get_config();
        init_tiles($wpdir['path'] . $subdir, $config);
    } else {
        trigger_error("Pendel: Configuration exists for id=$id", E_USER_NOTICE);
        $config = get_config();
    }

    hook_body($wpdir['url'] . $subdir);
}

add_action('the_content', 'on_content');

function pendel_on_activation() {
    install();
}

register_activation_hook(__FILE__, 'pendel_on_activation');

function pendel_on_deactivation() {
    deinstall();
}

register_deactivation_hook(__FILE__, 'pendel_on_deactivation');

/**
 * Enqueue plugin style-file
 */
function pendel_add_my_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style('pendel-style', plugins_url('style.css', __FILE__));
    wp_enqueue_style('pendel-style');

//    wp_enqueue_style('leaflet-style', 'https://unpkg.com/leaflet@1.2.0/dist/leaflet.css');
}

function pendel_add_my_script() {
    wp_register_script('pendel-script', plugins_url('pendel.js', __FILE__), array('jquery'));
    wp_enqueue_script('pendel-script');

//    wp_enqueue_script('leaflet-script', 'https://unpkg.com/leaflet@1.2.0/dist/leaflet.js', false);
}

/**
 * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
 */
add_action('wp_enqueue_scripts', 'pendel_add_my_stylesheet');
add_action('wp_enqueue_scripts', 'pendel_add_my_script');


// enqueue and localise scripts
wp_localize_script('my-ajax-handle', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
// THE AJAX ADD ACTIONS
add_action('wp_ajax_the_ajax_hook', 'the_action_function');
add_action('wp_ajax_nopriv_the_ajax_hook', 'the_action_function'); // need this to serve non logged in users
// THE FUNCTION

function the_action_function() {

    $nextNr = $_REQUEST["nextNr"];
    echo "Received nextNr=$nextNr";

    die(); // wordpress may print out a spurious zero without this - can be particularly bad if using json
}
