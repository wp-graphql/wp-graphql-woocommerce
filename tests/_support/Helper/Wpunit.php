<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Wpunit extends \Codeception\Module {
	/**
	 * HOOK:
	 * triggered after module is created and configuration is loaded
	 */
	public function _initialize() {
		// Helper classes
		require_once __DIR__ . '/crud-helpers/wcg-helper.php';
		require_once __DIR__ . '/crud-helpers/customer.php';
		require_once __DIR__ . '/crud-helpers/coupon.php';
		require_once __DIR__ . '/crud-helpers/product.php';
		require_once __DIR__ . '/crud-helpers/product-variation.php';
		require_once __DIR__ . '/crud-helpers/shipping-method.php';
		require_once __DIR__ . '/crud-helpers/tax-rate.php';
		require_once __DIR__ . '/crud-helpers/order-item.php';
		require_once __DIR__ . '/crud-helpers/order.php';
		require_once __DIR__ . '/crud-helpers/refund.php';
		require_once __DIR__ . '/crud-helpers/cart.php';
		require_once __DIR__ . '/../Utils/class-wc-product-advanced.php';
	}

	/**
	 * HOOK:
	 * executed before suite.
	 */
	public function _beforeSuite( $settings = null ) {
		$helper = $this->product();
		$helper->create_attribute( 'size', [ 'small', 'medium', 'large' ] );
		$helper->create_attribute( 'color', [ 'red', 'blue', 'green' ] );
		codecept_debug( 'ATTRIBUTES_LOADED' );
		add_action( 'init_graphql_request', [ __CLASS__, 'shortcode_test_init' ] );
		codecept_debug( 'SHORTCODE_INITIALIZED' );
		\Stripe\Stripe::setApiKey(
			defined( 'STRIPE_API_SECRET_KEY' ) ? STRIPE_API_SECRET_KEY : getenv( 'STRIPE_API_SECRET_KEY' )
		);
	}

	public function cart() {
		return \CartHelper::instance();
	}

	public function coupon() {
		return \CouponHelper::instance();
	}

	public function customer() {
		return \CustomerHelper::instance();
	}

	public function order() {
		return \OrderHelper::instance();
	}

	public function item() {
		return \OrderItemHelper::instance();
	}

	public function product() {
		return \ProductHelper::instance();
	}

	public function product_variation() {
		return \ProductVariationHelper::instance();
	}

	public function refund() {
		return \RefundHelper::instance();
	}

	public function shipping_method() {
		return \ShippingMethodHelper::instance();
	}

	public function tax_rate() {
		return \TaxRateHelper::instance();
	}

	public function get_nodes( $ids, $crud ) {
		$nodes = [];
		foreach ( $ids as $id ) {
			$nodes[] = $crud->get_query_data( $id );
		}

		return [ 'nodes' => $nodes ];
	}

	public function clear_loader_cache( $loader_name ) {
		$loader = \WPGraphQL::get_app_context()->get_loader( $loader_name );
		$loader->clearAll();
	}

	public static function shortcode_test_init() {
		add_shortcode( 'shortcode_test', [ __CLASS__, 'shortcode_test_handler' ] );
	}

	public static function shortcode_test_handler( $atts ) {
		return '<p>This is the product description.</p>';
	}
}
