<?php
/**
 * Model - Shipping_Method
 *
 * Resolves shipping method object model
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Model\Model;

/**
 * Class Shipping_Method
 *
 * @property \WC_Shipping_Method $data
 *
 * @property int    $ID
 * @property string $id
 * @property int    $databaseId
 * @property string $title
 * @property string $description
 *
 * @package WPGraphQL\WooCommerce\Model
 */
class Shipping_Method extends Model {
	/**
	 * Shipping_Method constructor
	 *
	 * @param \WC_Shipping_Method $method - Shipping method object.
	 */
	public function __construct( $method ) {
		$this->data                = $method;
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		];

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$restricted_cap = apply_filters( 'shipping_method_restricted_cap', '' );

		parent::__construct( $restricted_cap, $allowed_restricted_fields, null );
	}

	/**
	 * Determines if the order item should be considered private.
	 *
	 * @return bool
	 */
	protected function is_private() {
		return false;
	}

	/**
	 * Initializes the Order field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = [
				'ID'          => function () {
					return $this->data->id;
				},
				'id'          => function () {
					return ! empty( $this->data->id ) ? Relay::toGlobalId( 'shipping_method', $this->data->id ) : null;
				},
				'databaseId'  => function () {
					return ! empty( $this->ID ) ? $this->ID : null;
				},
				'title'       => function () {
					return ! empty( $this->data->method_title ) ? $this->data->method_title : null;
				},
				'description' => function () {
					return ! empty( $this->data->method_description ) ? $this->data->method_description : null;
				},
			];
		}
	}
}
