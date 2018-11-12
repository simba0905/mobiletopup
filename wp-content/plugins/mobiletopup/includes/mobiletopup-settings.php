<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class mobileTopUpSetting {


	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */

	public $dir;
	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct (  ) {
		$this->_version = ' 1.0.0';

		$this->file = '';
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'mobiletopup/assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( 'mobiletopup/assets/', $this->file ) ) );

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page

		add_filter( 'plugin_action_links'  , array( $this, 'add_settings_link' ),10, 5 );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS // infuter
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		global $wpdb;
		$this->settings = $this->settings_fields();

		// Create table
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'thailand_top_up';
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'" ) != $table_name){
			$sql= "CREATE TABLE IF NOT EXISTS $table_name (
				id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				ORDER_ID varchar(255) DEFAULT NULL,
				ORDER_ID_THAILAD_TOPUP varchar(255) DEFAULT NULL,
				TOKENT_API varchar(255) DEFAULT NULL,
				reg_date TIMESTAMP
				) $charset_collate ;";
			require_once (ABSPATH. 'wp-admin/includes/upgrade.php' );
			dbDelta($sql);
		}
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page(
			__( 'ThailandTopup Settings', 'mobiletop' ) ,
			__( 'ThailandTopup', 'mobiletop' ) ,
			'manage_options' ,
			'mobiletopup_settings' ,
			array( $this, 'settings_page' ), //function call back
			'dashicons-smartphone',999
		);

	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $actions, $plugin_file  ) {

		if ($plugin_file == 'mobiletopup/mobiletopup_main.php')
		{

			$settings = array('settings' => '<a href="admin.php?page=mobiletopup_settings">' . __( 'Settings', 'mobiletop' ) . '</a>');
			$actions = array_merge($settings, $actions);
		}

		return $actions;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['standard'] = array(
			'title'					=>'Plugin Settings',
			'description'			=> __( 'If you do not have one yet, you will need an account at ThailandTopup.com', 'mobiletop' ),
			'fields'				=> array(
				// array(
				// 	'id' 			=> 'sanbox_mode_mobiletopup',
				// 	'label'			=> __( 'Sanbox mode', 'mobiletop' ),
				// 	'description'	=> __( 'For testing', 'mobiletop' ),
				// 	'type'			=> 'radio',
				// 	'default'		=> ''
				// ),
				array(
					'id' 			=> 'radio_networking_mobiletopup',
					'label'			=> __( 'Choose Network', 'mobiletop' ),
					'description'	=> __( '', 'mobiletop' ),
					'options'		=> array( '1' => 'AIS 12Call', '2' => 'DTAC Happy', '3' => 'True Move', '4' => 'Any network via lookup' ),
					'type'			=> 'radio',
					'default'		=> '4'
				),
				array(
					'id' 			=> 'secret_key_api_mobiletopup',
					'label'			=> __( 'API Secret Key:' , 'mobiletop' ),
					'description'	=> __( '<a target="_balnk" href="https://thailandtopup.com/api_keys/new">Get your secrect key</a>', 'mobiletop' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'pk_b30fbcxxxxxxxxf64f971989e7', 'mobiletop' )
				),
			)
		);

		$settings = apply_filters( 'mobiletopup_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {


		if ( is_array( $this->settings ) ) {

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), 'mobiletopup_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = 'mbt_'. $field['id'];
					register_setting( 'mobiletopup_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array('mobileTopUpDisplayField', 'display_field' ), 'mobiletopup_settings', $section, array( 'field' => $field) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}



	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="mobiletopup_settings">' . "\n";
		$html .= '<h2>' . __( 'ThailandTopup Settings' , 'mobiletop' ) . '</h2>' . "\n";
		$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";
		// Get settings fields
		ob_start();
		settings_fields( 'mobiletopup_settings' );
		do_settings_sections( 'mobiletopup_settings' );
		$html .= ob_get_clean();
			$html .= '<p class="submit">' . "\n";
				$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'mobiletop' ) ) . '" />' . "\n";
			$html .= '</p>' . "\n";
		$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}


	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( 'mobiletopup-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( 'mobiletopup-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( 'mobiletopup-frontend-js', esc_url( $this->assets_url ) . 'js/frontend.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( 'mobiletopup-frontend-js' );
	} // End enqueue_scripts ()


	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( 'mobiletopup-admin', esc_url( $this->assets_url ) . 'js/backend.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( 'mobiletopup-admin' );
	} // End admin_enqueue_scripts ()


}
