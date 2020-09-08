<?php
/*
Plugin Name: User Verification
Plugin URI: http://pickplugins.com
Description: Verify user before access on your website.
Version: 1.0.43
Text Domain: user-verification
Domain Path: /languages
Author: PickPlugins
Author URI: http://pickplugins.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 


class UserVerification{
	
	public function __construct(){
	
		$this->uv_define_constants();

        $this->uv_loading_functions();
		//$this->uv_declare_classes();
		$this->uv_declare_actions();
		$this->uv_loading_script();
        add_action( 'init', array( $this, 'uv_declare_classes' ));


		add_action( 'init', array( $this, '_textdomain' ));
        register_activation_hook( __FILE__, array( $this, '_activation' ) );
        register_deactivation_hook(__FILE__, array($this, '_deactivation'));
        //add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );

	}


	public function _textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'user-verification' );
		load_textdomain('user-verification', WP_LANG_DIR .'/user-verification/user-verification-'. $locale .'.mo' );

		load_plugin_textdomain( 'user-verification', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

    function cron_schedules( $schedules ){

        $schedules['1minute'] = array(
            'interval'  => 60,
            'display'   => __( '1 Minute', 'user-verification' )
        );

        $schedules['5minute'] = array(
            'interval'  => 300,
            'display'   => __( '5 Minute', 'user-verification' )
        );

        $schedules['10minute'] = array(
            'interval'  => 600,
            'display'   => __( '10 Minute', 'user-verification' )
        );



        return $schedules;
    }


    public function _activation(){ 


        if (!wp_next_scheduled('user_verification_clean_user_meta')) {
            wp_schedule_event(time(), 'daily', 'user_verification_clean_user_meta');
        }


        do_action('user_verification_activation');

    }


    public function _deactivation(){

        wp_clear_scheduled_hook('user_verification_clean_user_meta');


        /*
         * Custom action hook for plugin deactivation.
         * Action hook: license_manager_deactivation
         * */
        do_action('user_verification_deactivation');
    }


	public function uv_loading_functions() {
		
		require_once( user_verification_plugin_dir . 'includes/functions.php');
		require_once( user_verification_plugin_dir . 'includes/functions-woocommerce.php');
		require_once( user_verification_plugin_dir . 'includes/functions-recaptcha.php');
        require_once( user_verification_plugin_dir . 'includes/functions-paid-memberships-pro.php');
        require_once( user_verification_plugin_dir . 'includes/functions-ultimate-member.php');

        require_once( user_verification_plugin_dir . 'includes/functions-buddypress.php');
        require_once( user_verification_plugin_dir . 'includes/3rd-party/functions-memberpress.php');
        require_once( user_verification_plugin_dir . 'includes/functions-cron-hook.php');

	}
	

	public function uv_loading_script() {
	
		add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
		add_action( 'wp_enqueue_scripts', array( $this, 'uv_front_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'uv_admin_scripts' ) );
	}
	
	
	public function uv_declare_actions() {

		require_once( user_verification_plugin_dir . 'includes/actions/action-uv-registration.php');
	}
	
	public function uv_declare_classes() {
        require_once( user_verification_plugin_dir . 'includes/classes/class-manage-verification.php');


        require_once( user_verification_plugin_dir . 'includes/classes/class-emails.php');
		require_once( user_verification_plugin_dir . 'includes/classes/class-settings.php');
        require_once( user_verification_plugin_dir . 'includes/classes/uv-class-column-users.php');
        require_once( user_verification_plugin_dir . 'includes/classes/class-settings-tabs.php');
        require_once( user_verification_plugin_dir . 'includes/settings-hook.php');
        require_once( user_verification_plugin_dir . 'includes/classes/class-admin-notices.php');


    }
	
	public function uv_define_constants() {

        $this->_define('user_verification_plugin_name', __('User Verification','user-verification') );
        $this->_define('user_verification_plugin_url', plugins_url('/', __FILE__)  );
        $this->_define('user_verification_plugin_dir', plugin_dir_path( __FILE__ ) );



    }
	
	private function _define( $name, $value ) {
		if( $name && $value )
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
		
	public function uv_front_scripts(){
		
		wp_enqueue_script('jquery');

		//wp_enqueue_script('uv_front_js', plugins_url( '/assets/front/js/scripts.js' , __FILE__ ) , array( 'jquery' ));
		//wp_localize_script( 'uv_front_js', 'uv_ajax', array( 'uv_ajaxurl' => admin_url( 'admin-ajax.php')));

        wp_register_style('user_verification', user_verification_plugin_url.'assets/front/css/style.css');

        //global
        wp_register_style('font-awesome-4', user_verification_plugin_url.'assets/global/css/font-awesome-4.css');
        wp_register_style('font-awesome-5', user_verification_plugin_url.'assets/global/css/font-awesome-5.css');
	}

	public function uv_admin_scripts(){

        $screen = get_current_screen();

        wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-accordion');
		
		wp_enqueue_script('uv_admin_js', plugins_url( '/assets/admin/js/scripts.js' , __FILE__ ) , array( 'jquery' ));
		wp_localize_script( 'uv_admin_js', 'uv_ajax', array( 'uv_ajaxurl' => admin_url( 'admin-ajax.php')));
		wp_localize_script( 'uv_admin_js', 'L10n_user_verification', array(
			'confirm_text' => __( 'Are you sure?', 'user-verification' ),
			'reset_confirm_text' => __( 'Do you really want to reset?', 'user-verification' ),
			'mark_as_verified' => __( 'Mark as verified', 'user-verification' ),
			'mark_as_unverified' => __( 'Mark as unverified', 'user-verification' ),
			'updating' => __( 'Updating user', 'user-verification' ),
		));
							
		wp_enqueue_style('uv_admin_style', user_verification_plugin_url.'assets/admin/css/style.css');
        wp_enqueue_style('jquery-ui', user_verification_plugin_url.'assets/global/css/jquery-ui.css');

        wp_register_style('font-awesome-4', user_verification_plugin_url.'assets/global/css/font-awesome-4.css');
        wp_register_style('font-awesome-5', user_verification_plugin_url.'assets/global/css/font-awesome-5.css');

        wp_register_style('settings-tabs', user_verification_plugin_url.'assets/settings-tabs/settings-tabs.css');
        wp_register_script('settings-tabs', user_verification_plugin_url.'assets/settings-tabs/settings-tabs.js'  , array( 'jquery' ));

        // Global

        //var_dump($screen);

        if ($screen->id == 'users_page_user_verification'){


            wp_enqueue_style('select2');
            wp_enqueue_script('select2');

            wp_enqueue_style('font-awesome-5');


            $settings_tabs_field = new settings_tabs_field();
            $settings_tabs_field->admin_scripts();
        }

	}
} 

new UserVerification();