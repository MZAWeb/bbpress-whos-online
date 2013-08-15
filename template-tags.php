<?php
/**
 * Returns the singleton instance of bbP_Whos_Online
 * @return bbP_Whos_Online
 */
function bbp_whos_online() {
	return bbP_Whos_Online::instance();
}

/**
 * Returns an array of pingbacks for the given or current topic
 *
 * @param bool $default
 *
 * @return bool
 */
function bbp_is_whos_online_active( $default ) {
	return (bool)bbp_whos_online()->admin->is_whos_online_active( $default );
}


function bbp_whos_online_threshold( $default ){
	return (int)bbp_whos_online()->admin->get_threshold( $default );
}