<?php

// Include WPCOM_Admin_Bar if it's not available. Which should never happen but does for some reason.
// if ( ! class_exists( 'WP_Admin_Bar' ) ) {
// include_once 'class.wpcom-admin-bar.php';
// }
// class A8C_Admin_Bar extends WPCOM_Admin_Bar {
class A8C_Admin_Bar extends WP_Admin_Bar {

	/**
	 * Overrides the WPCOM_Admin_Bar render method so that we can control the output and access
	 * final and protected methods within WPCOM_Admin_Bar.
	 *
	 * @see WPCOM_Admin_Bar
	 */
	function render() {
		global $is_IE;

		$class = 'nojq nojs';
		if ( $is_IE ) {
			if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 7' ) ) {
				$class .= ' ie7';
			} elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 8' ) ) {
				$class .= ' ie8';
			} elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 9' ) ) {
				$class .= ' ie9';
			}
		} elseif ( wp_is_mobile() ) {
			$class .= ' mobile';
		}

		$user = wp_get_current_user();
		if ( 'closed' == get_user_meta( $user->ID, 'superadmin_bar_closed', true ) ) {
			$class .= ' toggle-closed';
		}

		$root = $this->_bind();
		if ( count( $root->children[0]->children ) > 0 ) {
			?>
			<div id="superadminbar" class="<?php echo $class; ?>" role="navigation">
				<?php foreach ( $root->children as $group ) {
					$this->_render_group( $group );
} ?>
			</div>
			<?php
		}
	}
}
