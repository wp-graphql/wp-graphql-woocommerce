<?php

class CollectionStatsQueryTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function setUp(): void {
        parent::setUp();
        update_option( 'woocommerce_attribute_lookup_enabled', 'yes' );
        update_option( 'woocommerce_attribute_lookup_direct_updates', 'yes' );
    }
    
    public function testCollectionStatsQuery() {
        $this->factory->product_variation->createSome(
			$this->factory->product->createVariable()
		);
        $this->factory->product->createSimple();
        $this->factory->product->createSimple();
        $this->factory->product_variation->createSome(
			$this->factory->product->createVariable()
		);

        $query = '
            query ($where: CollectionStatsWhereArgs, $taxonomies: [CollectionStatsQueryInput]) {
                collectionStats(
                    calculatePriceRange: true
                    calculateRatingCounts: true
                    calculateStockStatusCounts: true
                    taxonomies: $taxonomies
                    where: $where
                ) {
                    attributeCounts {
                        name
                        slug
                        label
                        terms {
                            node { slug }
                            termId
                            count
                        }
                    }
                    stockStatusCounts {
                        status
                        count
                    }
                }
            }
        ';

        $variables = [
            'where' => [
                'attributes' => [
                    [
                        'taxonomy' => 'PA_COLOR',
                        'terms'    => 'red',
                        'operator' => 'IN',
                    ],
                ]
            ],
            'taxonomies' => [
                [
                    'taxonomy' => 'PA_COLOR',
                    'relation' => 'AND',
                ]
            ]
        ];
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedNode(
                'collectionStats.attributeCounts', 
                [
                    $this->expectedField('slug', 'PA_COLOR' ),
                    $this->expectedField('label', 'color' ),
                    $this->expectedField('name', 'color' ),
                    $this->expectedNode(
                        'terms',
                        [
                            $this->expectedField( 'node.slug', 'red' ),
                            $this->expectedField( 'count', 2 ),
                            $this->expectedField( 'termId', self::NOT_FALSY ),
                        ] 
                    ),
                    $this->expectedNode(
                        'terms',
                        [
                            $this->expectedField( 'node.slug', 'blue' ),
                            $this->expectedField( 'count', 2 ),
                            $this->expectedField( 'termId', self::NOT_FALSY ),
                        ] 
                    ),
                    $this->expectedNode(
                        'terms',
                        [
                            $this->expectedField( 'node.slug', 'green' ),
                            $this->expectedField( 'count', 2 ),
                            $this->expectedField( 'termId', self::NOT_FALSY ),
                        ] 
                    ),
                ],
                0
            ),
            $this->expectedNode(
                'collectionStats.stockStatusCounts',
                [
                    $this->expectedField( 'status', 'IN_STOCK' ),
                    $this->expectedField( 'count', 2 ),
                ]
            ),
            $this->expectedNode(
                'collectionStats.stockStatusCounts',
                [
                    $this->expectedField( 'status', 'OUT_OF_STOCK' ),
                    $this->expectedField( 'count', 0 ),
                ]
            ),
            $this->expectedNode(
                'collectionStats.stockStatusCounts',
                [
                    $this->expectedField( 'status', 'ON_BACKORDER' ),
                    $this->expectedField( 'count', 0 ),
                ]
            ),
        ];
        $this->assertQuerySuccessful( $response, $expected );
    }
}