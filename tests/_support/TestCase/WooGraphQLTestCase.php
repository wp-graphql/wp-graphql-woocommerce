<?php
/**
 * WPGraphQL test case
 *
 * For testing WPGraphQL responses.
 *
 * @since 0.8.0
 * @package Tests\WPGraphQL\TestCase
 */
namespace Tests\WPGraphQL\WooCommerce\TestCase;

use Automattic\WooCommerce\Internal\ProductDownloads\ApprovedDirectories\Register as Download_Directories;

class WooGraphQLTestCase extends \Tests\WPGraphQL\TestCase\WPGraphQLTestCase {
	/**
	 * Holds the User ID of an user with the "shop_manager" role.
	 * For use through the tests for purpose of testing user access levels.
	 *
	 * @var integer
	 */
	protected $shop_manager;

	/**
	 * Holds the User ID of an user with the "customer/subscriber" role.
	 * For use through the tests for purpose of testing user access levels.
	 *
	 * @var integer
	 */
	protected $customer;

	/**
	 * Creates users and loads factories.
	 */
	public function setUp(): void {
		parent::setUp();

		// Load factories.
		$factories = [
			'Product',
			'ProductVariation',
			'Cart',
			'Coupon',
			'Customer',
			'ShippingZone',
			'TaxRate',
			'Order',
			'Refund',
			'PaymentToken',
		];

		foreach ( $factories as $factory ) {
			$factory_name                   = strtolower( preg_replace( '/\B([A-Z])/', '_$1', $factory ) );
			$factory_class                  = '\\Tests\\WPGraphQL\\WooCommerce\\Factory\\' . $factory . 'Factory';
			$this->factory->{$factory_name} = new $factory_class( $this->factory );
		}

		$this->factory->shipping_zone->createLegacyFlatRate();

		// Create test users.
		$this->shop_manager = $this->factory->user->create( [ 'role' => 'shop_manager' ] );
		$this->customer     = $this->factory->customer->create();

		// For these tests, we are not concerned with Approved Download Directory functionality.
		wc_get_container()->get( Download_Directories::class )->set_mode( Download_Directories::MODE_DISABLED );

		// Clear cached schema.
		$this->clearSchema();
	}

	public function tearDown(): void {
		\WC()->cart->empty_cart( true );

		// then
		parent::tearDown();
	}

	/**
	 * Logs in as a "shop manager"
	 */
	protected function loginAsShopManager() {
		$this->loginAs( $this->shop_manager );
	}

	/**
	 * Logs in as a "customer"
	 */
	protected function loginAsCustomer() {
		$this->loginAs( $this->customer );
	}

	/**
	 * Logs in as a specific user
	 */
	protected function loginAs( $customer_id = 0 ) {
		wp_set_current_user( $customer_id );
		\WC()->customer = new \WC_Customer( get_current_user_id(), true );
		\WC()->session->init();
	}

	/**
	 * Logs out current user.
	 */
	protected function logout() {
		wp_set_current_user( 0 );
	}

	/**
	 * The death of `! empty( $v ) ? apply_filters( $v ) : null;`
	 *
	 * @param array|mixed $possible   Variable whose existence has to be verified, or
	 * an array containing the variable followed by a decorated value to be returned.
	 * @param mixed       $default    Default value to be returned if $possible doesn't exist.
	 *
	 * @return mixed
	 */
	protected function maybe( $possible, $default = self::IS_NULL ) {
		if ( is_array( $possible ) && 2 === count( $possible ) ) {
			list( $possible, $decorated ) = $possible;
		} else {
			$decorated = $possible;
		}
		return ! empty( $possible ) ? $decorated : $default;
	}
}
