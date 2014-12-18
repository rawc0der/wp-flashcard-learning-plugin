<?php
if(!class_exists('WP_Plugin_Template_Settings'))
{
    class WP_Plugin_Template_Settings
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // register actions
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('admin_menu', array(&$this, 'add_menu'));

            // add_action( 'wp_ajax_nopriv_ajax-addWord', array(&$this,'myajax_addWord_func') );
            add_action( 'wp_ajax_ajax-addWord', array(&$this,'myajax_addWord_func') );

            // add_action( 'wp_ajax_nopriv_ajax-removeWord', array(&$this,'myajax_removeWord_func') );
            add_action( 'wp_ajax_ajax-removeWord', array(&$this,'myajax_removeWord_func') );

            add_action( 'wp_ajax_ajax-getWords', array(&$this,'myajax_getWords_func') );

            add_action( 'wp_ajax_ajax-deleteLibrary', array(&$this,'myajax_deleteLibrary_func') );
            add_action( 'wp_ajax_ajax-updateWord', array(&$this,'myajax_updateWord_func') );
            add_action( 'wp_ajax_ajax-changeNo', array(&$this,'myajax_changeNo_func') );



            add_action( "admin_head", array(&$this,'localize_flashcard_collection_vars') );
            add_action( "wp_enqueue_scripts", array(&$this,'localize_flashcard_collection_vars') );

        } // END public function __construct
        
        public function myajax_getWords_func(){
            $nonce = $_POST['nextNonce'];   
            if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) )
                die ( 'Busted!');
            $userid = $_POST['userID'];           
            $userMeta = get_user_meta($userid, 'words', true);
            $response = json_encode( array( 'meta'=> $userMeta ) );
            // response output
            header( "Content-Type: application/json" );
            die($response);
        }

        public function myajax_deleteLibrary_func(){
            $nonce = $_POST['nextNonce'];   
            if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) )
                die ( 'Busted!');
            $userid = $_POST['userID'];         
            delete_user_meta($userid, 'words');
            $response = json_encode( array( 'meta'=> get_user_meta($userid, 'words', true) ) );
            // response output
            header( "Content-Type: application/json" );
            die($response);
        }

        public function myajax_addWord_func(){
            $nonce = $_POST['nextNonce'];   
            if (! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) )
                die ( 'Busted!');
            $userid = $_POST['userID'];                
            $word = $_POST['word'];
            $words = get_user_meta($userid, 'words', true);
            if ($words === "") {
                add_user_meta(array(), 'words', true);
                $words = array();
            }
            foreach ($words as $key => $value) {
                reset($value);
                if ( trim(strtolower( key($value) )) === trim(strtolower( $word ) )) {
                    $keyexists = true;
                    $stat = $value[ key($value) ] ;
                }
            }
            if (! $keyexists) {
                array_push($words, array(strtolower( $word ) => null));
                $userMeta = update_user_meta($userid, 'words', $words);
            } else {
                if ($stat === null) $stat = "new";
                $userMeta = $stat;
            }
            $response = json_encode( array( 'meta'=> $userMeta ) ); 
            // response output
            header( "Content-Type: application/json" );
            die($response);
        }

      
        public function myajax_updateWord_func(){
            $nonce = $_POST['nextNonce'];   
            if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) )
                die ( 'Busted!');
            $userid = $_POST['userID'];                
            $word = $_POST['word'];
            $flag = $_POST['flag'];
            $words = get_user_meta($userid, 'words', true);
            if ($words === "" || !$words) {
                add_user_meta(array(), 'words', true);
                $words = array();
            }
            foreach ($words as $key => &$value) {
                reset($value);
                if ( trim(strtolower( key($value) )) === trim(strtolower( $word ) )) {
					$value[ key($value) ] = $flag;
                    $flagset = true;
                }
            }
            if ( $flagset ) {
                $userMeta = update_user_meta($userid, 'words', $words);
            }
            $response = json_encode( array( 'meta'=> $userMeta ) ); 
            // response output
            header( "Content-Type: application/json" );
            die($response);
        }

        public function myajax_changeNo_func(){
            $nonce = $_POST['nextNonce'];   
            if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) )
                die ( 'Busted!');
            $userid = $_POST['userID'];                
            $no = $_POST['no'];
            $userMeta = update_user_meta($userid, 'no_cards', $no);
            $response = json_encode( array( 'meta'=> $userMeta ) ); 
            // response output
            header( "Content-Type: application/json" );
            die($response);
        }

        public function myajax_removeWord_func(){
            $nonce = $_POST['nextNonce'];   
            if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) )
                die ( 'Busted!');
            $userid = $_POST['userID'];                
            $word = $_POST['word'];
            $words = get_user_meta($userid, 'words', true);
            foreach ($words as $key => $value) {
                reset($value);
                if ( trim(strtolower( key($value) )) === trim(strtolower( $word ) )) {
                    unset( $words[$key] );
                    $userMeta = update_user_meta($userid, 'words', $words);
                }
            }
            $response = json_encode( array( 'meta'=> $userMeta ) );
            // response output
            header( "Content-Type: application/json" );
            die($response);
        }

        public function localize_flashcard_collection_vars() 
        {
            $admin_url = admin_url( 'admin-ajax.php' );
            $nonce =  wp_create_nonce( 'myajax-next-nonce' );
            $userid = get_current_user_id();
            ?>
                <script type='text/javascript'>
                    window.FC_Ajax = {
                        'ajaxurl'   : '<?php echo $admin_url; ?>' ,
                        'nextNonce'    : '<?php echo $nonce; ?>',
                        'userID' : '<?php echo $userid; ?>'
                    };
                </script>
            <?php
        } 

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // register your plugin's settings
            register_setting('wp_plugin_template-group', 'no_cards');
            register_setting('wp_plugin_template-group', 'color_n');
            register_setting('wp_plugin_template-group', 'color_p');
            register_setting('wp_plugin_template-group', 'color_l');
            wp_enqueue_script(
                'flashcardAdmin',
                plugins_url('js/flashcardCollection.js',__file__),
                null,
                array('jquery'),
                true
            );
            wp_enqueue_style( 'fcards', plugins_url('css/flashcard.css',__file__) );
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'flashcard-colors', plugins_url('flashcard-colors.js', __FILE__ ), array(  'wp-color-picker', 'jQuery' ), false, true );
            // add your settings section
            add_settings_section(
                'wp_plugin_template-section', 
                'Flashcards Settings', 
                array(&$this, 'settings_section_wp_plugin_template'), 
                'wp_plugin_template'
            );
            
            // add your setting's fields
            add_settings_field(
                'wp_plugin_template-setting_a', 
                'Number of cards per Session', 
                array(&$this, 'settings_field_input_text'), 
                'wp_plugin_template', 
                'wp_plugin_template-section',
                array(
                    'field' => 'no_cards'
                )
            );
            add_settings_field(
                'wp_plugin_template-setting_b', 
                'Not Learned Color', 
                array(&$this, 'settings_field_colors'), 
                'wp_plugin_template', 
                'wp_plugin_template-section',
                array(
                    'field' => 'color_n'
                )
            );
            add_settings_field(
                'wp_plugin_template-setting_c', 
                'Partially Learned Color', 
                array(&$this, 'settings_field_colors'), 
                'wp_plugin_template', 
                'wp_plugin_template-section',
                array(
                    'field' => 'color_p'
                )
            );
            add_settings_field(
                'wp_plugin_template-setting_d', 
                'Fully Learned Color', 
                array(&$this, 'settings_field_colors'), 
                'wp_plugin_template', 
                'wp_plugin_template-section',
                array(
                    'field' => 'color_l'
                )
            );
            // Possibly do additional admin_init tasks
        } // END public static function activate
        
        public function settings_section_wp_plugin_template()
        {
            // Think of this as help text for the section.
            echo 'Set the default flashcards settings';
        }
     
        public function settings_field_colors($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" class="my-color-field" />', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        

        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        
        /**
         * add a menu
         */     
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
            add_options_page(
                'Flashcards Settings', 
                'Flashcards Learing Session', 
                'read', 
                'wp_plugin_template', 
                array(&$this, 'plugin_settings_page')
            );
        } // END public function add_menu()
    
        /**
         * Menu Callback
         */     
        public function plugin_settings_page()
        {
            if(!current_user_can('read'))
            {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
    
            // Render the settings template
            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class WP_Plugin_Template_Settings
} // END if(!class_exists('WP_Plugin_Template_Settings'))
