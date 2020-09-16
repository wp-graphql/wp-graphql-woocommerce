<?php

use GraphQLRelay\Relay;
class CouponQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $coupon;
	private $helper;

	public function setUp() {
		parent::setUp();

		$this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer     = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->helper       = $this->getModule('\Helper\Wpunit')->coupon();
		$this->coupon       = $this->helper->create(
			array(
				'code'          => '10off',
				'amount'        => 10,
				'discount_type' => 'percent',
			)
		);
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testCouponQuery() {
		$query = '
			query ($id: ID!){
				coupon(id: $id) {
					id
					databaseId
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
							... on SimpleProduct {
								databaseId
							}
						}
					}
					excludedProducts {
						nodes {
							... on SimpleProduct {
								databaseId
							}
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
							databaseId
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
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array( 'data' => array( 'coupon' => $this->helper->print_query( $this->coupon ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCouponQueryAndIds() {
		wp_set_current_user( $this->customer );
		$id = Relay::toGlobalId( 'shop_coupon', $this->coupon );
		$coupon = new WC_Coupon( $this->coupon );
		$query = '
			query ($id: ID!, $idType: CouponIdTypeEnum) {
				coupon(id: $id, idType: $idType) {
					id
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Testing "ID" ID type.
		 */
		$variables = array(
			'id'     => $id,
			'idType' => 'ID',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array( 'data' => array( 'coupon' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * Testing "DATABASE_ID" ID type
		 */
		$variables = array(
			'id'     => $coupon->get_id(),
			'idType' => 'DATABASE_ID',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array( 'data' => array( 'coupon' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * Testing "CODE" ID type.
		 */
		$variables = array(
			'id'     => $coupon->get_code(),
			'idType' => 'CODE',
		);
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array( 'data' => array( 'coupon' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCouponsQueryAndWhereArgs() {
		$coupons = array(
			$this->coupon,
			$this->helper->create(
				array(
					'code'          => '20off',
					'amount'        => 20,
					'discount_type' => 'percent',
				)
			),
			$this->helper->create(
				array(
					'code'          => '30off',
					'amount'        => 30,
					'discount_type' => 'percent',
				)
			),
		);

		$query = '
			query ($code: String, $include: [Int], $exclude: [Int]) {
				coupons(where: { code: $code, include: $include, exclude: $exclude }) {
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
		$actual    = graphql( array( 'query' => $query ) );
		$expected  = array( 'data' => array( 'coupons' => array ( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );


		/**
		 * Assertion Two
		 *
		 * Should return data because user has required capabilities
		 */
		wp_set_current_user( $this->shop_manager );
		$actual    = graphql( array( 'query' => $query ) );
		$expected  = array( 'data' => array( 'coupons' => array( 'nodes' => $this->helper->print_nodes( $coupons ) ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * Tests 'code' where argument
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'code' => '10off' );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'coupons' => array(
					'nodes' => $this->helper->print_nodes(
						$coupons,
						array(
							'filter' => function( $id ) {
								$coupon = new \WC_Coupon( $id );
								return '10off' === $coupon->get_code();
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Four
		 *
		 * Tests 'include' where argument
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'include' => $coupons[0] );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'coupons' => array(
					'nodes' => $this->helper->print_nodes(
						$coupons,
						array(
							'filter' => function( $id ) use( $coupons ) {
								return $coupons[0] === $id;
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Five
		 *
		 * Tests 'exclude' where argument
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'exclude' => $coupons[0] );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'coupons' => array(
					'nodes' => $this->helper->print_nodes(
						$coupons,
						array(
							'filter' => function( $id ) use( $coupons ) {
								return $coupons[0] !== $id;
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}
}
