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
