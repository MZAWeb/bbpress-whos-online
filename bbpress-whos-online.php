<?php
/**
 * Plugin Name: bbPress Who's Online
 * Plugin URI:  https://github.com/MZAWeb/bbpress-pingbacks
 * Description: Adds a widget to show who's online
 * Author:      Daniel Dvorkin
 * Plugin URI:  http://danieldvork.in
 * Version:     0.1
 * Text Domain: bbp-whos-online
 * Domain Path: /languages/
 */

include_once 'bbpress-whos-online-admin.php';
include_once 'template-tags.php';


class bbP_Whos_Online {
	/**
	 * Singleton instance of this class
	 * @var bbP_Whos_Online
	 */
	protected static $instance;

	/**
	 * Name of the option with the array of online users
	 * @var string
	 */
	protected $option_name = 'bbp_whos_online';

	/**
	 * Instance of bbP_Whos_Online_Admin
	 * @var bbP_Whos_Online_Admin|null
	 */
	public $admin = null;

	/**
	 * Class constructor
	 */
	function __construct() {
		add_action( 'init', array( $this, 'maybe_record_online_user' ) );

		/* debug-bar-bbpress Integration */
		add_filter( 'bbp-debug-bar-vars',   array( $this, 'add_debug_bar_users_count' )    );
		add_action( 'bbp-debug-bar-panels', array( $this, 'add_debug_bar_users_list'  ), 8 );

		$this->admin = new bbP_Whos_Online_Admin();
	}


	/**
	 * Register an user as online.
	 * @param int|string $identifier user_id or IP
	 * @param int $timestamp time for the user's last pageload
	 *
	 */
	public function maybe_record_online_user( $identifier = null, $timestamp = null ) {

		// If bbp whos online is deactivated from the bbp settings
		if ( ! bbp_is_whos_online_active( false ) )
			return;

		/* Get the current user ID or IP */
		if ( empty( $identifier ) )
			$identifier = is_user_logged_in() ? get_current_user_id() : $this->get_real_ip_address();

		if ( empty( $identifier ) )
			return;

		if ( empty( $timestamp ) )
			$timestamp = time();

		/* Store the timestamp */
		$users = $this->get_online_users();
		$users[$identifier] = $timestamp;
		$this->save_online_users( $users );

		return;

	}

	/**
	 * Retrieve the stored array of online users
	 *
	 * @filter bbp_get_online_users over the list of online users
	 *
	 * @return mixed|void
	 */
	protected function get_online_users() {
		return apply_filters( 'bbp_get_online_users', get_option( $this->option_name, array() ) );
	}


	/**
	 * Save the stored array of online users
	 *
	 * @param array $users
	 *
	 * @filter bbp_save_online_users over the list of online users
	 */
	protected function save_online_users( $users ) {
		update_option( $this->option_name, apply_filters( 'bbp_save_online_users', $users ) );
	}

	/**
	 * Singleton function to get the only instance of this class
	 *
	 * @static
	 * @return bbP_Whos_Online
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new bbP_Whos_Online;
		}
		return self::$instance;
	}

	/**
	 * Get user IP address
	 *
	 * See http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
	 *
	 * @return string
	 */
	public function get_real_ip_address() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) )
			return null;

		return $ip;
	}

	/**
	 *  If the debug-bar-bbpress plugin is activated, adds the count
	 *  of online users to the vars section
	 *
	 * @link http://wordpress.org/extend/plugins/debug-bar-bbpress/
	 */
	public function add_debug_bar_users_count( $vars ) {
		$users = $this->get_online_users();
		$vars[__( 'Online users', 'bbp-whos-online' )] = count( $users );

		return $vars;
	}

	/**
	 *  If the debug-bar-bbpress plugin is activated, adds the list
	 *  of online users at the bottom of the panel
	 *
	 * @link http://wordpress.org/extend/plugins/debug-bar-bbpress/
	 */
	public function add_debug_bar_users_list() {

		$users = $this->get_online_users();
		
		if ( empty( $users ) )
			return;

		echo '<h3 style="float: none;clear: both;font-family: georgia,times,serif;font-size: 22px;margin: 15px 10px 15px 0!important;">';
		echo  __( 'Online users:', 'bbp-whos-online' );
		echo '</h3>';

		echo '<ol style="clear: both; list-style: decimal;">';

		$anon = 0;

		foreach ( $users as $user => $time ) {
			if ( ( time() - $time ) > ( bbp_whos_online_threshold( 5 ) * 60 ) ){
				unset ($users[$user]);
				continue;
			}

			if ( is_numeric( $user ) )
				echo sprintf( '<li style="margin-left: 20px;"><p>%s</p></li>', bbp_get_user_profile_link( $user ) );
			else
				$anon ++;
		}

		echo sprintf( '<li style="margin-left: 20px;"><p>%s %d</p></li>', __( 'Anonymous users:', 'bbp-whos-online' ), $anon );

		echo '</ol>';

		$this->save_online_users( $users );

	}

}

/* Loads the plugin */
add_action( 'plugins_loaded', 'bbp_whos_online' );