<?php

function getAllCards($userid) {
	
    $postSlugs = array();
    $postsIDS = array();
	
	$nlPostIDS = array();
	$plPostIDS = array();
	$newPostIDS = array();
	
    // $page = get_page_by_title( 'impulse', $output, 'flashcard' );
    $words = get_user_meta($userid, 'words', true);
    if ($words === "" || !$words) { $words = array(); }
    foreach ($words as $key => $value) {
        reset($value);
        if ($value[key($value)] !==   '3' ) {
            array_push( $postSlugs,   key($value)  );
        }
    }
    foreach ($postSlugs as $key => $name) {
        $val = strtolower(trim($name));
        $post = get_page_by_title( $val, $output, 'flashcard' );
        if( $post->ID ) {
			$stat = getWordStats(strtolower(trim($name)), $userid);
			if ($stat === '1') {
				array_push($nlPostIDS, $post->ID);
			} else if ( $stat === '2' ) {
				array_push($plPostIDS, $post->ID);
			} else if ($stat === null) {
				array_push($newPostIDS, $post->ID);
			}
			
            array_push($postsIDS, $post->ID);
        }
         // array_push($postsIDS, $name);
    }

        $max = get_user_meta($userid, 'no_cards', true) + 1;
        if ($max === null)  $max = get_option( 'no_cards') + 1;
        $total = count($nlPostIDS) + count($plPostIDS) + count($newPostIDS);

        if($max > $total) $max = $total;

        $numberNL = round(  pctx( count($nlPostIDS), $total ) ) * $max / 100;
        $numberPL = round( pctx( count($plPostIDS), $total ) )* $max / 100;
        $numberN = round( pctx( count($newPostIDS), $total ) )* $max / 100;

        $nLearnedArr =  array();
        $pLearnedArr = array();
        $newWordsArr = array();

        if (  $numberNL > 0 ) {
            $nLearnedArr = picRandom( $numberNL,  $nlPostIDS);
        }

        if (  $numberPL > 0 ) {
            $pLearnedArr = picRandom( $numberPL, $plPostIDS);
        }

        if (  $numberN > 0 ) {
            $newWordsArr = picRandom( $numberN, $newPostIDS);
        }

        $all = array_merge((array)$nLearnedArr, (array)$pLearnedArr, (array)$newWordsArr);

        // var_dump($all);

    return $all;
}


function pctx($n, $total) {
    return ($n * 100) / $total ;
}

function picRandom( $n, $arr){
    $tmp = array();
	// pic n words from arr
    $keys = array_rand($arr, $n);
    if ( count($keys) > 1 ) {
        foreach ($keys as $key => $value) {
            # code...
            array_push($tmp, $arr[$value] );
        }
    } else {
        array_push($tmp, $arr[0] );

    }
    return $tmp;
}

function getWordStats($wd, $userid) {
    $words = get_user_meta($userid, 'words', true);
    foreach ($words as $key => $value) {
        reset($value);
        if ( trim(strtolower( key($value) )) === trim(strtolower( $wd ) )) {
            $flagset = $value[ key( $value ) ];
        }
    }
    return $flagset;
}

function generateSessionContent($userid){
    // $excludePosts = getAllLearnedCards($userid);
    $includePosts = getAllCards($userid);
    if (!empty($includePosts)){
       
        $colorOptions = array( 
            get_option( 'color_n') ,
            get_option( 'color_p') ,
            get_option( 'color_l') ,
            'rgb(211,211,211);'
        );
        $type = 'flashcard';
        $args = array ( 
         'post_type' => $type,
         'post_status' => 'publish',
         'posts_per_page' => get_option( 'no_cards'),
         'ignore_sticky_posts'=> 1,
         'post__in' => $includePosts    
        );
         $temp = $wp_query; // assign ordinal query to temp variable for later use  
            $wp_query = null;
            $wp_query = new WP_Query($args); 
            echo '<div id="flashcards">';
            if ( $wp_query->have_posts() ) :
                while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" style="display:none;"> 
                            <?php echo('<input type="hidden" class="wordTitle" value="'.get_the_title().'" style="position:absolute;">'); ?>
                            <ul class="flashcard-container">
                               <li style="width: 330px; height: 430px;">
                                    <?php 
                                        $wdstat = getWordStats(get_the_title(), $userid); 
                                        if ( !$wdstat ) {
                                            $wdstat = 4;
                                        }
                                            $cclass = "background-color: ".$colorOptions[ $wdstat - 1 ].";";
                                       
                                    ?>
                                    <div class="fcontainer front" style="width: 330px; height: 430px; <?php echo  $cclass; ?> background-position: initial initial; background-repeat: initial initial;">
                                        <div style="font-size: 20px; color: black;">
                                            <h4 style="margin-top: 38%;"><?php the_title(); ?></h4>
                                        </div>
                                    </div>
                                    <div class="back" style="width: 330px; height: 430px; background-color: black; display: none; left: 0px;">
                                        <div style="font-size: 20px; color: white;">
                                            <h4><?php the_title(); ?> Definition</h4>
                                            <div style="width: 320px; height: 200px; overflow-y: scroll;">
                                                <p> <?php the_content(); ?> </p>
                                            </div>
                                        </div>
                                        <div class="audioFile">
                                            <div class="listen0">
                                                
                                            </div>
                                        </div>
                                     <div style="position: relative;"> 
                                        <label for="not<?php the_ID(); ?>"> <input type="radio" id="not<?php the_ID(); ?>" name="learned" value="1" selected="">Not leaned </label>
                                        <label for="part<?php the_ID(); ?>"> <input type="radio" id="part<?php the_ID(); ?>" name="learned" value="2"> Partially learned</label> 
                                        <label for="ok<?php the_ID(); ?>"> <input id="ok<?php the_ID(); ?>" type="radio" name="learned" value="3"> Learned </label> 
                                    </div>
                                    <input type="button" value="Save" class="save" style="position: relative; bottom: -10px; ">
                                    <!-- <input type="button" value="Next" class="nextCard" style="position: relative; bottom: -10px; "> -->
                                
                                </div>
                            </li>
                        </ul>
                    </article>
                <?php endwhile;
            else :
                echo '<h2>No Words selected</h2>';
            endif;
            $wp_query = $temp;

            echo '<article style="display:none">  <h2>Congratulations! </h2> <h3> Session Ended. </h3> </article> </div>';
        } else {
             echo('<h2>Library empty</h2><h3>Please add some cards to the Library</h3>');
        }

}

if ( ! function_exists( 'flashcards_post_nav' ) ) :
/**
 * Displays navigation to next/previous post when applicable.
*
* @since Twenty Thirteen 1.0
*
* @return void
*/


function flashcards_post_nav() {
    global $post;

    // Don't print empty markup if there's nowhere to navigate.
    $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
    $next     = get_adjacent_post( false, '', false );

    if ( ! $next && ! $previous )
        return;
    ?>
    <nav class="navigation post-navigation" role="navigation">
        <h1 class="screen-reader-text"><?php _e( 'Post navigation', 'twentythirteen' ); ?></h1>
        <div class="nav-links">

            <?php previous_post_link( '%link', _x( '<span class="meta-nav">&larr;</span> %title', 'Previous post link', 'twentythirteen' ) ); ?>
            <?php next_post_link( '%link', _x( '%title <span class="meta-nav">&rarr;</span>', 'Next post link', 'twentythirteen' ) ); ?>

        </div><!-- .nav-links -->
    </nav><!-- .navigation -->
    <?php
}
endif;

if(!class_exists('Post_Type_Template'))
{
    /**
     * A PostTypeTemplate class that provides 3 additional meta fields
     */
    class Post_Type_Template
    {
        const POST_TYPE = "Flashcard";
        private $_meta  = array(
            'meta_a',
            'meta_b',
            'meta_c',
        );
        
        /**
         * The Constructor
         */
        public function __construct()
        {
                // register actions
                add_action('init', array(&$this, 'init'));
                add_action('admin_init', array(&$this, 'admin_init'));
        } // END public function __construct()

        public function add_switch_control($content) {
            return '
            <div id="FlashcardCollection" style="display:block;"><span class="arrow-w arrow"></span><div class="ftitle">Flashcard Collection </div>  <div> <p id="WordCollection"> </p></div></div>'.$content;
        }


        public function registerFlashcardScripts() {
            wp_enqueue_script(
                'jq',
                plugins_url('../js/jquery.js',__file__),
                null,
                '',
                true
            );
            wp_enqueue_script(
                'jq-transform',
                plugins_url('../js/jquery-css-transform.js',__file__),
                null,
                array('jq'),
                true
            );
            wp_enqueue_script(
                'flashcard-popcorn',
                plugins_url('../js/popcorn-complete.js',__file__),
                null,
                array('jq'),
                true
            );
            wp_enqueue_script(
                'rotate3Di',
                plugins_url('../js/rotate3Di.js',__file__),
                null,
                array('jq' ),
                true
            );

        wp_enqueue_script(
                'flashcardInit',
                plugins_url('../js/flashcard.js',__file__),
                null,
                array('jq', 'flashcard-popcorn', 'jq-transform', 'rotate3Di'),
                true
            );
        wp_enqueue_style( 'fcards', plugins_url('../css/flashcard.css',__file__) );

        }

                

        /**
         * hook into WP's init action hook
         */
        public function init()
        {
            // Initialize Post Type
            $this->create_post_type();
            add_action('save_post', array(&$this, 'save_post'));   
            add_action('wp_enqueue_scripts', array(&$this, 'registerFlashcardScripts') );
            // add_filter( 'the_content', array(&$this,'add_switch_control'), 6); 

        } // END public function init()

        /**
         * Create the post type
         */
        public function create_post_type()
        {
            register_post_type(self::POST_TYPE,
                array(
                    'labels' => array(
                        'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", self::POST_TYPE)))),
                        'singular_name' => __(ucwords(str_replace("_", " ", self::POST_TYPE)))
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'description' => __("This is a sample post type meant only to illustrate a preferred structure of plugin development"),
                    'supports' => array(
                        'title', 'editor', 'excerpt', 
                    ),
                )
            );
        }
    
        /**
         * Save the metaboxes for this custom post type
         */
        public function save_post($post_id)
        {
            // verify if this is an auto save routine. 
            // If it is our form has not been submitted, so we dont want to do anything
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            {
                return;
            }
            
            if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
            {
                foreach($this->_meta as $field_name)
                {
                    // Update the post's meta field
                    update_post_meta($post_id, $field_name, $_POST[$field_name]);
                }
            }
            else
            {
                return;
            } // if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
        } // END public function save_post($post_id)

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {           
            // Add metaboxes
            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
        } // END public function admin_init()
            
        /**
         * hook into WP's add_meta_boxes action hook
         */
        public function add_meta_boxes()
        {
            // Add this metabox to every selected post
            add_meta_box( 
                sprintf('wp_plugin_template_%s_section', self::POST_TYPE),
                sprintf('%s Information', ucwords(str_replace("_", " ", self::POST_TYPE))),
                array(&$this, 'add_inner_meta_boxes'),
                self::POST_TYPE
            );                  
        } // END public function add_meta_boxes()

        /**
         * called off of the add meta box
         */     
        public function add_inner_meta_boxes($post)
        {       
            // Render the job order metabox
            include(sprintf("%s/../templates/%s_metabox.php", dirname(__FILE__), self::POST_TYPE));         
        } // END public function add_inner_meta_boxes($post)

    } // END class Post_Type_Template
} // END if(!class_exists('Post_Type_Template'))