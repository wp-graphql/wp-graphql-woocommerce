<?php
/**
 * Abstract Model - WC_Post
 *
 * Defines shared functionality for WooCommerce CPT models.
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQL\Error\UserError;
use WPGraphQL\Model\Post;
use WP_Post_Type;

/**
 * Class WC_Post
 */
abstract class WC_Post extends Post {

	/**
	 * Stores the incoming post type object for the post being modeled
	 *
	 * @var WP_Post_Type $post_type_object
	 */
	protected $post_type_object;


	/**
	 * Store the WP_Post object connected to the model.
	 * 
	 * @var WP_Post $post
	 */
	protected $post;

	/**
	 * Stores the WC_Data object connected to the model.
	 * 
	 * @var \WC_Data $data
	 */
	protected $data;

	/**
	 * WC_Post constructor
	 *
	 * @param string $post_type  Post type.
	 * @param int    $data       Data object to be used by the model.
	 */
	public function __construct( $post_type, $data, $owner_id = null ) {
		// Get WP_Post object.
		$this->post = get_post( $data->get_id() );

		// Execute Post Model constructor.
		parent::__construct( $data, $owner_id );
	}

	/**
	 * Setup the global data for the model to have proper context when resolving
	 */
	public function setup() {
		/**
		 * Set the resolving post to the global $post. That way any filters that
		 * might be applied when resolving fields can rely on global post and
		 * post data being set up.
		 */
		if ( $this->post && $this->post instanceof \WP_Post ) {
			$GLOBALS['post'] = $this->post;
			setup_postdata( $this->post );
		}
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
