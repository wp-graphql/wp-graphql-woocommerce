<?php

use GraphQLRelay\Relay;
class CouponQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $admin;
	private $shopManager;
	private $customer;
	private $coupon;

	public function setUp() {
		parent::setUp();

		$this->admin = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		$this->shopManager = $this->factory->user->create(
			array(
				'role' => 'shop_manager',
			)
		);
		$this->customer = $this->factory->user->create(
			array(
				'role' => 'customer',
			)
		);

		// Create a coupon
		$this->coupon = $this->create_coupon( '10off' );
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}


	/**
	 * Create a dummy coupon.
	 *
	 * @param string $coupon_code
	 * @param array  $meta
	 *
	 * @return WC_Coupon
	 */
	private function create_coupon( $coupon_code = 'dummycoupon', $meta = array() ) {
		// Insert post
		$coupon_id = wp_insert_post( array(
			'post_title'   => $coupon_code,
			'post_type'    => 'shop_coupon',
			'post_status'  => 'publish',
			'post_excerpt' => 'This is a dummy coupon',
		) );

		$meta = wp_parse_args( $meta, array(
			'discount_type'              => 'fixed_cart',
			'coupon_amount'              => '1',
			'individual_use'             => 'no',
			'product_ids'                => '',
			'exclude_product_ids'        => '',
			'usage_limit'                => '',
			'usage_limit_per_user'       => '',
			'limit_usage_to_x_items'     => '',
			'expiry_date'                => '',
			'free_shipping'              => 'no',
			'exclude_sale_items'         => 'no',
			'product_categories'         => array(),
			'exclude_product_categories' => array(),
			'minimum_amount'             => '',
			'maximum_amount'             => '',
			'customer_email'             => array(),
			'usage_count'                => '0',
		) );

		// Update meta.
		foreach ( $meta as $key => $value ) {
			update_post_meta( $coupon_id, $key, $value );
		}

		return new \WC_Coupon( $coupon_code );
	}

	// tests
	public function testCouponQuery() {
		$query     = '
			query CouponQuery( $id: ID! ){
				coupon( id: $id ) {
					id
					couponId
					code
					amount
					date
					modified
					discountType
					description
					dateExpiry
					usageCount
					individualUse
					usageLimit
					usageLimitPerUser
					limitUsageToXItems
					freeShipping
					excludeSaleItems
					minimumAmount
					maximumAmount
					emailRestrictions
					products {
						nodes {
							productId
						}
					}
					excludedProducts {
						nodes {
							productId
						}
					}
					productCategories {
						nodes {
							productCategoryId
						}
					}
					excludedProductCategories {
						nodes {
							productCategoryId
						}
					}
					usedBy {
						nodes {
							customerId
						}
					}
				}
			}
		';

		$coupon_id = Relay::toGlobalId( 'shop_coupon', $this->coupon->get_id() );
		$variables = wp_json_encode( array( 'id' => $coupon_id ) );
		$actual    = do_graphql_request( $query, 'CouponQuery', $variables );

		$expected = [
			'data' => [
				'coupon' => [
					'id'                        => $coupon_id,
					'couponId'                  => $this->coupon->get_id(),
					'code'                      => $this->coupon->get_code(),
					'amount'                    => $this->coupon->get_amount(),
					'date'                      => $this->coupon->get_date_created(),
					'modified'                  => $this->coupon->get_date_modified(),
					'discountType'              => $this->coupon->get_discount_type(),
					'description'               => $this->coupon->get_description(),
					'dateExpiry'                => $this->coupon->get_date_expires(),
					'usageCount'                => $this->coupon->get_usage_count(),
					'individualUse'             => $this->coupon->get_individual_use(),
					'usageLimit'                => $this->coupon->get_usage_limit(),
					'usageLimitPerUser'         => $this->coupon->get_usage_limit_per_user(),
					'limitUsageToXItems'        => $this->coupon->get_limit_usage_to_x_items(),
					'freeShipping'              => $this->coupon->get_free_shipping(),
					'excludeSaleItems'          => $this->coupon->get_exclude_sale_items(),
					'minimumAmount'             => $this->coupon->get_minimum_amount(),
					'maximumAmount'             => $this->coupon->get_maximum_amount(),
					'emailRestrictions'         => $this->coupon->get_email_restrictions(),
					'products'                  => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productId' => $id );
							},
							$this->coupon->get_product_ids()
						),
					],
					'excludedProducts'          => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productId' => $id );
							},
							$this->coupon->get_excluded_product_ids()
						),
					],
					'productCategories'         => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productCategoryId' => $id );
							},
							$this->coupon->get_product_categories()
						),
					],
					'excludedProductCategories' => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productCategoryId' => $id );
							},
							$this->coupon->get_excluded_product_categories()
						),
					],
					'usedBy'                    => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'customerId' => $id );
							},
							$this->coupon->get_used_by()
						),
					],
				],
			],
		];

		/**
		 * use --debug flag to view
		 */
		codecept_debug( $actual );

		/**
		 * use --debug flag to view
		 */
		codecept_debug( $expected );

		$this->assertEquals( $expected, $actual );
	}

	public function testCouponByQuery() {
		$wc_coupon = new WC_Coupon();
		$wc_coupon->set_code( '10off' );
		$wc_coupon->set_description( 'Test coupon' );
		$wc_coupon->set_discount_type( 'percent' );
		$wc_coupon->set_amount( floatval( 25 ) );
		$wc_coupon->set_individual_use( true );
		$wc_coupon->set_usage_limit( 1 );
		$wc_coupon->set_date_expires( strtotime( '+6 months' ) );
		$wc_coupon->set_free_shipping( false );
		$wc_coupon->save();

		$query = '
			query {
				couponBy( code: "10off" ) {
					couponId
					code
					amount
				}
			}
		';

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'couponBy' => [
					'couponId' => $wc_coupon->get_id(),
					'code'     => $wc_coupon->get_code(),
					'amount'   => $wc_coupon->get_amount(),
				],
			],
		];

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $expected );

		$this->assertEquals( $expected, $actual );
	}

	public function testCouponsQuery() {
		$wc_coupon = new WC_Coupon();
		$wc_coupon->set_code( '10off' );
		$wc_coupon->set_description( 'Test coupon' );
		$wc_coupon->set_discount_type( 'percent' );
		$wc_coupon->set_amount( floatval( 25 ) );
		$wc_coupon->set_individual_use( true );
		$wc_coupon->set_usage_limit( 1 );
		$wc_coupon->set_date_expires( strtotime( '+6 months' ) );
		$wc_coupon->set_free_shipping( false );
		$wc_coupon->save();

		$query = '
			query {
				coupons( where: { code: "10off" } ) {
					nodes {
						couponId
						code
						amount
					}
				}
			}
		';

		$actual = do_graphql_request( $query );

		$expected = [
			'data' => [
				'coupons' => [
					'nodes' => [
						[
							'couponId' => $wc_coupon->get_id(),
							'code'     => $wc_coupon->get_code(),
							'amount'   => $wc_coupon->get_amount(),
						],
					],
				],
			],
		];

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $expected );

		$this->assertEquals( $expected, $actual );
	}
}
