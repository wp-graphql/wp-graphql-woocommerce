<?php

use GraphQLRelay\Relay;
class CouponQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $coupon;
	private $helper;

	public function setUp() {
		parent::setUp();

		$this->shop_manager = $this->factory->user->create(
			array(
				'role' => 'shop_manager',
			)
		);
		$this->customer     = $this->factory->user->create(
			array(
				'role' => 'customer',
			)
		);
		$this->helper       = $this->getModule('\Helper\Wpunit')->coupon();
		$this->coupon       = $this->helper->create( '10off' );
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testCouponQuery() {
		$query     = '
			query couponQuery( $id: ID! ){
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

		/**
		 * Assertion One
		 */
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => Relay::toGlobalId( 'shop_coupon', $this->coupon ) );
		$actual    = do_graphql_request( $query, 'couponQuery', $variables );
		$expected  = array( 'data' => array( 'coupon' => $this->helper->get_query_data( $this->coupon ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCouponByQueryAndArgs() {
		wp_set_current_user( $this->customer );
		$id = Relay::toGlobalId( 'shop_coupon', $this->coupon );
		$coupon = new WC_Coupon( $this->coupon );
		$query = '
			query CouponBy( $id: ID, $couponId: Int, $code: String ) {
				couponBy( id: $id, couponId: $couponId, code: $code ) {
					id
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Testing "id" query argument
		 */
		$variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'CouponBy', $variables );
		$expected  = array( 'data' => array( 'couponBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * Testing "couponId" query argument
		 */
		$variables = array( 'couponId' => $coupon->get_id() );
		$actual    = do_graphql_request( $query, 'CouponBy', $variables );
		$expected  = array( 'data' => array( 'couponBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Three
		 * 
		 * Testing "couponId" query argument
		 */
		$variables = array( 'code' => $coupon->get_code() );
		$actual    = do_graphql_request( $query, 'CouponBy', $variables );
		$expected  = array( 'data' => array( 'couponBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCouponsQueryAndWhereArgs() {
		$coupons = array(
			'10off' => array( 'id' => Relay::toGlobalId( 'shop_coupon', $this->coupon ) ),
			'20off' => array( 'id' => Relay::toGlobalId( 'shop_coupon', $this->helper->create( '20off' ) ) ),
			'30off' => array( 'id' => Relay::toGlobalId( 'shop_coupon', $this->helper->create( '30off' ) ) ),
		);

		$query = '
			query CouponsQuery( $code: String ) {
				coupons( where: { code: $code } ) {
					nodes {
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Should return null due to lack of required capabilities
		 */
		wp_set_current_user( $this->customer );
		$actual    = do_graphql_request( $query ,'CouponsQuery' );
		$expected  = array( 'data' => array( 'coupons' => array ( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );


		/**
		 * Assertion Two
		 * 
		 * Should return data because user has required capabilities
		 */
		wp_set_current_user( $this->shop_manager );
		$actual    = do_graphql_request( $query, 'CouponsQuery' );
		$expected  = array( 'data' => array( 'coupons' => array( 'nodes' => array_reverse( array_values( $coupons ) ) ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Three
		 * 
		 * Should return data because user has required capabilities
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'code' => '10off' );
		$actual    = do_graphql_request( $query, 'CouponsQuery', $variables );
		$expected  = array( 'data' => array( 'coupons' => array( 'nodes' => array( $coupons['10off'] ) ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}
}
