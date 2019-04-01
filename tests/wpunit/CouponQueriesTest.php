<?php

use GraphQLRelay\Relay;
class CouponQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();
		$this->user = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testCouponQuery() {
		/**
		 * Create a coupon
		 */
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

		$coupon_id = Relay::toGlobalId( 'shop_coupon', $wc_coupon->get_id() );
		$variables = wp_json_encode( array( 'id' => $coupon_id ) );
		$actual    = do_graphql_request( $query, 'CouponQuery', $variables );

		$expected = [
			'data' => [
				'coupon' => [
					'id'                        => $coupon_id,
					'couponId'                  => $wc_coupon->get_id(),
					'code'                      => $wc_coupon->get_code(),
					'amount'                    => $wc_coupon->get_amount(),
					'date'                      => $wc_coupon->get_date_created(),
					'modified'                  => $wc_coupon->get_date_modified(),
					'discountType'              => $wc_coupon->get_discount_type(),
					'description'               => $wc_coupon->get_description(),
					'dateExpiry'                => $wc_coupon->get_date_expires(),
					'usageCount'                => $wc_coupon->get_usage_count(),
					'individualUse'             => $wc_coupon->get_individual_use(),
					'usageLimit'                => $wc_coupon->get_usage_limit(),
					'usageLimitPerUser'         => $wc_coupon->get_usage_limit_per_user(),
					'limitUsageToXItems'        => $wc_coupon->get_limit_usage_to_x_items(),
					'freeShipping'              => $wc_coupon->get_free_shipping(),
					'excludeSaleItems'          => $wc_coupon->get_exclude_sale_items(),
					'minimumAmount'             => $wc_coupon->get_minimum_amount(),
					'maximumAmount'             => $wc_coupon->get_maximum_amount(),
					'emailRestrictions'         => $wc_coupon->get_email_restrictions(),
					'products'                  => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productId' => $id );
							},
							$wc_coupon->get_product_ids()
						),
					],
					'excludedProducts'          => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productId' => $id );
							},
							$wc_coupon->get_excluded_product_ids()
						),
					],
					'productCategories'         => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productCategoryId' => $id );
							},
							$wc_coupon->get_product_categories()
						),
					],
					'excludedProductCategories' => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'productCategoryId' => $id );
							},
							$wc_coupon->get_excluded_product_categories()
						),
					],
					'usedBy'                    => [
						'nodes' => array_map(
							function( $id ) {
								return array( 'customerId' => $id );
							},
							$wc_coupon->get_used_by()
						),
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
