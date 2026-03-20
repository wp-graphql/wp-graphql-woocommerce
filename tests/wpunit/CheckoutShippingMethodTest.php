<?php
/**
 * Tests that the checkout mutation respects the chosen shipping method.
 *
 * Reproduces the issue where the shipping method from the checkout input
 * is overwritten by WooCommerce's default (cheapest) during calculate_totals().
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/927
 */
class CheckoutShippingMethodTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		parent::setUp();

		$this->loginAs( 0 );

		update_option( 'woocommerce_ship_to_countries', 'all' );
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );
		update_option( 'woocommerce_calc_taxes', 'no' );

		// Enable BACS payment gateway.
		$gateways     = \WC()->payment_gateways->payment_gateways();
		$bacs_gateway = $gateways['bacs'];
		$bacs_gateway->settings['enabled'] = 'yes';
		update_option( $bacs_gateway->get_option_key(), $bacs_gateway->settings );
		\WC()->payment_gateways->init();
	}

	/**
	 * Creates a shipping zone with 5 shipping methods ordered cheapest to most expensive.
	 *
	 * @return array<string, int|string> Zone ID and rate IDs for each method.
	 */
	private function create_shipping_zone_with_methods() {
		$zone = new \WC_Shipping_Zone();
		$zone->set_zone_name( 'US Shipping' );
		$zone->add_location( 'US', 'country' );
		$zone->save();

		// 1. Free shipping ($0).
		$free_id = $zone->add_shipping_method( 'free_shipping' );

		// 2. Flat rate - Economy ($5).
		$economy_id = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $economy_id . '_settings',
			[
				'title'      => 'Economy',
				'tax_status' => 'none',
				'cost'       => '5',
			]
		);

		// 3. Flat rate - Standard ($15).
		$standard_id = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $standard_id . '_settings',
			[
				'title'      => 'Standard',
				'tax_status' => 'none',
				'cost'       => '15',
			]
		);

		// 4. Flat rate - Express ($30).
		$express_id = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $express_id . '_settings',
			[
				'title'      => 'Express',
				'tax_status' => 'none',
				'cost'       => '30',
			]
		);

		// 5. Flat rate - Overnight ($50).
		$overnight_id = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $overnight_id . '_settings',
			[
				'title'      => 'Overnight',
				'tax_status' => 'none',
				'cost'       => '50',
			]
		);

		// Reload shipping methods so WooCommerce picks up the new zone.
		\WC_Cache_Helper::get_transient_version( 'shipping', true );
		\WC()->shipping()->load_shipping_methods();

		return [
			'zone_id'       => $zone->get_id(),
			'free_shipping' => 'free_shipping:' . $free_id,
			'economy'       => 'flat_rate:' . $economy_id,
			'standard'      => 'flat_rate:' . $standard_id,
			'express'       => 'flat_rate:' . $express_id,
			'overnight'     => 'flat_rate:' . $overnight_id,
		];
	}

	private function get_checkout_mutation() {
		return '
			mutation checkout( $input: CheckoutInput! ) {
				checkout( input: $input ) {
					order {
						id
						status
						shippingTotal
						total
						shippingLines {
							nodes {
								methodTitle
								total
							}
						}
					}
					result
				}
			}
		';
	}

	private function get_checkout_input( $shipping_method_id ) {
		return [
			'paymentMethod'  => 'bacs',
			'shippingMethod' => [ $shipping_method_id ],
			'billing'        => [
				'firstName' => 'John',
				'lastName'  => 'Doe',
				'address1'  => '123 Main St',
				'city'      => 'New York',
				'state'     => 'NY',
				'postcode'  => '10001',
				'country'   => 'US',
				'email'     => 'shipping-test-' . uniqid() . '@example.com',
				'phone'     => '555-555-1234',
				'overwrite' => true,
			],
		];
	}

	/**
	 * Tests that selecting a non-default (non-cheapest) shipping method
	 * in the checkout input results in the correct shipping on the order.
	 *
	 * The default WooCommerce behavior selects the cheapest method (free shipping).
	 * This test verifies the Express ($30) method is preserved through checkout.
	 */
	public function testCheckoutUsesInputShippingMethodNotDefault() {
		$shipping = $this->create_shipping_zone_with_methods();

		$product_id = $this->factory->product->createSimple(
			[
				'regular_price' => 50,
				'price'         => 50,
			]
		);

		\WC()->cart->add_to_cart( $product_id, 1 );

		$query     = $this->get_checkout_mutation();
		$variables = [ 'input' => $this->get_checkout_input( $shipping['express'] ) ];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		// The order should use Express ($30), not free shipping ($0).
		$expected = [
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.order.shippingTotal', '$30.00' ),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Express' ),
					$this->expectedField( 'total', '30' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Tests that the most expensive shipping method is correctly applied.
	 */
	public function testCheckoutUsesOvernightShipping() {
		$shipping = $this->create_shipping_zone_with_methods();

		$product_id = $this->factory->product->createSimple(
			[
				'regular_price' => 50,
				'price'         => 50,
			]
		);

		\WC()->cart->add_to_cart( $product_id, 1 );

		$query     = $this->get_checkout_mutation();
		$variables = [ 'input' => $this->get_checkout_input( $shipping['overnight'] ) ];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.order.shippingTotal', '$50.00' ),
			$this->expectedNode(
				'checkout.order.shippingLines.nodes',
				[
					$this->expectedField( 'methodTitle', 'Overnight' ),
					$this->expectedField( 'total', '50' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
