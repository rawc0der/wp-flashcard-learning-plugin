<?php
/**
 * The Template for displaying single flashcard post-type.
 *
 */

get_header(); ?>

    <div id="primary" class="content-area">
        <div id="content" class="site-content" role="main">

            <?php /* The loop */ ?>
            <?php while ( have_posts() ) : the_post(); ?>

<article id="post-<?php the_ID(); ?>">
        <div id="flashcards">
          <?php echo('<input type="hidden" id="wordTitle" value="'.get_the_title().'" style="position:absolute;">'); ?>

        <ul class="flashcard-container">
           <li style="width: 330px; height: 430px;">
                <div class="fcontainer front" style="width: 330px; height: 430px; background-color: rgb(211, 211, 211); background-position: initial initial; background-repeat: initial initial;">
                    <div style="font-size: 20px; color: black;">
                        <h4 style="margin-top: 38%;"><?php the_title(); ?></h4>
                    </div>
                </div>
                <div class="back" style="width: 330px; height: 430px; background-color: black; display: none; left: 0px;">
                    <div style="font-size: 20px; color: white;">
                        <h4><?php the_title(); ?> Definition</h4>
                        <div id="cbar" style="width: 320px; height: 300px; overflow-y: scroll;">
                            <p> <?php the_content(); ?> </p>
                        </div>
                    </div>
                    <div class="audioFile">
                        <div id="listen0">
                            
                        </div>
                    </div>
                </div>
        </li>
    </ul>
        </div>
</article><!-- #post -->

                              
                                <?php flashcards_post_nav(); ?>
                <?php comments_template(); ?>

            <?php endwhile; ?>

                        <?php echo('<input type="hidden" id="userID" value="'.get_current_user_id().'">'); ?>
        </div><!-- #content -->
    </div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>