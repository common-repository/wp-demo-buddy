<?php 
if ( ! defined( 'ABSPATH' ) ) exit; 
class DemoBuddy {
		protected $plugin_slug = 'DemoBuddy';
        protected static $instance = null;

	   function __construct() {
            register_activation_hook( DemoBuddy6_PLUGIN_NAME, array( $this, 'create_uploads_folder' ) );
            add_filter('widget_text','do_shortcode');            
			add_action( 'plugins_loaded', array($this, 'DemoBuddy_load_textdomain'));
			add_action( 'admin_menu', array($this, 'add_plugin_admin_menu'));
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
            add_action( 'wp_enqueue_scripts', array($this, 'enqueue_front_styles' ));
            add_action( 'wp_enqueue_scripts', array($this, 'enqueue_front_scripts' ));
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
 		     //ADMIN AJAX////////////
            add_action( 'wp_ajax_New_Demo_Product',  array($this,'New_Demo_Product'));		
            add_shortcode( 'DEMOBUDDY', array($this,'demobuddy_shortcode') );
            add_action('wp', array($this,'shortcode_block_access_redirect'));
            
            if($_POST['demobuddy_form_id'] == 'demobuddy_form_5812')
                add_action('wp', array($this, 'create_demo'));
                
            if($_GET['dEMoBuDdYCron'] == 'dEMoBuDdYCron')
                add_action('plugins_loaded', array($this, 'run_cron'));
                
            add_filter( 'manage_users_columns', array($this,'demobuddy_modify_user_table' ));
            add_filter( 'manage_users_custom_column', array($this,'demobuddy_modify_user_table_row'), 10, 3);    
        }
        function run_cron()
        {
            include_once DemoBuddy6_PLUGIN_DIR.'/admin/model/cron.php';
            $cron = new demobuddy_demo_delexp;
            exit;
        }
        function create_uploads_folder()
        {
            $upload = wp_upload_dir();
            $dir['upload_dir'] = $upload['basedir']. '/demobuddy/';
            $dir['errlog_dir'] = $upload['basedir']. '/demobuddy/error/';
            $dir['temp_dir'] = $upload['basedir']. '/demobuddy/temp/';
            
            
            foreach($dir as  $k => $d)
            {
               if (! is_dir($d)) 
                    mkdir( $d, 0755 );
               if(!file_exists($d.'index.php'))    
                    file_put_contents($d.'index.php',"");            
            
                update_option('wp_demo_buddy_'.$k,$d);
            }
            return;
        }
        function shortcode_block_access_redirect()
        {
            global $post, $wpdb;
            if(trim($_GET['id'] =='') ) return;
            $q = "SELECT  block_access FROM ".$wpdb->prefix."demobuddy_instances 
                    WHERE uniq = '".trim($_GET['id'])."'";
            $block_access = $wpdb->get_var($q);
            
            if($block_access == '1') 
            {
                wp_redirect(get_permalink($post->ID));
                exit;
            }
            return;
        }        
        function demobuddy_modify_user_table( $column ) {
            $column['DemoDuck'] = 'Demo Duck';
            return $column;
        }
        function demobuddy_modify_user_table_row( $val, $column_name, $user_id ) {
            switch ($column_name) 
            {
                case 'DemoDuck' :
                    $url = '<a style="padding-left:25px" href="'.admin_url().'admin.php?page=DemoBuddy_userlog&id='.$user_id.'" class="iconbrdr"><img title="'. __('Demo Log & Chart', 'DemoBuddy').'" src="'.DemoBuddy6_PLUGIN_URL.'/assets/staticon.png" /></a>';
                    return $url;
                    break;
                default:
            }
            return $val;
        }
                
        function create_demo()
        {
            if (! isset( $_POST['demobuddy_createdemo_nonce_field'] )  || 
               ! wp_verify_nonce( $_POST['demobuddy_createdemo_nonce_field'], 'demobuddy_createdemo_nonce_action' ) ) 
            {
            
               print __('Sorry, your nonce did not verify.', 'DemoBuddy');
               exit;
            
            }
            global $post;
            
            $gensettings = json_decode(get_option('demobuddy_gensettings'));  
            if(trim($gensettings->secretkey) != '' && trim($gensettings->sitekey) != '')
            {
                $body = array( 'secret' => $gensettings->secretkey, 'response' => $_POST['g-recaptcha-response'] );
 
                $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
                                                        	'method' => 'POST',
                                                        	'timeout' => 45,
                                                        	'redirection' => 5,
                                                        	'httpversion' => '1.0',
                                                        	'blocking' => true,
                                                        	'headers' => array(),
                                                        	'body' => $body,
                                                        	'cookies' => array()
                                                            )
                                                        );
                $resbody = wp_remote_retrieve_body($response);
                $responseData = json_decode($resbody);
     
                if(!$responseData->success)
                {
                   $url = get_permalink($post->ID);
                    wp_redirect($url); 
                    exit;
                }                
            }

            require_once DemoBuddy6_PLUGIN_DIR.'/front/model/create_demo.php';
            $demobuddy_demo = new demobuddy_demo();
            $id = $demobuddy_demo->create_demo($_POST);
            $url = get_permalink($post->ID).'?id='.$id;
            wp_redirect($url);
            exit;
        }
        
        function demobuddy_shortcode( $atts)
        {
            $a = shortcode_atts( array(
                                        'id' => ''
                                    ), $atts );
                                    
            if($a['id'] == '') return;
            
            include_once DemoBuddy6_PLUGIN_DIR.'/front/model/shorcode_form.php';
            return do_shortcode($out);
        }   
        
        function New_Demo_Product() 
        {
        	require_once DemoBuddy6_PLUGIN_DIR.'/admin/model/admin-model.php';
            $adminmodel = new DemoBuddy_admin_model();
            echo $adminmodel->demo_product_formcode($_POST['id']);        
        	exit();  
        }
        

		function DemoBuddy_load_textdomain() {
			load_plugin_textdomain($this->plugin_slug, false, dirname(plugin_basename(__file__)) . '/lang/');
		}
        function enqueue_front_styles()
        {
            wp_enqueue_style( 'DemoBuddy-fcss',  plugins_url('assets/css/front.css', __file__), false ); 
        }

        function enqueue_front_scripts()
        {
            wp_enqueue_script( 'DemoBuddy-fjs',  plugins_url('assets/js/front.js', __file__) , false );
            wp_enqueue_script( 'DemoBuddy-googrecaptchjs',  '//www.google.com/recaptcha/api.js' , false );
        }
		function enqueue_admin_styles() {
			$allowed = array('DemoBuddy_Products','DemoBuddy_Settings','DemoBuddy_userlog', 'DemoBuddy_productlog','DemoBuddy_formstyle');
            
			if (!in_array($_REQUEST['page'], $allowed))	return;
			
			wp_enqueue_style($this->plugin_slug . '-admin-styles', plugins_url('assets/css/bootstrap.min.css', __file__), $this->version);
         //   wp_enqueue_style($this->plugin_slug . '-admin-styles4', '//fortawesome.github.io/Font-Awesome/3.2.1/assets/font-awesome/css/font-awesome.css', $this->version);
            wp_enqueue_style($this->plugin_slug . '-admin-styles2', plugins_url('assets/css/admin.css', __file__), $this->version);
                
        //        wp_enqueue_style($this->plugin_slug . '-lcswitch', plugins_url('assets/css/lc_switch.css', __file__), $this->				version);
		}
        
		function enqueue_admin_scripts() {
			$allowed = array('DemoBuddy_Products','DemoBuddy_Settings','DemoBuddy_userlog', 'DemoBuddy_productlog','DemoBuddy_formstyle' );
			if (!in_array($_REQUEST['page'], $allowed))	return;
                     
            wp_register_script($this->plugin_slug . '-bootstrapjs', plugins_url('assets/js/bootstrap.min.js', __file__), array('jquery'), $this->version, true);
            wp_enqueue_script($this->plugin_slug . '-bootstrapjs');           
            wp_register_script($this->plugin_slug . '-bootstrapconf',  plugins_url('assets/js/bootstrap-confirmation.js', __file__), array('jquery'), $this->version, true);
            wp_enqueue_script($this->plugin_slug . '-bootstrapconf');  
             wp_register_script($this->plugin_slug . '-chartjs', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_slug . '-chartjs'); 

			wp_register_script($this->plugin_slug . '-admin', plugins_url('assets/js/admin.js', __file__), array('wp-color-picker', 'jquery'), $this->version, true);
			wp_localize_script($this->plugin_slug . '-admin', 'Ob10n', array(
                'DemoBuddy6_url' =>  plugins_url('', __file__),
				'Saved_Successfully' => __('Saved Successfully', 'DemoBuddy')
				));
			wp_enqueue_script($this->plugin_slug . '-admin');
		}
        function add_plugin_admin_menu() {
		 add_menu_page('Demo Buddy', 'Demo Buddy', 'manage_options', 'DemoBuddy', array($this, 'Products'),  DemoBuddy6_PLUGIN_URL.'/assets/demobuddyicon.jpg' );
	//	 add_submenu_page( 'DemoBuddy', '', '', 'manage_options',  'DemoBuddy');		 
         add_submenu_page('DemoBuddy', 'Products', 'Products', 'manage_options', 'DemoBuddy_Products',  array($this, 'Products'));
		 add_submenu_page('DemoBuddy', 'Settings', 'Settings', 'manage_options', 'DemoBuddy_Settings',  array($this, 'Settings'));
         add_submenu_page('Products', 'Product Log', 'Product Log', 'manage_options', 'DemoBuddy_productlog',  array($this, 'Product_Log'));
         remove_submenu_page('DemoBuddy', 'DemoBuddy');
   }
 
 
 
      
   
	function Products()  {
		  include_once (DemoBuddy6_PLUGIN_DIR.'/admin/views/Products.php'); 
		 }
	function Settings()  {
		  include_once (DemoBuddy6_PLUGIN_DIR.'/admin/views/Settings.php'); 
		 }

        function get_instance() {
			if (null == self::$instance) {
				self::$instance = new self;
			}
			return self::$instance;
		}
    
	} 
    

    ?>