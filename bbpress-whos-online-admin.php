<?php
class bbP_Whos_Online_Admin {

	/**
	 * Who's online options key
	 * @var string
	 */
	protected $whos_online = '_bbp_whos_online';

	/**
	 * Time threshold in minutes options key
	 * @var string
	 */
	protected $threshold = '_bbp_whos_online_threshold';


	function __construct() {
		add_filter( 'bbp_admin_get_settings_sections', 	array( $this, 'add_settings_section' 	 ) 		  );
		add_filter( 'bbp_admin_get_settings_fields', 	array( $this, 'add_settings_fields' 	 ) 		  );
		add_filter( 'bbp_map_settings_meta_caps', 		array( $this, 'set_settings_section_cap' ), 10, 4 );
	}

	/**
	 * Returns whether the Who's Online functionality is active or not.
	 *
	 * @param bool $default
	 *
	 * @return bool
	 */
	public function is_whos_online_active( $default  ) {
		return (bool) apply_filters( 'bbp_whos_online', (bool) get_option( $this->whos_online, $default ) );
	}

	/**
	 * Returns the threshold of inactivity to stop considering an user as online. In Minutes.
	 *
	 * @param $default
	 *
	 * @return int
	 */
	public function get_threshold( $default ) {
		return (int) apply_filters( 'bbp_whos_online_threshold', (int) get_option( $this->threshold, $default ) );
	}

	/**
	 * Adds settings section to the bbPress settings page
	 *
	 * @param array $sections
	 *
	 * @return array
	 */
	public function add_settings_section( $sections ) {
		$sections['bbp_settings_whos_online'] = array(
			'title'    => __( "Who's Online Settings", 'bbp-whos-online' ),
			'callback' => array( $this, 'setting_section_description' ),
			'page'     => 'bbpress'
		);

		return $sections;
	}

	/**
	 * Description for our settings page
	 */
	public function setting_section_description() {
		_e( "Settings for the Who's Online functionality.", 'bbp-whos-online' );
	}

	/**
	 * @param $caps
	 * @param $cap
	 * @param $user_id
	 * @param $args
	 *
	 * @return array
	 */
	public function set_settings_section_cap( $caps, $cap, $user_id, $args ) {
		if ( $cap !== 'bbp_settings_whos_online' )
			return $caps;

		return array( bbpress()->admin->minimum_capability );
	}

	/**
	 * Adds settings fields to the bbPress settings page
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function add_settings_fields( $settings ) {

		$settings['bbp_settings_whos_online'] = array(

			// Activate who's online
			$this->whos_online => array(
				'title'             => __( "Who's Online", 'bbp-whos-online' ),
				'callback'          => array( $this, 'field_whos_online' ),
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// Time threshold
			$this->threshold => array(
				'title'             => __( "Time threshold", 'bbp-whos-online' ),
				'callback'          => array( $this, 'field_threshold' ),
				'sanitize_callback' => 'intval',
				'args'              => array()
			),


		);

		return $settings;
	}

	/**
	 *  Settings field for who's online
	 */
	public function field_whos_online() {
		?>
		<input id="<?php echo $this->whos_online; ?>" name="<?php echo $this->whos_online; ?>" type="checkbox" value="1" <?php checked( bbp_is_whos_online_active( false ) ); ?> />
		<label for="<?php echo $this->whos_online; ?>"><?php _e( "Activate the Who's Online functionality", 'bbp-whos-online' ); ?></label>
	<?php
	}

	/**
	 *  Settings field for time threshold
	 */
	public function field_threshold() {
		?>
		<input id="<?php echo $this->threshold; ?>" name="<?php echo $this->threshold; ?>" type="number" min="1" step="1" value="<?php echo esc_attr( bbp_whos_online_threshold( 5 ) ); ?>" class="small-text" />
		<label for="<?php echo $this->threshold; ?>"><?php _e( "Threshold in minutes to stop considering an user as online", 'bbp-whos-online' ); ?></label>
	<?php
	}
}