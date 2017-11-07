<?php
/**
 * Template Name: Full Width
 *
 * Description: A custom template for displaying a fullwidth layout with no sidebar.
 *
 * @package Twenty Seventeen Child
 */
//get_header();
?>

<div class="wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <?php
            while (have_posts()) : the_post();
                get_template_part('template-parts/page/content', 'page');
                // If comments are open or we have at least one comment, load up the comment template.
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
            endwhile; // End of the loop.
            ?>

        </main><!-- #main -->
    </div><!-- #primary -->
</div><!-- .wrap -->

<?php
//get_footer();
