<?php
/**
 * Abstract Model - Crud_CPT
 *
 * Defines share functionality for Crud objects wrapped around WordPress CPTs
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQL\Error\UserError;
use WPGraphQL\Model\Model;
use WP_Post_Type;

/**
 * Class Crud_CPT
 */
abstract class Crud_CPT extends Model {

	/**
	 * Stores the incoming post type object for the post being modeled
	 *
	 * @var null|WP_Post_Type $post_type_object
	 */
	protected $post_type_object;

	/**
	 * Crud_CPT constructor
	 *
	 * @param array  $allowed_restricted_fields - Fields that can be resolved even if post is restricted.
	 * @param string $post_type                 - Object post-type.
	 * @param int    $post_id                   - Post ID.
	 */
	public function __construct( $allowed_restricted_fields, $post_type, $post_id ) {
		$author_id              = get_post_field( 'post_author', $post_id );
		$this->post_type_object = get_post_type_object( $post_type );

		/**
		 * Set the resolving post to the global $post. That way any filters that
		 * might be applied when resolving fields can rely on global post and
		 * post data being set up.
		 */
		$post = get_post( $post_id );
		// @codingStandardsIgnoreLine
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		$restricted_cap = apply_filters(
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			$this->post_type_object->name . '_restricted_cap',
			$this->get_restricted_cap()
		);

		parent::__construct( $restricted_cap, $allowed_restricted_fields, $author_id );
	}

	/**
	 * Forwards function calls to WC_Data sub-class instance.
	 *
	 * @param string $method - function name.
	 * @param array  $args  - function call arguments.
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		return $this->data->$method( ...$args );
	}

	/**
	 * Determines if the data object should be considered private
	 *
	 * @return bool
	 */
	protected function is_private() {
		$post_status = get_post_status( $this->data->get_id() );
		if ( true === $this->owner_matches_current_user() || 'publish' === $post_status ) {
			return false;
		}
		if ( 'private' === $post_status && ! current_user_can( $this->post_type_object->cap->read_private_posts ) ) {
			return true;
		}
		if ( 'auto-draft' === $post_status && true !== $this->owner_matches_current_user() ) {
			return true;
		}
		return false;
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the crud object
	 *
	 * @return string
	 */
	abstract public function get_restricted_cap();

	/**
	 * Wrapper function for deleting
	 *
	 * @throws UserError Not authorized.
	 *
	 * @param boolean $force_delete Should the data be deleted permanently.
	 * @return boolean
	 */
	public function delete( $force_delete = false ) {
		if ( ! current_user_can( $this->post_type_object->cap->edit_posts ) ) {
			throw new UserError(
				__(
					'User does not have the capabilities necessary to delete this object.',
					'wp-graphql-woocommerce'
				)
			);
		}

		return $this->data->delete( $force_delete );
	}
}
