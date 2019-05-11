<?php
/**
 * Defines Admin only restriction caps.
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
/**
 * Trait Shop_Manager_Capabilities
 */
trait Shop_Manager_Caps {
	/**
	 * Retrieve the cap to check if the data should be restricted for the order
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_restricted_cap() {
		if ( post_password_required( $this->data->get_id() ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( get_post_status( $this->data->get_id() ) ) {
			case 'draft':
				$cap = $this->post_type_object->cap->edit_others_posts;
				break;
			default:
				$cap = $this->post_type_object->cap->edit_posts;
				break;
		}
		return $cap;
	}
}
