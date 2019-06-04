<?php
/**
 * Plugin load class.
 *
 * @author   Simon Fuhlhaber
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'LP_Addon_Course_Review' ) ) {
	/**
	 * Class LP_Addon_Course_Review.
	 */
	class LP_Addon_IDEAS_API extends LP_Addon {

		/**
		 * LP_Addon_Course_Review constructor.
		 */
		public function __construct() {
			parent::__construct();
			//add_action( 'widgets_init', array( $this, 'load_widget' ) );
			
			
		}

		/**
		 * Define Learnpress Course Review constants.
		 *
		 * @since 3.0.0
		 */
		protected function _define_constants() {
			define( 'LP_ADDON_IDEAS_API_PATH', dirname( LP_ADDON_IDEAS_API_FILE ) );
//			define( 'LP_ADDON_COURSE_REVIEW_PER_PAGE', 5 );
//			define( 'LP_ADDON_COURSE_REVIEW_TMPL', LP_ADDON_COURSE_REVIEW_PATH . '/templates/' );
//			define( 'LP_ADDON_COURSE_REVIEW_THEME_TMPL', learn_press_template_path() . '/addons/course-review/' );
//			define( 'LP_ADDON_COURSE_REVIEW_URL', untrailingslashit( plugins_url( '/', dirname( __FILE__ ) ) ) );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @since 3.0.0
		 */
		protected function _includes() {
			require_once LP_ADDON_IDEAS_API_PATH . '/inc/functions.php';
		}

		/**
		 * Init hooks.
		 */
		protected function _init_hooks() {
			ilp_api_init_hooks();
		}
	}
}

add_action( 'plugins_loaded', array( 'LP_Addon_IDEAS_API', 'instance' ) );