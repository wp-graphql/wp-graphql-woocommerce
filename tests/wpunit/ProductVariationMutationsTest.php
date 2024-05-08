<?php

class ProductVariationMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testCreateProductVariation() {
        $kind_attribute = $this->factory->product->createAttribute( 'kind', [ 'normal', 'special' ] );
        $product_id = $this->factory->product->createVariable(
            [
                'attributes'     => [
                    $this->factory->product->createAttributeObject(
                        $kind_attribute['attribute_id'],
                        $kind_attribute['attribute_taxonomy'],
                        $kind_attribute['term_ids'],
                        0,
                        true,
                        true,
                    ),
                ],
                'attribute_data' => [],
            ]
        );

        $query = '
            mutation ($input: CreateProductVariationInput!) {
                createProductVariation(input: $input) {
                    variation {
                        id
                        parent { node { id } }
                        price
                        regularPrice
                        salePrice
                        attributes {
                            nodes {
                                id
                                name
                                value
                            }
                        }
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'productId'    => $product_id,
                'regularPrice' => 14.99,
                'salePrice'    => 9.99,
                'attributes'   => [
                    [
                        'id'             => $kind_attribute['attribute_id'],
                        'attributeName'  => $kind_attribute['attribute_name'],
                        'attributeValue' => 'special',
                    ],
                ],
            ],
        ];
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'createProductVariation.variation',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'parent.node.id', $this->toRelayId( 'product', $product_id ) ),
                    $this->expectedField( 'price', "$9.99" ),
                    $this->expectedField( 'regularPrice', "$14.99" ),
                    $this->expectedField( 'salePrice', "$9.99" ),
                    $this->expectedNode(
                        'attributes.nodes',
                        [
                            $this->expectedField( 'id', self::NOT_NULL ),
                            $this->expectedField( 'name', $kind_attribute['attribute_taxonomy'] ),
                            $this->expectedField( 'value', 'special' ),
                        ]
                    ),
                ]
            )
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateProductVariation() {
        $ids = $this->factory->product_variation->createSome( 
            $this->factory->product->createVariable()
        );

        $query = '
            mutation ($input: UpdateProductVariationInput!) {
                updateProductVariation(input: $input) {
                    variation {
                        id
                        parent { node { id } }
                        price
                        regularPrice
                        salePrice
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'productId'    => $ids['product'],
                'id'           => $ids['variations'][0],
                'regularPrice' => 19.99,
                'salePrice'    => 14.99,
            ],
        ];
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'updateProductVariation.variation',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'product_variation', $ids['variations'][0] ) ),
                    $this->expectedField( 'parent.node.id', $this->toRelayId( 'product', $ids['product'] ) ),
                    $this->expectedField( 'price', "$14.99" ),
                    $this->expectedField( 'regularPrice', "$19.99" ),
                    $this->expectedField( 'salePrice', "$14.99" ),
                ]
            )
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testDeleteProductVariation() {
        $ids = $this->factory->product_variation->createSome( 
            $this->factory->product->createVariable()
        );

        $query = '
            mutation ($input: DeleteProductVariationInput!) {
                deleteProductVariation(input: $input) {
                    variation {
                        id
                        parent { node { id } }
                        price
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'id'    => $ids['variations'][0],
                'force' => true,
            ],
        ];

        $response = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        $this->loginAsCustomer();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'deleteProductVariation.variation',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'product_variation', $ids['variations'][0] ) ),
                    $this->expectedField( 'parent.node.id', $this->toRelayId( 'product', $ids['product'] ) ),
                    $this->expectedField( 'price', self::NOT_NULL ),
                ]
            )
        ];

        $this->assertQuerySuccessful( $response, $expected );

        // $variation = wc_get_product( $ids['variations'][0] );
        // $this->assertFalse( $variation );
    }
}
