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
	 * Stores the WC_Data object connected to the model.
	 *
	 * @var \WC_Data $data
	 */
	protected $wc_data;

	/**
	 * WC_Post constructor
	 *
	 * @param int $data  Data object to be used by the model.
	 */
	public function __construct( $data ) {
		// Store CRUD object.
		$this->wc_data = $data;

		// Get WP_Post object.
		$post = get_post( $data->get_id() );

		// Add $allowed_restricted_fields.
		if ( ! has_filter( 'graphql_allowed_fields_on_restricted_type', [ static::class, 'add_allowed_restricted_fields' ] ) ) {
			add_filter( 'graphql_allowed_fields_on_restricted_type', [ static::class, 'add_allowed_restricted_fields' ], 10, 2 );
		}

		// Execute Post Model constructor.
		parent::__construct( $post );
	}

	/**
	 * Injects CRUD object fields into $allowed_restricted_fields
	 *
	 * @param array  $allowed_restricted_fields  The fields to allow when the data is designated as restricted to the current user.
	 * @param string $model_name                 Name of the model the filter is currently being executed in.
	 *
	 * @return string[]
	 */
	public static function add_allowed_restricted_fields( $allowed_restricted_fields, $model_name ) {
		$class_name = static::class;
		if ( "{$class_name}Object" === $model_name ) {
			return static::get_allowed_restricted_fields( $allowed_restricted_fields );
		}

		return $allowed_restricted_fields;
	}

	/**
	 * Return the fields allowed to be displayed even if this entry is restricted.
	 *
	 * @param array $allowed_restricted_fields  The fields to allow when the data is designated as restricted to the current user.
	 *
	 * @return array
	 */
	protected static function get_allowed_restricted_fields( $allowed_restricted_fields = [] ) {
		return [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		];
	}

	/**
	 * Forwards function calls to WC_Data sub-class instance.
	 *
	 * @param string $method - function name.
	 * @param array  $args  - function call arguments.
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException Method not found on WC data object.
	 */
	public function __call( $method, $args ) {
		if ( \is_callable( [ $this->wc_data, $method ] ) ) {
			return $this->wc_data->$method( ...$args );
		}

		$class = __CLASS__;
		throw new BadMethodCallException( "Call to undefined method {$method} on the {$class}" );
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

		return $this->wc_data->delete( $force_delete );
	}

	/**
	 * Method for determining if the data should be considered private or not
	 *
	 * @param WP_Post $post_object The object of the post we need to verify permissions for.
	 *
	 * @return bool
	 */
	protected function is_post_private( $post_object = null ) {
		$post_type_object = $this->post_type_object;

		if ( empty( $post_object ) ) {
			$post_object = $this->data;
		}

		if ( empty( $post_object ) ) {
			return true;
		}

		/**
		 * If the status is NOT publish and the user does NOT have capabilities to edit posts,
		 * consider the post private.
		 */
		if ( ! isset( $post_type_object->cap->edit_posts ) || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			return true;
		}

		/**
		 * If the owner of the content is the current user
		 */
		if ( ( true === $this->owner_matches_current_user() ) && 'revision' !== $post_object->post_type ) {
			return false;
		}

		if ( 'private' === $this->data->post_status && ( ! isset( $post_type_object->cap->read_private_posts ) || ! current_user_can( $post_type_object->cap->read_private_posts ) ) ) {
			return true;
		}

		if ( 'auto-draft' === $this->data->post_status ) {
			$parent = get_post( (int) $this->data->post_parent );

			if ( empty( $parent ) ) {
				return true;
			}

			$parent_post_type_obj = $post_type_object;

			if ( empty( $parent_post_type_obj ) ) {
				return true;
			}

			if ( 'private' === $parent->post_status ) {
				$cap = isset( $parent_post_type_obj->cap->read_private_posts ) ? $parent_post_type_obj->cap->read_private_posts : 'read_private_posts';
			} else {
				$cap = isset( $parent_post_type_obj->cap->edit_post ) ? $parent_post_type_obj->cap->edit_post : 'edit_post';
			}

			if ( ! current_user_can( $cap, $parent->ID ) ) {
				return true;
			}
		}//end if

		return false;
	}

	/**
	 * Returns the source WP_Post instance.
	 *
	 * @return \WP_Post
	 */
	public function as_WP_Post() {
		return $this->data;
	}

	/**
	 * Returns the source WC_Data instance
	 *
	 * @return \WC_Data
	 */
	public function as_WC_Data() {
		return $this->wc_data;
	}
}
