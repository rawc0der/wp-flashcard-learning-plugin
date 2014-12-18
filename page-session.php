<?php
/*
Template Name: Learning session
*/

get_header(); ?>
<div id="LearningSession">
   <?php generateSessionContent( get_current_user_id( ) )?>
</div>

<?php get_footer(); ?>