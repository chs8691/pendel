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
 * Declaration of URI parameter to bebuild the database. The refreshcode value
 * has to be set in the pendel configuration string. E.g. if the pendel page is
 * configures with
 * [pendel: id="ffm" x="4000", ...., refreshcode="123"]
 * Then the url to rebuild all tiles is
 * http://127.0.0.1:8080/wordpress/sample-page/?refreshcode=123
 * This is not very save, because the request parameter can be sniffed easily.
 * But for this purposes I think it's ok.
 *  */
add_filter('query_vars', 'parameter_queryvars');

function parameter_queryvars($qvars) {
    $qvars[] = 'refreshcode';
    return $qvars;
}

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

    //----- Request for refreshing tiles
    $codeact = filter_input(INPUT_GET, 'refreshcode', FILTER_SANITIZE_SPECIAL_CHARS);
    if ($codeact != NULL && $codeact != FALSE) {
        $codeexp = extract_refreshcode(get_the_content());
        if ($codeact == $codeexp) {
            echo "Refresh will be done now...<br>";
            install();
            echo "Done! You can close this page now.";
        } else {
            echo "Unsupported query";
        }
        return;
    }

    //----- Normal page call
    $id = extract_pendel_id(get_the_content());
    if ($id == false or strlen($id) == 0) {
        log_error("Leaving pendel because of missing id.");
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
    wp_register_style('pendel-style', plugins_url('pendel-style.css', __FILE__));
    wp_enqueue_style('pendel-style');

//    wp_enqueue_style('leaflet-style', 'https://unpkg.com/leaflet@1.2.0/dist/leaflet.css');
}

function pendel_add_my_script() {
    wp_register_script('pendel-script', plugins_url('pendel-script.js', __FILE__), array('jquery'));
    wp_enqueue_script('pendel-script');

    // Add JS object with constant values for the given JS file, so they can be accessed
    // within the script, e.g. var url = pendel_vars.ajaxurl
    // Original usage is for text localization.
//  THE FUNCTION: 1 - js script handle name, 2 - JS object name for accessing array values
    wp_localize_script('pendel-script', 'pendel_vars', array(
        // URL to wp-admin/admin-ajax.php to process the request
        'ajaxurl' => admin_url('admin-ajax.php'),
            // generate a nonce with a unique ID "myajax-post-comment-nonce"
            // so that you can check it later when an AJAX request is sent
//        'security' => wp_create_nonce('my-special-string')
    ));

    //Touchscreen lib
//    wp_register_script('pendel-touchswipe', plugins_url('jquery.touchSwipe.min.js', __FILE__));
//    wp_enqueue_script('pendel-touchswipe');
//    wp_enqueue_script('leaflet-script', 'https://unpkg.com/leaflet@1.2.0/dist/leaflet.js', false);
}

/**
 * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
 */
add_action('wp_enqueue_scripts', 'pendel_add_my_stylesheet');
add_action('wp_enqueue_scripts', 'pendel_add_my_script');

//https://benmarshall.me/wordpress-ajax-frontend-backend/
if (is_admin()) {
// THE AJAX ADD ACTIONS. Must start with wp_ajax and wp_ajax_nopriv, followed by '_"
// and then the action name, used in the POST data of the AJAX call in JS
    add_action('wp_ajax_pendel_paging', 'on_pendel_paging');
    add_action('wp_ajax_nopriv_pendel_paging', 'on_pendel_paging'); // need this to serve non logged in users
} else {
    // Add non-Ajax front-end action hooks here
}

/**
 * This will be called bu AJAX request for getting tiles configuration
 */
function on_get_tiles_configuration() {
    error_log("on_get_tiles_configuration()");
//    check_ajax_referer('my-special-string', 'security');
    $pendel_id = filter_input(INPUT_POST, "pendelId");
    error_log("pendelId=$pendel_id");
    // Needed for database class.
    $GLOBALS['pendel_id'] = $pendel_id;

    $ret = get_tiles_status($next_nr);
    error_log("Response json=$ret");
    echo($ret);

    die(); // wordpress may print out a spurious zero without this - can be particularly bad if using json
}

/**
 * This will be called bu AJAX request for scrolling
 */
function on_pendel_paging() {
    error_log("on_pendel_paging()");
//    check_ajax_referer('my-special-string', 'security');
    $next_nr = filter_input(INPUT_POST, "nextNr");
    $pendel_id = filter_input(INPUT_POST, "pendelId");
    error_log("Requested nextNr=$next_nr, pendelId=$pendel_id");
    // Needed for database class.
    $GLOBALS['pendel_id'] = $pendel_id;

    $ret = get_tiles_status($next_nr);
    error_log("Response json=$ret");
    echo($ret);

    die(); // wordpress may print out a spurious zero without this - can be particularly bad if using json
}
