<?php

/**
 * Tests that ProductAttribute.name and VariationAttribute.name are consistent
 * for both local and global attributes.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/965
 */
class AttributeNameConsistencyTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Test that local and global ProductAttribute names are sanitized
	 * and match VariationAttribute names.
	 */
	public function testAttributeNameConsistencyBetweenProductAndVariation() {
		$product_ids  = $this->factory->product_variation->createSome();
		$product_id   = $product_ids['product'];
		$variation_id = $product_ids['variations'][0];

		$query = '
			query ($productId: ID!, $variationId: ID!) {
				product(id: $productId) {
					... on VariableProduct {
						attributes {
							nodes {
								name
								label
								scope
							}
						}
					}
				}
				productVariation(id: $variationId) {
					attributes {
						nodes {
							name
							label
							value
						}
					}
				}
			}
		';

		$variables = [
			'productId'   => $this->toRelayId( 'post', $product_id ),
			'variationId' => $this->toRelayId( 'post', $variation_id ),
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Get the variation's attribute names to use as expected values.
		$product   = wc_get_product( $variation_id );
		$var_attrs = $product->get_attributes();

		$expected = [];
		foreach ( $var_attrs as $name => $value ) {
			$is_taxonomy = taxonomy_exists( $name );

			// The ProductAttribute.name should be sanitized and match the VariationAttribute.name.
			$expected[] = $this->expectedNode(
				'product.attributes.nodes',
				[
					$this->expectedField( 'name', $is_taxonomy ? $name : sanitize_title( $name ) ),
				]
			);

			// The VariationAttribute should have the same name.
			$expected[] = $this->expectedNode(
				'productVariation.attributes.nodes',
				[
					$this->expectedField( 'name', $is_taxonomy ? $name : sanitize_title( $name ) ),
				]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
	}
}
