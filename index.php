<?php
/*
Plugin Name: Cryptocurrency Donation Widget
Plugin URI: 
Description: Accept top 50+ major crypto currencies donation inside your WordPress website.
Author: Bytemart.org
Author URI: https://www.bytemart.org/
Version: 0.1
*/


if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! defined( 'BTCDNT_PATH' ) ) {

	
	define( 'BTCDNT_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

define( 'BTCDNT_VERSION', '1.0.5' );
define( 'BTCDNT_TEXT_DOMAIN', 'btcdnt' );

if ( ! class_exists( 'BTCDNT' ) ) {

	/**
	 * Adds BTCDNT.
	 */
	class BTCDNT extends WP_Widget {

		/**
		 * Instance of the class
		 *
		 * @var instance
		 */
		public static $instance;

		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			load_plugin_textdomain( BTCDNT_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) );
			add_action( 'admin_init', array( $this, 'dismiss_review_notice' ) );
			add_action( 'admin_init', array( $this, 'show_review_notice') );

			parent::__construct(
				'btcdnt', // Base ID.
				esc_html__( 'Cryptocurrency Donation Widget', BTCDNT_TEXT_DOMAIN ), // Name.
				array( 'description' => esc_html__( 'Accept top 50+ major crypto currencies donation inside your WordPress website', BTCDNT_TEXT_DOMAIN ) ) // Args.
			);
		}

		
		public function widget( $args, $instance ) {

			$btcdnt_type    = $instance['btcdnt_type'];
			
			$btcdnt_content =  trim($instance['btcdnt_content']);
			
			echo $args['before_widget'];

			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $instance['title'] ) ) . $args['after_title'];
			}
			
			echo '<div class="code-widget">' . $btcdnt_final . '</div>';

			echo $args['after_widget'];
			
			
			$theuri = base64_encode($btcdnt_content.'-'.$_SERVER['SERVER_NAME']);
			echo sprintf(
      '<img src="//chart.apis.google.com/chart?cht=%1$s&chs=%2$dx%3$d&chl=%4$s&choe=%5$s" alt="%6$s" />', 
      'qr', 
      '180', 
      '180', 
      'https://www.bytemart.org/donateqr/'.$theuri, 
      'UTF-8', 
      'Donate Crypto'
    );
			
			
			echo '<small><a target="_blank" href="https://www.bytemart.org/donateqr/'.$theuri.'" >Donate now</a></small>';
			
		}

		
		public function form( $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Donate Crypto', BTCDNT_TEXT_DOMAIN );
			if ( 0 == count( $instance ) ) {
				
				
				$instance['btcdnt_content'] = ! empty( $instance['btcdnt_content'] ) ? $instance['btcdnt_content'] : esc_html__( 'Provide your Bitcoin wallet address', BTCDNT_TEXT_DOMAIN );
				
			} else {
				
				$instance['btcdnt_content'] = $instance['btcdnt_content'];
				
			}
			?>
				<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', BTCDNT_TEXT_DOMAIN ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text"
				value="<?php echo esc_attr( $title ); ?>">
				</p>
				<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'btcdnt_type' ) ); ?>">
					<?php esc_attr_e( 'Bitcoin Wallet Address:', BTCDNT_TEXT_DOMAIN ); ?>
				</label>
				</p>
				
				<p>
				<input name="<?php echo esc_attr( $this->get_field_name( 'btcdnt_content' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'btcdnt_content' ) ); ?>" value="<?php echo $instance['btcdnt_content']; ?>" />
				
				
				</p>
				<p><small>Please provide your bitcoin wallet addresse</small></p>
			<?php
		}

		
		public function update( $new_instance, $old_instance ) {
			$instance               = array();
			$instance['title']      = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			
			$instance['btcdnt_content'] = ( ! empty( $new_instance['btcdnt_content'] ) ) ? strip_tags( $new_instance['btcdnt_content'] ) : '';
			
			if ( current_user_can( 'unfiltered_html' ) ) {
				$instance['btcdnt_content'] = $new_instance['btcdnt_content'];
			} else {
				$instance['btcdnt_content'] = stripslashes( wp_filter_post_kses( $new_instance['btcdnt_content'] ) );
			}
			return $instance;
		}

	

		
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
					self::$instance = new self();
			}
			return self::$instance;
		}

		
		public function show_review_notice() {

			$install_date = get_option( 'btcdnt_activation_time' );
			$already_done = get_option( 'btcdntrn_dismiss' );
			$show_later_date = get_option( 'btcdntrn_show_later' );
			$now = strtotime( 'now' );

			
			if( $already_done ){
				return;
			}
			if( ! $install_date ) {
				update_option( 'btcdnt_activation_time', strtotime( '-7 days' ) );
				$install_date = strtotime( '-7 days' );
				
			}
			$past_date    = strtotime( '-7 days' );
			
			if( ( $show_later_date &&  $now < $show_later_date ) ) {
				return;
			}
			
			
		
			if ( $past_date >= $install_date ) {
	
				add_action( 'admin_notices', array( $this, 'review_admin_notice' ) );
			}
		}

		/**
		 * Review Show admin notice.
		 *
		 * @since 1.0.5
		 * @return void
		 */
		public function review_admin_notice() {
			// wordpress global variable 
			global $pagenow;
			if( $pagenow == 'index.php' ){

					$show_later = add_query_arg( array(
						'btcdnt_show_later' => '1',
						'_wpnonce' => wp_create_nonce( 'show-later' )
					), get_admin_url() );

					$already_done = add_query_arg( array(
						'btcdnt_already_done' => '1',
						'_wpnonce' => wp_create_nonce( 'already-done' )
					), get_admin_url() );
					$review_url = esc_url( 'https://wordpress.org/support/plugin/bitcoin-donation-and-fundraising-widget/reviews/#new-post' );

					printf(__('<div class="notice notice-info"><p>You have been using <b> Bitcoin Donation and Fundraising Widget</b> for a while. We hope you liked it ! Please give us a quick rating, it works as a boost for us to keep working on the plugin !</p><p class="action">
					<a href="%s" class="button button-primary" target="_blank">Rate Now!</a>
					<a href="%s" class="button button-secondary "> Show Later </a>
					<a href="%s" class="void-grid-review-done"> Already Done !</a>
							</p></div>', BTCDNT_TEXT_DOMAIN ), $review_url, $show_later, $already_done );
			}
		}

		
		public function dismiss_review_notice () {

			if( isset( $_GET['btcdnt_show_later'] ) && isset( $_GET['_wpnonce'] ) ) {
				if (  wp_verify_nonce( $_GET['_wpnonce'], 'show-later' ) ) {
					update_option( 'btcdntrn_show_later', strtotime( '+2 days' ) );
				}
			}

			if( isset( $_GET['btcdnt_already_done'] ) && isset( $_GET['_wpnonce'] ) ) {
				if (  wp_verify_nonce( $_GET['_wpnonce'], 'already-done' ) ) {
					update_option( 'btcdntrn_dismiss', true );
				}
			}

		}



	} // class BTCDNT.


	
	function register_BTCDNT() {
	
		register_widget( 'BTCDNT' );
	}
	add_action( 'widgets_init', 'register_BTCDNT' );

}

register_activation_hook( __FILE__, 'btcdnt_activation_time' );

/**
 * Plugin Activation hook callback.
 * @since 1.0.0
 * @return void
 */
function btcdnt_activation_time() {
	$get_activation_time = strtotime("now");
	update_option( 'btcdnt_activation_time', $get_activation_time );  
}

?>