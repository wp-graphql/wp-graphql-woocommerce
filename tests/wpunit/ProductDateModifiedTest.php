<?php
/**
 * Test Product dateModified field in GraphQL
 */

class ProductDateModifiedTest extends \Tests\WPGraphQL\TestCase\WPGraphQLTestCase {

	public function setUp(): void {
		parent::setUp();

		// Create a test product
		$this->product_id = $this->factory->product->create(
			array(
				'name' => 'Test Product',
			)
		);

		// Modify the product so we know date_modified is set
		$product = wc_get_product( $this->product_id );
		$product->set_name( 'Updated Test Product' );
		$product->save();
	}

	public function tearDown(): void {
		parent::tearDown();
		wp_delete_post( $this->product_id, true );
	}

	public function testProductHasDateModifiedField() {
		$query = '
        {
          product(id: "' . $this->product_id . '", idType: DATABASE_ID) {
            ... on SimpleProduct {
              id
              name
              dateModified
            }
          }
        }';

		$response = $this->graphql( array( 'query' => $query ) );

		$this->assertArrayNotHasKey( 'errors', $response, print_r( $response, true ) );
		$this->assertArrayHasKey( 'data', $response );
		$this->assertArrayHasKey( 'product', $response['data'] );
		$this->assertNotNull( $response['data']['product']['dateModified'] );
	}
}
