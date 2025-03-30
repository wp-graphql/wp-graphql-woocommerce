<?php
/**
 * Model - Shipping_Zone
 *
 * This model represents a Shipping Zone.
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Model\Model;

/**
 * Class Shipping_Zone
 *
 * @property \WC_Shipping_Zone $data
 *
 * @property int    $ID
 * @property string $id
 * @property int    $databaseId
 * @property string $name
 * @property string $order
 * @property object{code: string, type: string}[] $locations
 * @property \WC_Shipping_Method[] $methods
 *
 * @package WPGraphQL\WooCommerce\Model
 */
class Shipping_Zone extends Model {
	/**
	 * Shipping_Zone constructor
	 *
	 * @param \WC_Shipping_Zone $zone Shipping zone object.
	 */
	public function __construct( $zone ) {
		$this->data                = $zone;
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		];

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$restricted_cap = apply_filters( 'shipping_zone_restricted_cap', '' );

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
				'ID'         => function () {
					return $this->data->get_id();
				},
				'id'         => function () {
					return ! empty( $this->data->get_id() ) ? Relay::toGlobalId( 'shipping_zone', (string) $this->data->get_id() ) : null;
				},
				'databaseId' => function () {
					return ! empty( $this->ID ) ? $this->ID : null;
				},
				'name'       => function () {
					return ! empty( $this->data->get_zone_name() ) ? $this->data->get_zone_name() : null;
				},
				'order'      => function () {
					return $this->data->get_zone_order();
				},
				'locations'  => function () {
					return $this->data->get_zone_locations();
				},
				'methods'    => function () {
					return $this->data->get_shipping_methods();
				},
			];
		}
	}

	/**
	 * Forwards function calls to WC_Data sub-class instance.
	 *
	 * @param string $method - function name.
	 * @param array  $args  - function call arguments.
	 *
	 * @return mixed
	 *
	 * @throws \BadMethodCallException Method not found on WC data object.
	 */
	public function __call( $method, $args ) {
		if ( \is_callable( [ $this->data, $method ] ) ) {
			return $this->data->$method( ...$args );
		}

		$class = self::class;
		throw new \BadMethodCallException( "Call to undefined method {$method} on the {$class}" );
	}

	/**
	 * Returns the source WC_Data instance
	 *
	 * @return \WC_Shipping_Zone
	 */
	public function as_WC_Data() {
		return $this->data;
	}
}
