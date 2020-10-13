<?php

class CoreInterfaceQueriesTest extends \Codeception\TestCase\WPTestCase {
    public function setUp() {
        // before
        parent::setUp();

		// your set up methods here
		$this->products   = $this->getModule('\Helper\Wpunit')->product();
		$this->variations = $this->getModule('\Helper\Wpunit')->product_variation();
		$this->orders     = $this->getModule('\Helper\Wpunit')->order();
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
	}

	public function graphql() {
		$results = graphql( ...func_get_args() );

		// use --debug flag to view.
		\codecept_debug( $results );

		return $results;
	}

    // tests
    public function testProductAsNodeWithComments() {
		// Create product and review to be queried.
		$product_id = $this->products->create_simple();
		$comment_id = $this->factory()->comment->create(
            array(
                'comment_author'       => 'Rude customer',
                'comment_author_email' => 'rude-guy@example.com',
                'comment_post_ID'      => $product_id,
                'comment_content'      => 'It came covered in poop!!!',
                'comment_approved'     => 1,
                'comment_type'         => 'review',
            )
        );
		update_comment_meta( $comment_id, 'rating', 1 );

		// Define query and variables.
		$query     = '
			query ( $id: ID! ) {
				product( id: $id, idType: DATABASE_ID ) {
					id
					... on NodeWithComments {
						commentCount
						commentStatus
					}
				}
			}
		';
		$variables = array( 'id' => $product_id );

		// Execute query and retrieve response.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'product' => array(
					'id'            => \GraphQLRelay\Relay::toGlobalId( 'product', $product_id ),
					'commentCount'  => 1,
					'commentStatus' => 'open',
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );
	}

	public function testOrderAsNodeWithComments() {
		// Create order and order note to be queried.
		$order_id = $this->orders->create();
		$order    = \wc_get_order( $order_id );
		$order->add_order_note( 'testnote' );
		$order->add_order_note( 'testcustomernote', 1, true );

		// Define query and variables.
		$query    = '
			query ( $id: ID! ) {
				order( id: $id, idType: DATABASE_ID ) {
					id
					... on NodeWithComments {
						commentCount
						commentStatus
					}
				}
			}
		';
		$variables = array( 'id' => $order_id );

		/**
		 * Assertion One
		 *
		 * Authenticate as a shop manager and execute query and validate response.
		 */
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'shop_manager' ) ) );
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'order' => array(
					'id'            => \GraphQLRelay\Relay::toGlobalId( 'shop_order', $order_id ),
					'commentCount'  => 2,
					'commentStatus' => 'open',
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );

		/**
		 * Assertion Two
		 *
		 * Authenticate as a shop manager and execute query and confirm 'commentStatus' is 'closed'.
		 */
		wp_set_current_user( $order->get_customer_id() );
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'order' => array(
					'id'            => \GraphQLRelay\Relay::toGlobalId( 'shop_order', $order_id ),
					'commentCount'  => 1,
					'commentStatus' => 'closed',
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );
	}

	public function testProductAsNodeWithContentEditor() {
		// Create product to be queried.
		$product_id = $this->products->create_simple();
		$product    = \wc_get_product( $product_id );

		// Authenticate to view RAW content.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'shop_manager' ) ) );

		// Define query and variables.
		$query     = '
			query ( $id: ID!, $format: PostObjectFieldFormatEnum ) {
				product( id: $id, idType: DATABASE_ID ) {
					id
					... on NodeWithContentEditor {
						content( format: $format )
					}
				}
			}
		';
		$variables = array(
			'id'     => $product_id,
			'format' => 'RAW',
		);

		// Execute query and retrieve response.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'product' => array(
					'id'      => \GraphQLRelay\Relay::toGlobalId( 'product', $product_id ),
					'content' => $product->get_description(),
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );
	}

	public function testProductAsNodeWithFeaturedImage() {
		// Create product to be queried.
		$attachment_id = $this->factory()->attachment->create(
			array(
				'post_mime_type' => 'image/gif',
				'post_author' => $this->admin
			)
		);
		$product_id = $this->products->create_simple( array( 'image_id' => $attachment_id ) );

		// Define query and variables.
		$query     = '
			query ( $id: ID! ) {
				product( id: $id, idType: DATABASE_ID ) {
					id
					... on NodeWithFeaturedImage {
						featuredImageId
						featuredImageDatabaseId
					}
				}
			}
		';
		$variables = array( 'id' => $product_id );

		// Execute query and retrieve response.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'product' => array(
					'id'                      => \GraphQLRelay\Relay::toGlobalId( 'product', $product_id ),
					'featuredImageId'         => \GraphQLRelay\Relay::toGlobalId( 'post', $attachment_id ),
					'featuredImageDatabaseId' => $attachment_id,
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );
	}

	public function testProductAsContentNode() {
		// Create product to be queried.
		$product_id = $this->products->create_simple();
		$wc_product = \wc_get_product( $product_id );
		$wp_product = get_post( $product_id );

		// Define query and variables.
		$query     = '
			query ( $id: ID! ) {
				product( id: $id, idType: DATABASE_ID ) {
					id
					... on ContentNode {
						id
						databaseId
						date
						dateGmt
						enclosure
						status
						slug
						modified
						modifiedGmt
						guid
						desiredSlug
						link
						uri
						isRestricted
						isPreview
						previewRevisionDatabaseId
						previewRevisionId
					}
				}
			}
		';
		$variables = array( 'id' => $product_id );

		// Execute query and retrieve response.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'product' => array(
					'id'                        => \GraphQLRelay\Relay::toGlobalId( 'product', $product_id ),
					'databaseId'                => $wp_product->ID,
					'date'                      => (string) $wc_product->get_date_created(),
					'dateGmt'                   => \WPGraphQL\Utils\Utils::prepare_date_response( $wp_product->post_date_gmt ),
					'enclosure'                 => get_post_meta( $wp_product->ID, 'enclosure', true ) ?: null,
					'status'                    => $wp_product->post_status,
					'slug'                      => $wp_product->post_name,
					'modified'                  => (string) $wc_product->get_date_modified(),
					'modifiedGmt'               => \WPGraphQL\Utils\Utils::prepare_date_response( $wp_product->post_modified_gmt ),
					'guid'                      => $wp_product->guid,
					'desiredSlug'               => null,
					'link'                      => get_permalink( $wp_product->ID ),
					'uri'                       => str_ireplace( home_url(), '', get_permalink( $wp_product->ID ) ),
					'isRestricted'              => false,
					'isPreview'                 => null,
					'previewRevisionDatabaseId' => null,
					'previewRevisionId'         => null,
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );
	}

	public function testProductAsUniformResourceIdentifiable() {
		// Create product to be queried.
		$product_id = $this->products->create_simple();
		$product    = \wc_get_product( $product_id );
		$wp_product = get_post( $product_id );

		// Define query and variables.
		$query     = '
			query ( $id: ID! ) {
				product( id: $id, idType: DATABASE_ID ) {
					... on UniformResourceIdentifiable {
						id
						uri
					}
				}
			}
		';
		$variables = array( 'id' => $product_id );

		// Execute query and retrieve response.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'product' => array(
					'id'  => \GraphQLRelay\Relay::toGlobalId( 'product', $product_id ),
					'uri' => str_ireplace( home_url(), '', get_permalink( $wp_product->ID ) ),
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );
	}

	public function testNodeInterfacesOnProductVariation() {
		// Create product collection to be queried.
		$product_ids  = $this->variations->create( $this->products->create_variable() );
		$variation_id = $product_ids['variations'][1];
		$wc_product   = \wc_get_product( $variation_id );
		$wp_product   = get_post( $variation_id );

		// Define query and variables.
		$query     = '
			query ( $id: ID! ) {
				productVariation( id: $id, idType: DATABASE_ID ) {
					id
					... on ContentNode {
						databaseId
						date
						dateGmt
						enclosure
						status
						slug
						modified
						modifiedGmt
						guid
						desiredSlug
						link
						uri
						isRestricted
						isPreview
						previewRevisionDatabaseId
						previewRevisionId
					}
					... on NodeWithFeaturedImage {
						featuredImageId
						featuredImageDatabaseId
					}
				}
			}
		';
		$variables = array( 'id' => $variation_id );

		// Execute query and retrieve response.
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Define expected data object.
		$expected = array(
			'data' => array(
				'productVariation' => array(
					'id'                        => \GraphQLRelay\Relay::toGlobalId( 'product_variation', $variation_id ),
					'databaseId'                => $wp_product->ID,
					'date'                      => (string) $wc_product->get_date_created(),
					'dateGmt'                   => \WPGraphQL\Utils\Utils::prepare_date_response( $wp_product->post_date_gmt ),
					'enclosure'                 => get_post_meta( $wp_product->ID, 'enclosure', true ) ?: null,
					'status'                    => $wp_product->post_status,
					'slug'                      => $wp_product->post_name,
					'modified'                  => (string) $wc_product->get_date_modified(),
					'modifiedGmt'               => \WPGraphQL\Utils\Utils::prepare_date_response( $wp_product->post_modified_gmt ),
					'guid'                      => $wp_product->guid,
					'desiredSlug'               => null,
					'link'                      => get_permalink( $wp_product->ID ),
					'uri'                       => str_ireplace( home_url(), '', get_permalink( $wp_product->ID ) ),
					'isRestricted'              => false,
					'isPreview'                 => null,
					'previewRevisionDatabaseId' => null,
					'previewRevisionId'         => null,
					'featuredImageId'           => \GraphQLRelay\Relay::toGlobalId( 'post', $wc_product->get_image_id() ),
					'featuredImageDatabaseId'   => $wc_product->get_image_id(),
				),
			),
		);

		// Assert query response valid.
		$this->assertEquals( $expected, $response );
	}

	public function testQueryProductWithNodeByUri() {

	}

}
