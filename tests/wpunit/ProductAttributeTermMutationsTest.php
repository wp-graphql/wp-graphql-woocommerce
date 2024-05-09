<?php

class ProductAttributeTermMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testCreateProductAttributeTerm() {
        $kind_attribute = $this->factory->product->createAttribute( 'kind', [ 'normal', 'special' ] );
        
        $query = '
            mutation ($input: CreateProductAttributeTermInput!) {
                createProductAttributeTerm(input: $input) {
                    term {
                        id
                        name
                        slug
                        description
                        menuOrder
                        count
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'attributeId' => $kind_attribute['attribute_id'],
                'name'        => 'Hated',
                'slug'        => 'hated',
                'description' => 'Hated by all',
                'menuOrder'   => 2,
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
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'createProductAttributeTerm.term',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Hated' ),
                    $this->expectedField( 'slug', 'hated' ),
                    $this->expectedField( 'description', 'Hated by all' ),
                    $this->expectedField( 'menuOrder', 2 ),
                    $this->expectedField( 'count', 0 ),
                ]
            )
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateProductAttributeTerm() {
        $kind_attribute = $this->factory->product->createAttribute( 'kind', [ 'normal', 'special', 'hated' ] );
        $hated_term_id  = get_term_by( 'slug', 'hated', 'pa_kind' )->term_id;

        $query = '
            mutation ($input: UpdateProductAttributeTermInput!) {
                updateProductAttributeTerm(input: $input) {
                    term {
                        id
                        name
                        slug
                        description
                        menuOrder
                        count
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'attributeId' => $kind_attribute['attribute_id'],
                'id'          => $hated_term_id,
                'name'        => 'Loved',
                'slug'        => 'loved',
                'description' => 'Loved by all',
                'menuOrder'   => 0,
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
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'updateProductAttributeTerm.term',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Loved' ),
                    $this->expectedField( 'slug', 'loved' ),
                    $this->expectedField( 'description', 'Loved by all' ),
                    $this->expectedField( 'menuOrder', 0 ),
                    $this->expectedField( 'count', 0 ),
                ]
            )
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testDeleteProductAttributeTerm() {
        $kind_attribute = $this->factory->product->createAttribute( 'kind', [ 'normal', 'special', 'hated' ] );
        $hated_term_id  = get_term_by( 'slug', 'hated', 'pa_kind' )->term_id;

        $query = '
            mutation ($input: DeleteProductAttributeTermInput!) {
                deleteProductAttributeTerm(input: $input) {
                    term {
                        id
                        slug
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'attributeId' => $kind_attribute['attribute_id'],
                'id'          => $hated_term_id,
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
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'deleteProductAttributeTerm.term',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'slug', 'hated' ),
                ]
            )
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }
}
