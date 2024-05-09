<?php

class ProductAttributeMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testCreateProductAttribute() {
        $query = '
            mutation ($input: CreateProductAttributeInput!) {
                createProductAttribute(input: $input) {
                    attribute {
                        id
                        name
                        label
                        type
                        orderBy
                        hasArchives
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'name'        => 'Pattern',
                'slug'        => 'pattern',
                'orderBy'     => 'menu_order',
                'hasArchives' => false,
            ],
        ];

        // Assert mutation fails as unauthenticated user.
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );


        // Assert mutation fails as authenticated user without proper capabilities.
        $this->loginAsCustomer();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Assert mutation succeeds as authenticated user with proper capabilities.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'createProductAttribute.attribute',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'pattern' ),
                    $this->expectedField( 'label', 'Pattern' ),
                    $this->expectedField( 'type', 'select' ),
                    $this->expectedField( 'orderBy', 'menu_order' ),
                    $this->expectedField( 'hasArchives', false ),
                ]
            ),
        ];
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateProductAttribute() {
        $query = '
            mutation ($input: CreateProductAttributeInput!) {
                createProductAttribute(input: $input) {
                    attribute {
                        id
                        name
                        label
                        type
                        orderBy
                        hasArchives
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'name'        => 'Pattern',
                'slug'        => 'pattern',
                'orderBy'     => 'menu_order',
                'hasArchives' => false,
            ],
        ];

        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'createProductAttribute.attribute',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'hasArchives', false ),
                ]
            ),
        ];
        $this->assertQuerySuccessful( $response, $expected );

        $attribute_id = $this->lodashGet( $response, 'data.createProductAttribute.attribute.id' );
        $this->assertNotEmpty( $attribute_id );

        $query = '
            mutation ($input: UpdateProductAttributeInput!) {
                updateProductAttribute(input: $input) {
                    attribute {
                        id
                        name
                        label
                        type
                        orderBy
                        hasArchives
                    }
                }
            }
        ';
        
        $variables = [
            'input' => [
                'id'          => $attribute_id,
                'name'        => 'Pattern',
                'slug'        => 'pattern',
                'orderBy'     => 'menu_order',
                'hasArchives' => true,
            ],
        ];

        // Assert mutation fails as unauthenticated user.
        $this->loginAs( 0 );
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );


        // Assert mutation fails as authenticated user without proper capabilities.
        $this->loginAsCustomer();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Assert mutation succeeds as authenticated user with proper capabilities.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'updateProductAttribute.attribute',
                [
                    $this->expectedField( 'id', $attribute_id ),
                    $this->expectedField( 'name', 'pattern' ),
                    $this->expectedField( 'label', 'Pattern' ),
                    $this->expectedField( 'type', 'select' ),
                    $this->expectedField( 'orderBy', 'menu_order' ),
                    $this->expectedField( 'hasArchives', true ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testDeleteProductAttribute() {
        $query = '
            mutation ($input: CreateProductAttributeInput!) {
                createProductAttribute(input: $input) {
                    attribute {
                        id
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'name'        => 'Pattern',
                'slug'        => 'pattern',
                'orderBy'     => 'menu_order',
                'hasArchives' => false,
            ],
        ];

        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQuerySuccessful( $response );

        $attribute_id = $this->lodashGet( $response, 'data.createProductAttribute.attribute.id' );
        $this->assertNotEmpty( $attribute_id );

        $query = '
            mutation ($input: DeleteProductAttributeInput!) {
                deleteProductAttribute(input: $input) {
                    attribute {
                        id
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'id' => $attribute_id,
            ],
        ];

        // Assert mutation fails as unauthenticated user.
        $this->loginAs( 0 );
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );


        // Assert mutation fails as authenticated user without proper capabilities.
        $this->loginAsCustomer();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Assert mutation succeeds as authenticated user with proper capabilities.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'deleteProductAttribute.attribute',
                [
                    $this->expectedField( 'id', $attribute_id ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }
}
