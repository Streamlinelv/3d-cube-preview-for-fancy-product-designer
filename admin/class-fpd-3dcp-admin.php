<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    fpd_3dcp
 * @subpackage fpd_3dcp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    fpd_3dcp
 * @subpackage fpd_3dcp/admin
 * @author     Your Name <email@example.com>
 */
class fpd_3dcp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $settings = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->register_shortcodes();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in fpd_3dcp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The fpd_3dcp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fpd-3dcp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in fpd_3dcp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The fpd_3dcp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fpd-3dcp-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Return settings or default values
	 *
	 * @since    1.0
	 */
	function get_settings( $key = null ){
		
		if ( is_null( $this->settings ) ) {
			// Load settings once and merge with defaults
			$this->settings = wp_parse_args(
				get_option( 'fpd_3dcp_settings', array() ),
				array(
					'scene_width'    		=> 1500,
					'scene_height'   		=> 800,
					'cube_height'     		=> 579,
					'button_name'     		=> __( '3D Preview', 'fpd-3dcp' ),
					'button_class'    		=> 'fpd-3dcp-button-preview',
					'product_ids'     		=> '',
					'plane_order'     		=> '0,1,2,3,4,5',
					'product_ids_5_sides'   => '',
					'quality_notice'  		=> __( '<strong>Please Note</strong>: This 3D preview is meant to visualize how the design wraps around the cube. The quality may appear lower here — your final product will be printed in high resolution.', 'fpd-3dcp' ),
				)
			);
		}

		if ( $key !== null ) {
			return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : null;
		}

		return $this->settings;
	}

	/**
	 * Register the menu under Fancy Product Designer admin menu.
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_menu(){
		global $fpd_3dcp_admin_menu_page;
		
		if( class_exists( 'Fancy_Product_Designer' ) ){
			$fpd_3dcp_admin_menu_page = add_submenu_page( 'fancy_product_designer', FPD_3DCP_PLUGIN_NAME, FPD_3DCP_PLUGIN_NAME, 'list_users', FPD_3DCP, array( $this, 'fpd_3dcp_display_page' ) );
		}
	}

	/**
	 * Display settings page contents
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_display_page(){ ?>
		<div class="wrap">
			<h1><?php echo FPD_3DCP_PLUGIN_NAME; ?></h1>
			<form method="post" action="options.php">
				<?php
				// Output security fields and registered settings
				settings_fields( 'fpd_3dcp_settings_group' );
				do_settings_sections( FPD_3DCP );
				submit_button( 'Save' );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register settings fields
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_register_settings(){
		register_setting( 'fpd_3dcp_settings_group', 'fpd_3dcp_settings' );

		$fields = array(
			'scene_width' 			=> __( 'Scene width', 'fpd-3dcp' ),
			'scene_height' 			=> __( 'Scene height', 'fpd-3dcp' ),
			'cube_height' 			=> __( 'Cube height', 'fpd-3dcp' ),
			'button_name' 			=> __( 'Preview button name', 'fpd-3dcp' ),
			'button_class' 			=> __( 'Preview button class', 'fpd-3dcp' ),
			'product_ids' 			=> __( 'Product IDs', 'fpd-3dcp' ),
			'plane_order' 			=> __( 'Plane order', 'fpd-3dcp' ),
			'product_ids_5_sides' 	=> __( 'Product IDs with 5 sides', 'fpd-3dcp' ),
			'quality_notice' 		=> __( 'Preview quality notice', 'fpd-3dcp' ),
		);

		add_settings_section(
			'fpd_3dcp_main_section',
			null,
			null,
			FPD_3DCP
		);

		//Adding setting field
		foreach( $fields as $key => $name ){
			add_settings_field(
				'fpd_3dcp_' . $key,
				'<label for="fpd_3dcp_'. $key .'">'. $name .'</label>',
				array( $this, 'fpd_3dcp_' . $key ),
				FPD_3DCP,
				'fpd_3dcp_main_section'
			);
		}
	}

	/**
	 * Defining width field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_scene_width(){
		$value = $this->get_settings( 'scene_width' ); ?>
		<input id="fpd_3dcp_scene_width" type="number" name="fpd_3dcp_settings[scene_width]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining height field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_scene_height(){
		$value = $this->get_settings( 'scene_height' ); ?>
		<input id="fpd_3dcp_scene_height" type="number" name="fpd_3dcp_settings[scene_height]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining cube height field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_cube_height(){
		$value = $this->get_settings( 'cube_height' ); ?>
		<input id="fpd_3dcp_cube_height" type="number" name="fpd_3dcp_settings[cube_height]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining cube plane order field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_plane_order(){
		$value = $this->get_settings( 'plane_order' ); ?>
		<input id="fpd_3dcp_plane_order" type="text" name="fpd_3dcp_settings[plane_order]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining preview button name field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_button_name(){
		$value = $this->get_settings( 'button_name' ); ?>
		<input id="fpd_3dcp_button_name" type="text" name="fpd_3dcp_settings[button_name]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining preview button class field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_button_class(){
		$value = $this->get_settings( 'button_class' ); ?>
		<input id="fpd_3dcp_button_class" type="text" name="fpd_3dcp_settings[button_class]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining product IDs field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_product_ids(){
		$value = $this->get_settings( 'product_ids' ); ?>
		<input id="fpd_3dcp_product_ids" type="text" name="fpd_3dcp_settings[product_ids]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining product IDs field for 5 side cubes
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_product_ids_5_sides(){
		$value = $this->get_settings( 'product_ids_5_sides' ); ?>
		<input id="fpd_3dcp_product_ids_5_sides" type="text" name="fpd_3dcp_settings[product_ids_5_sides]" value="<?php echo esc_attr( $value ?? '' ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Defining product IDs field
	 *
	 * @since    1.0
	 */
	function fpd_3dcp_quality_notice(){
		$value = $this->get_settings( 'quality_notice' ); ?>
		<textarea id="fpd_3dcp_quality_notice" rows="5" name="fpd_3dcp_settings[quality_notice]" value="" class="regular-text"><?php echo esc_attr( $value ?? '' ); ?></textarea>
		<?php
	}

	/**
	 * Add 3D Preview button
	 *
	 * @since    1.0
	 */
	function add_3d_preview_button( $atts = [], $content = null ){
		$atts = shortcode_atts([
			'title' => esc_html__( '3D Preview', 'fpd-3dcp' ),
		], $atts, 'fpd_3dcp_button' );
		ob_start(); ?>
		<button class="button fpd-3dcp-button-preview"><?php echo esc_html( $atts['title'] ); ?></button>
		<?php return ob_get_clean();
	}

	/**
	 * Shortcode for adding the button
	 * Example on using the shortcode: echo do_shortcode('[fpd_3dcp_button title="Your title"]');
	 *
	 * @since    1.0
	 */
	function register_shortcodes(){
		add_shortcode( 'fpd_3dcp_button', array( $this, 'add_3d_preview_button' ) );
	}
}