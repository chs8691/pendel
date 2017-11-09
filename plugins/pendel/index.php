<?php

/**
 * @package Pendel
 * @version 1.0
 */
/*
  Plugin Name: Pedel Image Wallpaper
  Plugin URI:
  Description: Show Image thumbnails of gps tracked imaged on a wallpaper
  Author: Christian Schulzendorff
  Version: 1.0
  Author URI:
 */

require_once( 'pendel_main.php' );

/*
 * Switch for pendel plugin. Has to be hooked in 'the_content'.
 * If pendel configuration is part of the page, all content will be replaced by
 * the canvas. For all other pages, page content will be returned.
 */

function on_content() {
//    $c = get_the_content();
//    echo "on_content() $c <br>";
//require_once 'pendel_main.php';
//    $configstr = '[pendel: x="4000", y="2000", tile="80", ' .
//            'start_lon="8.615272", start_lat="50.2031599",' .
//            'end_lon="9.1137766", end_lat="50.0544646"]';

    if (!init_config(get_the_content())) {
        return get_the_content();
    }

    $config = get_config();
    $wpdir = wp_upload_dir();
//    echo 'url. ' . $wpdir['path'] . '<br>';
//    echo 'start: ' . $config->id . '<br>';
//$px_x, $px_y, $tile_x, $tile_y, $start_lon, $start_lat, $end_lon, $end_lat
// start: left, top
// end: right, bottom
// (0.0) ---------------------> x, lon
//   |
//   |
//   |
//   |
//   |
//   v                       . (xmax,ymax)
//  y, lat
    init_tiles($wpdir['path'], $config);
//    echo "url " . $wpdir['url'] . " <br>";
//    echo "baseurl " . $wpdir['baseurl'] . " <br>";
    hook_body($wpdir['url']);
}

add_action('the_content', 'on_content');

