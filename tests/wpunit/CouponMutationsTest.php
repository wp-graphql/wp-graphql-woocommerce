<?php

class CouponMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	// Tests
	public function testCreateCoupon() {
		$query = '
			mutation($input: CreateCouponInput!) {
				createCoupon(input: $input) {
					coupon {
						id
						databaseId
						code
						amount
						discountType
					}
				}
			}
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'some_id',
				'code'             => 'testcode',
				'amount'           => 0.25,
				'discountType'     => 'PERCENT',
			],
		];

		/**
		 * Assertion One
		 *
		 * Expect mutation to failed due to lack of capabilities
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedErrorPath( 'createCoupon' ),
			$this->expectedField( 'createCoupon', self::IS_NULL ),
		];

		$this->assertQueryError( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Try again as an authenticated customer and expect continued failure.
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQueryError( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Try as shop manager and expect mutation to succeed
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'createCoupon.coupon',
				[
					$this->expectedField( 'id', self::NOT_FALSY ),
					$this->expectedField( 'databaseId', self::NOT_FALSY ),
					$this->expectedField( 'code', 'testcode' ),
					$this->expectedField( 'amount', 0.25 ),
					$this->expectedField( 'discountType', 'PERCENT' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testUpdateCoupon() {
		$coupon_id = $this->factory->coupon->create();

		$query = '
			mutation($input: UpdateCouponInput!) {
				updateCoupon(input: $input) {
					coupon {
						id
						databaseId
						code
						amount
						discountType
					}
				}
			}
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'some_id',
				'id'               => $this->toRelayId( 'shop_coupon', $coupon_id ),
				'code'             => 'blahblah',
				'amount'           => 0.25,
				'discountType'     => 'PERCENT',
			],
		];

		/**
		 * Assertion One
		 *
		 * Expect mutation to failed due to lack of capabilities
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedErrorPath( 'updateCoupon' ),
			$this->expectedField( 'updateCoupon', self::IS_NULL ),
		];

		$this->assertQueryError( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Try again as an authenticated customer and expect continued failure.
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQueryError( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Try as shop manager and expect mutation to succeed
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'updateCoupon.coupon',
				[
					$this->expectedField( 'id', $this->toRelayId( 'shop_coupon', $coupon_id ) ),
					$this->expectedField( 'databaseId', $coupon_id ),
					$this->expectedField( 'code', 'blahblah' ),
					$this->expectedField( 'amount', 0.25 ),
					$this->expectedField( 'discountType', 'PERCENT' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testDeleteCoupon() {
		$coupon_id = $this->factory->coupon->create();

		$query = '
			mutation($input: DeleteCouponInput!) {
				deleteCoupon(input: $input) {
					coupon {
						id
						databaseId
					}
				}
			}
		';

		$variables = [
			'input' => [
				'clientMutationId' => 'some_id',
				'id'               => $this->toRelayId( 'shop_coupon', $coupon_id ),
			],
		];

		/**
		 * Assertion One
		 *
		 * Expect mutation to failed due to lack of capabilities
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedErrorPath( 'deleteCoupon' ),
			$this->expectedField( 'deleteCoupon', self::IS_NULL ),
		];

		$this->assertQueryError( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Try again as an authenticated customer and expect continued failure.
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQueryError( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Try as shop manager and expect mutation to succeed
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'deleteCoupon.coupon',
				[
					$this->expectedField( 'id', $this->toRelayId( 'shop_coupon', $coupon_id ) ),
					$this->expectedField( 'databaseId', $coupon_id ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
