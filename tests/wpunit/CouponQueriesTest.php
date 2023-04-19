<?php

use GraphQLRelay\Relay;
class CouponQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function expectedCouponData( $coupon_id ) {
		$coupon = new \WC_Coupon( $coupon_id );

		$expected = [
			$this->expectedField( 'coupon.id', $this->toRelayId( 'shop_coupon', $coupon_id ) ),
			$this->expectedField( 'coupon.databaseId', $coupon->get_id() ),
			$this->expectedField( 'coupon.code', $coupon->get_code() ),
			$this->expectedField( 'coupon.amount', floatval( $coupon->get_amount() ) ),
			$this->expectedField( 'coupon.date', $coupon->get_date_created()->__toString() ),
			$this->expectedField( 'coupon.modified', $coupon->get_date_modified()->__toString() ),
			$this->expectedField( 'coupon.discountType', strtoupper( $coupon->get_discount_type() ) ),
			$this->expectedField( 'coupon.description', $coupon->get_description() ),
			$this->expectedField( 'coupon.dateExpiry', $this->maybe( $coupon->get_date_expires(), self::IS_NULL ) ),
			$this->expectedField( 'coupon.usageCount', $coupon->get_usage_count() ),
			$this->expectedField( 'coupon.individualUse', $coupon->get_individual_use() ),
			$this->expectedField( 'coupon.usageLimit', $this->maybe( $coupon->get_usage_limit(), self::IS_NULL ) ),
			$this->expectedField( 'coupon.usageLimitPerUser', $this->maybe( $coupon->get_usage_limit_per_user(), self::IS_NULL ) ),
			$this->expectedField( 'coupon.limitUsageToXItems', $this->maybe( $coupon->get_limit_usage_to_x_items(), self::IS_NULL ) ),
			$this->expectedField( 'coupon.freeShipping', $coupon->get_free_shipping() ),
			$this->expectedField( 'coupon.excludeSaleItems', $coupon->get_exclude_sale_items() ),
			$this->expectedField( 'coupon.minimumAmount', $this->maybe( $coupon->get_minimum_amount(), self::IS_NULL ) ),
			$this->expectedField( 'coupon.maximumAmount', $this->maybe( $coupon->get_maximum_amount(), self::IS_NULL ) ),
			$this->expectedField( 'coupon.emailRestrictions', $this->maybe( $coupon->get_email_restrictions(), self::IS_NULL ) ),
		];

		foreach ( $coupon->get_product_ids() as $product_id ) {
			$expected[] = $this->expectedNode( 'coupon.products.nodes', [ 'databaseId' => $product_id ] );
		}

		foreach ( $coupon->get_excluded_product_ids() as $product_id ) {
			$expected[] = $this->expectedNode( 'coupon.excludedProducts.nodes', [ 'databaseId' => $product_id ] );
		}

		foreach ( $coupon->get_product_categories() as $category_id ) {
			$expected[] = $this->expectedNode( 'coupon.productCategories.nodes', [ 'productCategoryId' => $category_id ] );
		}

		foreach ( $coupon->get_excluded_product_categories() as $category_id ) {
			$expected[] = $this->expectedNode( 'coupon.excludedProductCategories.nodes', [ 'productCategoryId' => $category_id ] );
		}

		foreach ( $coupon->get_used_by() as $customer_id ) {
			$expected[] = $this->expectedNode( 'coupon.usedBy.nodes', [ 'databaseId' => $customer_id ] );
		}

		return $expected;
	}

	// tests
	public function testCouponQuery() {
		$coupon_id = $this->factory->coupon->create(
			[
				'code'                 => '10off',
				'amount'               => 10,
				'discount_type'        => 'percent',
				'product_ids'          => [ $this->factory->product->createSimple() ],
				'excluded_product_ids' => [ $this->factory->product->createSimple() ],
			]
		);

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
		 *
		 * Confirm customer's can't query coupons by ID.
		 */
		$this->loginAsCustomer();
		$variables = [ 'id' => $this->toRelayId( 'shop_coupon', $coupon_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [ $this->expectedField( 'coupon', self::IS_NULL ) ];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Confirm shop managers can query coupons by ID.
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $this->expectedCouponData( $coupon_id ) );
	}

	public function testCouponQueryAndIds() {
		$coupon_id = $this->factory->coupon->create();
		$coupon    = new \WC_Coupon( $coupon_id );
		$relay_id  = $this->toRelayId( 'shop_coupon', $coupon_id );

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
		$this->loginAsShopManager();
		$variables = [
			'id'     => $relay_id,
			'idType' => 'ID',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [ $this->expectedField( 'coupon.id', $relay_id ) ];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Testing "DATABASE_ID" ID type
		 */
		$variables = [
			'id'     => $coupon_id,
			'idType' => 'DATABASE_ID',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Testing "CODE" ID type.
		 */
		$variables = [
			'id'     => $coupon->get_code(),
			'idType' => 'CODE',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCouponsQueryAndWhereArgs() {
		$coupons = [
			$this->factory->coupon->create(),
			$this->factory->coupon->create(
				[
					'code'          => '20off',
					'amount'        => 20,
					'discount_type' => 'percent',
				]
			),
			$this->factory->coupon->create(
				[
					'code'          => 'testcode',
					'amount'        => 30,
					'discount_type' => 'percent',
				]
			),
		];

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
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'coupons.nodes', self::IS_FALSY ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Two
		 *
		 * Should return data because user has required capabilities
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['0'] ) ] ),
			$this->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['1'] ) ] ),
			$this->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['2'] ) ] ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Three
		 *
		 * Tests 'code' where argument
		 */
		$variables = [ 'code' => 'testcode' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['2'] ) ] ),
			$this->not()->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['0'] ) ] ),
			$this->not()->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['1'] ) ] ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Four
		 *
		 * Tests 'include' where argument
		 */
		$variables = [ 'include' => $coupons[0] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['0'] ) ] ),
			$this->not()->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['1'] ) ] ),
			$this->not()->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['2'] ) ] ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Five
		 *
		 * Tests 'exclude' where argument
		 */
		$variables = [ 'exclude' => $coupons[0] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->not()->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['0'] ) ] ),
			$this->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['1'] ) ] ),
			$this->expectedNode( 'coupons.nodes', [ 'id' => $this->toRelayId( 'shop_coupon', $coupons['2'] ) ] ),

		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
