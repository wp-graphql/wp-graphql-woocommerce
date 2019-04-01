<?php
/**
 * Abstract Model - Crud_CPT
 *
 * Defines share functionality for Crud objects wrapped around WordPress CPTs
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use WPGraphQL\Model\Model;

/**
 * Class Crud_CPT
 */
abstract class Crud_CPT extends Model {
	/**
	 * Stores the incoming post type object for the post being modeled
	 *
	 * @var null|\WP_Post_Type $post_type_object
	 * @access protected
	 */
	protected $post_type_object;

	/**
	 * Crud_CPT constructor
	 *
	 * @param string $name                      - Model name.
	 * @param mixed  $data                      - WC Crud object.
	 * @param array  $allowed_restricted_fields - Fields that can be resolved even if post is restricted.
	 * @param string $post_type                 - Object post-type.
	 * @param int    $post_id                   - Post ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $name, $data, $allowed_restricted_fields, $post_type, $post_id ) {
		$author_id              = get_post_field( 'post_author', $post_id );
		$this->post_type_object = get_post_type_object( $post_type );

		$restricted_cap = $this->get_restricted_cap();
		if ( ! has_filter( 'graphql_data_is_private', [ $this, 'is_private' ] ) ) {
			add_filter( 'graphql_data_is_private', [ $this, 'is_private' ], 1, 3 );
		}

		parent::__construct(
			$name,
			$data,
			$restricted_cap,
			$allowed_restricted_fields,
			$author_id
		);
		$this->init();
	}

	/**
	 * Callback for the graphql_data_is_private filter to determine if the crud object should be
	 * considered private
	 *
	 * @param bool   $private    - True or False value if the data should be private.
	 * @param string $model_name - Name of the model for the data currently being modeled.
	 * @param mixed  $data       - The Data currently being modeled.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_private( $private, $model_name, $data ) {
		if ( $this->model_name !== $model_name ) {
			return $private;
		}

		$post_status = get_post_status( $data->get_id() );
		if ( true === $this->owner_matches_current_user() || 'publish' === $post_status ) {
			return false;
		}
		if ( 'private' === $post_status && ! current_user_can( $this->post_type_object->cap->read_private_posts ) ) {
			return true;
		}
		if ( 'auto-draft' === $post_status && true !== $this->owner_matches_current_user() ) {
			return true;
		}
		return $private;
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the crud object
	 *
	 * @access protected
	 * @return string
	 */
	abstract protected function get_restricted_cap();

	/**
	 * Initializes the crud object field resolvers
	 *
	 * @access public
	 */
	abstract public function init();
}
