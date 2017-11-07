<?php

function my_theme_enqueue_styles() {

    $parent = 'twentyseventeen-style';

    //get_template_directory_uri returns the path to the parent theme
    // first parameters seems to be just a name.
    wp_enqueue_style($parent, get_template_directory_uri() . '/style.css');
    wp_enqueue_style('pendel-child-style', get_stylesheet_directory_uri() . '/style.css', array($parent), wp_get_theme()->get('Version')
    );
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');
?>