<?php
class UnsupportedProductTypeTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function testUnsupportedProductTypeFailsWhenDisabled() {
		$product_id = $this->factory->product->createSimple(
			[
				'product_class' => '\WC_Product_Advanced',
			]
		);

		$query = '
            query ($id: ID!) {
                product(id: $id) {
                    id
                }
            }
        ';

		$variables = [ 'id' => $this->toRelayId( 'product', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product', self::IS_NULL ),
		];

		$this->assertQueryError( $response, $expected );
	}

	public function testUnsupportedProductTypePassesWhenEnabled() {
		update_option(
			'woographql_settings',
			[ 'enable_unsupported_product_type' => 'on' ]
		);

		$product_id = $this->factory->product->createSimple(
			[ 'product_class' => '\WC_Product_Advanced' ]
		);

		$query = '
            query ($id: ID!) {
                product(id: $id) {
                    id
                    type
                }
            }
        ';

		$variables = [ 'id' => $this->toRelayId( 'product', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedField( 'product.type', 'UNSUPPORTED' ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		$query = '
            query ($id: ID!) {
                unsupportedProduct(id: $id) {
                    id
                    type
                }
            }
        ';

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'unsupportedProduct.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedField( 'unsupportedProduct.type', 'UNSUPPORTED' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
