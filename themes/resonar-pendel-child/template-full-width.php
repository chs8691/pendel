<?php
/**
 * Template Name: Resonar Pendel Full Width
 *
 * Description: A custom template for displaying a fullwidth layout with no sidebar and header.
 *
 * @package Resonar Child
 */
get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php
        // Start the loop.
        while (have_posts()) : the_post();

            get_template_part('content', 'single');

            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;


        endwhile;
        ?>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php
get_footer();
?>
