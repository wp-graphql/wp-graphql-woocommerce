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

	public function testCouponByQuery() {
		$query = '
			query {
				couponBy( code: "10off" ) {
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
		$actual    = do_graphql_request( $query );
		$expected  = array( 'data' => array( 'couponBy' => $this->helper->get_query_data( $this->coupon ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCouponsQuery() {
		$query = '
			query {
				coupons {
					nodes {
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
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * Assertion One
		 * 
		 * Should return null due to lack of required capabilities
		 */
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => Relay::toGlobalId( 'shop_coupon', $this->coupon ) );
		$actual    = do_graphql_request( $query );
		$expected  = array( 'data' => array( 'coupons' => array ( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );


		/**
		 * Assertion One
		 * 
		 * Should return data because user has required capabilities
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'id' => Relay::toGlobalId( 'shop_coupon', $this->coupon ) );
		$actual    = do_graphql_request( $query );
		
		// Get array of coupon IDs.
		$coupons = get_posts(
			array(
				'post_type'   => 'shop_coupon',
				'count_total' => false,
				'order'       => 'DESC',
				'fields'      => 'ids',
			)
		);

		$expected  = array(
			'data' => array(
				'coupons' => $this->getModule('\Helper\Wpunit')->get_nodes( $coupons, $this->helper ),
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}
}
