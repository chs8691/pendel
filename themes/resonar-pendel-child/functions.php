<?php

function my_theme_enqueue_styles() {

    $parent = 'resonar-style';
    $stylesheet_uri = get_stylesheet_directory_uri();

    //get_template_directory_uri returns the path to the parent theme
    // first parameters seems to be just a name.
    $template_dir = get_template_directory_uri();
    error_log("get_template_directory_uri=$template_dir");
    error_log("stylesheet_uri=$stylesheet_uri");
    wp_enqueue_style($parent, $template_dir . '/style.css');
    wp_enqueue_style('pendel-child-style', $stylesheet_uri . '/style.css', array($parent), wp_get_theme()->get('Version'));
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

/*
 * Disable google fonts
 */

function wpse_dequeue_google_fonts() {
    wp_dequeue_style('twentyfifteen-fonts');
}

add_action('wp_enqueue_scripts', 'wpse_dequeue_google_fonts', 20);
?>
