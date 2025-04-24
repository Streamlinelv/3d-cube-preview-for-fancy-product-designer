<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    fpd_3dcp
 * @subpackage fpd_3dcp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    fpd_3dcp
 * @subpackage fpd_3dcp/public
 * @author     Your Name <email@example.com>
 */
class fpd_3dcp_Public {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		if( !$this->is_3d_preview_product() ) return;

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fpd-3dcp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		
		if( !$this->is_3d_preview_product() ) return;

		$admin = new fpd_3dcp_Admin( FPD_3DCP_PLUGIN_NAME, FPD_3DCP_VERSION );
		$settings = $admin->get_settings();

		wp_enqueue_script( 'three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', array(), null, true );
		wp_enqueue_script( 'three-orbitcontrols', 'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.min.js', array( 'three-js' ), null, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fpd-3dcp-public.js', array( 'jquery', 'three-js', 'three-orbitcontrols' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'fpd_3dcp', $settings );
	}

	/**
	 * Defining cube 3D preview canvas
	 *
	 * @since    1.0
	 */
	function add_cube_preview_canvas(){

		if( !$this->is_3d_preview_product() ) return;

		$admin = new fpd_3dcp_Admin( FPD_3DCP_PLUGIN_NAME, FPD_3DCP_VERSION );
		$quality_notice = $admin->get_settings( 'quality_notice' );

		ob_start(); ?>
			<div id="fpd-3dcp-cube-preview-overlay" class="fpd-3dcp-preview-overlay">
				<span id="fpd-3dcp-close-preview" class="fpd-3dcp-close-preview">&times;</span>
				<div id="fpd-3dcp-info-message" class="fpd-3dcp-info-message" >
					<span id="fpd-3dcp-close-message" class="fpd-3dcp-close-message">&times;</span>
					<?php echo $quality_notice; ?>
				</div>
				<canvas id="fpd-3dcp-cube-canvas"></canvas>
			</div>
		<?php echo ob_get_clean();
	}

	/**
	 * Defining cube 3D preview canvas
	 *
	 * @since    1.0
	 * @return   boolean
	 */
	function is_3d_preview_product(){
		$result = false;
		
		if( is_product() ){
	   		$product_id = get_queried_object_id();

	   		$admin = new fpd_3dcp_Admin( FPD_3DCP_PLUGIN_NAME, FPD_3DCP_VERSION );
			$product_ids_string = $admin->get_settings( 'product_ids' );
			$product_ids_array = array_map( 'trim', explode( ',', $product_ids_string ) );

			if( in_array( $product_id, $product_ids_array ) ){
				$result = true;
			}
		}

		return $result;
	}
}