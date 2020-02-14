<?php
/**
 * Defines Admin only restriction caps.
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Model;

/**
 * Trait Shop_Manager_Capabilities.
 */
trait Shop_Manager_Caps {

	/**
	 * Retrieve the cap to check if the data should be restricted for the order
	 *
	 * @return string
	 */
	public function get_restricted_cap() {
		if ( post_password_required( $this->data->get_id() ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( get_post_status( $this->data->get_id() ) ) {
			case 'draft':
				$cap = $this->post_type_object->cap->edit_others_posts;
				break;
			default:
				$cap = '';
				if ( ! $this->owner_matches_current_user() ) {
					$cap = $this->post_type_object->cap->edit_posts;
				}
				break;
		}
		return $cap;
	}

	/**
	 * Whether or not the owner of the data matches the current user.
	 *
	 * @return bool
	 */
	protected function owner_matches_current_user() {
		// Get Customer ID.
		$customer_id = null;
		if ( is_callable( array( $this->data, 'get_customer_id' ) ) ) {
			$customer_id = $this->data->get_customer_id();
		}

		if ( empty( $this->current_user->ID ) || ( empty( $this->owner ) && empty( $customer_id ) ) ) {
			return false;
		}
		return ( absint( $this->owner ) === absint( $this->current_user->ID ) || absint( $customer_id ) === absint( $this->current_user->ID ) )
			? true
			: false;
	}
}
