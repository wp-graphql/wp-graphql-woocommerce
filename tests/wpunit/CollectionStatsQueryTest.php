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
                        slug
                        label
						name
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

		$variables = array(
			'where'      => array(
				'attributes' => array(
					'queries' => array(
						array(
							'taxonomy' => 'PA_COLOR',
							'terms'    => 'red',
							'operator' => 'IN',
						),
					),
				),
			),
			'taxonomies' => array(
				array(
					'taxonomy' => 'PA_COLOR',
					'relation' => 'AND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_COLOR' ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'red' ),
							$this->expectedField( 'count', 2 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						)
					),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'blue' ),
							$this->expectedField( 'count', 2 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						)
					),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'green' ),
							$this->expectedField( 'count', 2 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						)
					),
				),
				0
			),
			$this->expectedNode(
				'collectionStats.stockStatusCounts',
				array(
					$this->expectedField( 'status', 'IN_STOCK' ),
					$this->expectedField( 'count', 2 ),
				)
			),
			$this->expectedNode(
				'collectionStats.stockStatusCounts',
				array(
					$this->expectedField( 'status', 'OUT_OF_STOCK' ),
					$this->expectedField( 'count', 0 ),
				)
			),
			$this->expectedNode(
				'collectionStats.stockStatusCounts',
				array(
					$this->expectedField( 'status', 'ON_BACKORDER' ),
					$this->expectedField( 'count', 0 ),
				)
			),
		);
		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCollectionStatsQueryWithWhereArgs() {
		// Create product attributes.
		$kind_attribute  = $this->factory->product->createAttribute( 'kind', array( 'special', 'normal' ), 'Product type' );
		$normal_term_id  = get_term_by( 'slug', 'normal', 'pa_kind' )->term_id;
		$special_term_id = get_term_by( 'slug', 'special', 'pa_kind' )->term_id;

		// Create attribute objects.
		$kind_attribute_normal_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $normal_term_id )
		);

		$kind_attribute_special_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $special_term_id )
		);

		$kind_attribute_both = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $normal_term_id, $special_term_id )
		);

		// Create taxonomies.
		$clothing_category_id = $this->factory->product->createProductCategory( 'clothing' );
		$shoes_tag_id         = $this->factory->product->createProductTag( 'shoes' );

		// Create products.
		$post_ids = $this->factory->product->createManySimple(
			20,
			array(
				'attributes'         => array( $kind_attribute_normal_only ),
				'category_ids'       => array( $clothing_category_id ),
				'default_attributes' => array( 'pa_kind' => 'normal' ),
			)
		);
		$this->factory->product->createManySimple(
			5,
			array(
				'category_ids'       => array( $clothing_category_id ),
				'attributes'         => array( $kind_attribute_special_only ),
				'default_attributes' => array( 'pa_kind' => 'special' ),
			)
		);
		$this->factory->product->createManySimple(
			5,
			array(
				'category_ids'       => array( $clothing_category_id ),
				'tag_ids'            => array( $shoes_tag_id ),
				'attributes'         => array( $kind_attribute_special_only ),
				'default_attributes' => array( 'pa_kind' => 'special' ),
			)
		);
		$this->factory->product->createManySimple( 3, array( 'attributes' => array( $kind_attribute_both ) ) );

		$query = '
			query ($where: CollectionStatsWhereArgs, $taxonomies: [CollectionStatsQueryInput]) {
				collectionStats(
					calculateRatingCounts: true
					taxonomies: $taxonomies
					where: $where
				) {
					
					attributeCounts {
						slug
						terms {
							node { slug }
							termId
							count
						}
					}
				}
			}
		';

		/**
		 * Query for products with the "clothing" category and confirm correct count.
		 */
		$variables = array(
			'calculateRatingCounts' => true,
			'where'                 => array(
				'categoryIdIn' => array( $clothing_category_id ),
			),
			'taxonomies'            => array(
				array(
					'taxonomy' => 'PA_KIND',
					'relation' => 'AND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 20 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						)
					),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 10 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						)
					),
				),
				0
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		// Test again with the "categoryIn" where arg.
		$variables = array(
			'calculateRatingCounts' => true,
			'where'                 => array(
				'categoryIn' => array( 'clothing' ),
			),
			'taxonomies'            => array(
				array(
					'taxonomy' => 'PA_KIND',
					'relation' => 'AND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Query for products with the "shoes" tag and confirm correct count.
		 */
		$variables = array(
			'calculateRatingCounts' => true,
			'where'                 => array(
				'tagIdIn' => array( $shoes_tag_id ),
			),
			'taxonomies'            => array(
				array(
					'taxonomy' => 'PA_KIND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this
						->not()
						->expectedNode( 'terms', array( $this->expectedField( 'node.slug', 'normal' ) ) ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 5 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						)
					),
				),
				0
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		// Test again with the "tagIn" where arg.
		$variables = array(
			'calculateRatingCounts' => true,
			'where'                 => array(
				'tagIn' => array( 'shoes' ),
			),
			'taxonomies'            => array(
				array(
					'taxonomy' => 'PA_KIND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Query for products with the "shoes" tag and confirm correct count.
		 */
		$variables = array(
			'calculateRatingCounts' => true,
			'where'                 => array(
				'categoryIn' => array( 'clothing' ),
				'attributes' => array(
					'queries' => array(
						array(
							'taxonomy' => 'PA_KIND',
							'ids'      => $normal_term_id,
							'operator' => 'IN',
						),
					),
				),
			),
			'taxonomies'            => array(
				array(
					'taxonomy' => 'PA_KIND',
					'relation' => 'AND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 20 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						)
					),
					$this
						->not()
						->expectedNode( 'terms', array( $this->expectedField( 'node.slug', 'special' ) ) ),
				),
				0
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCollectionStatsQueryWithOrTaxQueries() {
		// Create product attributes.
		$kind_attribute    = $this->factory->product->createAttribute( 'kind', array( 'normal', 'special' ), 'Product type' );
		$pattern_attribute = $this->factory->product->createAttribute( 'pattern', array( 'polka-dot', 'striped' ), 'Product pattern' );

		$normal_term_id              = get_term_by( 'slug', 'normal', 'pa_kind' )->term_id;
		$special_term_id             = get_term_by( 'slug', 'special', 'pa_kind' )->term_id;
		$kind_attribute_normal_only  = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $normal_term_id )
		);
		$kind_attribute_special_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $special_term_id )
		);

		$polka_dot_term_id                = get_term_by( 'slug', 'polka-dot', 'pa_pattern' )->term_id;
		$striped_term_id                  = get_term_by( 'slug', 'striped', 'pa_pattern' )->term_id;
		$pattern_attribute_polka_dot_only = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			array( $polka_dot_term_id )
		);
		$pattern_attribute_striped_only   = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			array( $striped_term_id )
		);

		// Create products.
		$this->factory->product->createManySimple(
			3,
			array(
				'attributes'         => array( $kind_attribute_normal_only ),
				'default_attributes' => array( 'pa_kind' => 'normal' ),
			)
		);
		$this->factory->product->createManySimple(
			7,
			array(
				'attributes'         => array( $kind_attribute_special_only ),
				'default_attributes' => array( 'pa_kind' => 'special' ),
			)
		);
		$this->factory->product->createManySimple(
			4,
			array(
				'attributes'         => array( $pattern_attribute_polka_dot_only ),
				'default_attributes' => array( 'pa_pattern' => 'polka-dot' ),
			)
		);
		$this->factory->product->createManySimple(
			6,
			array(
				'attributes'         => array( $pattern_attribute_striped_only ),
				'default_attributes' => array( 'pa_pattern' => 'striped' ),
			)
		);
		$this->factory->product->createManySimple(
			2,
			array(
				'attributes'         => array(
					$kind_attribute_normal_only,
					$pattern_attribute_polka_dot_only,
				),
				'default_attributes' => array(
					'pa_kind'    => 'normal',
					'pa_pattern' => 'polka-dot',
				),
			)
		);
		$this->factory->product->createManySimple(
			8,
			array(
				'attributes'         => array(
					$kind_attribute_special_only,
					$pattern_attribute_striped_only,
				),
				'default_attributes' => array(
					'pa_kind'    => 'special',
					'pa_pattern' => 'striped',
				),
			)
		);

		$query = '
			query ($where: CollectionStatsWhereArgs, $taxonomies: [CollectionStatsQueryInput]) {
				collectionStats(
					calculateRatingCounts: true
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
				}
			}
		';

		$variables = array(
			'taxonomies' => array(
				array(
					'taxonomy' => 'PA_KIND',
					'relation' => 'OR',
				),
				array(
					'taxonomy' => 'PA_PATTERN',
					'relation' => 'OR',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 5 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						0
					),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 15 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						1
					),
				),
			),
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_PATTERN' ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'polka-dot' ),
							$this->expectedField( 'count', 6 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						0
					),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'striped' ),
							$this->expectedField( 'count', 14 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						1
					),
				),
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCollectionStatsQueryWithAndTaxQueries() {
		// Create product attributes.
		$kind_attribute    = $this->factory->product->createAttribute( 'kind', array( 'normal', 'special' ), 'Product type' );
		$pattern_attribute = $this->factory->product->createAttribute( 'pattern', array( 'polka-dot', 'striped' ), 'Product pattern' );

		$normal_term_id              = get_term_by( 'slug', 'normal', 'pa_kind' )->term_id;
		$special_term_id             = get_term_by( 'slug', 'special', 'pa_kind' )->term_id;
		$kind_attribute_normal_only  = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $normal_term_id )
		);
		$kind_attribute_special_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $special_term_id )
		);

		$polka_dot_term_id                = get_term_by( 'slug', 'polka-dot', 'pa_pattern' )->term_id;
		$striped_term_id                  = get_term_by( 'slug', 'striped', 'pa_pattern' )->term_id;
		$pattern_attribute_polka_dot_only = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			array( $polka_dot_term_id )
		);
		$pattern_attribute_striped_only   = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			array( $striped_term_id )
		);

		// Create taxonomies.
		$clothing_category_id = $this->factory->product->createProductCategory( 'clothing' );

		// Create products.
		$this->factory->product->createManySimple(
			3,
			array(
				'category_ids'       => array( $clothing_category_id ),
				'attributes'         => array( $kind_attribute_normal_only ),
				'default_attributes' => array( 'pa_kind' => 'normal' ),
			)
		);
		$this->factory->product->createManySimple(
			7,
			array(
				'attributes'         => array( $kind_attribute_special_only ),
				'default_attributes' => array( 'pa_kind' => 'special' ),
			)
		);
		$this->factory->product->createManySimple(
			4,
			array(
				'category_ids'       => array( $clothing_category_id ),
				'attributes'         => array( $pattern_attribute_polka_dot_only ),
				'default_attributes' => array( 'pa_pattern' => 'polka-dot' ),
			)
		);
		$this->factory->product->createManySimple(
			6,
			array(
				'attributes'         => array( $pattern_attribute_striped_only ),
				'default_attributes' => array( 'pa_pattern' => 'striped' ),
			)
		);
		$this->factory->product->createManySimple(
			2,
			array(
				'attributes'         => array(
					$kind_attribute_normal_only,
					$pattern_attribute_polka_dot_only,
				),
				'default_attributes' => array(
					'pa_kind'    => 'normal',
					'pa_pattern' => 'polka-dot',
				),
			)
		);
		$this->factory->product->createManySimple(
			8,
			array(
				'category_ids'       => array( $clothing_category_id ),
				'attributes'         => array(
					$kind_attribute_special_only,
					$pattern_attribute_striped_only,
				),
				'default_attributes' => array(
					'pa_kind'    => 'special',
					'pa_pattern' => 'striped',
				),
			)
		);

		$query = '
			query ($where: CollectionStatsWhereArgs, $taxonomies: [CollectionStatsQueryInput]) {
				collectionStats(
					calculateRatingCounts: true
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
				}
			}
		';

		$variables = array(
			'where'      => array(
				'categoryIn' => array( 'clothing' ),
			),
			'taxonomies' => array(
				array(
					'taxonomy' => 'PA_PATTERN',
					'relation' => 'OR',
				),
				array(
					'taxonomy' => 'PA_KIND',
					'relation' => 'AND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 3 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						0
					),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 8 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						1
					),
				),
			),
			$this->expectedNode(
				'collectionStats.attributeCounts',
				array(
					$this->expectedField( 'slug', 'PA_PATTERN' ),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'polka-dot' ),
							$this->expectedField( 'count', 4 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						0
					),
					$this->expectedNode(
						'terms',
						array(
							$this->expectedField( 'node.slug', 'striped' ),
							$this->expectedField( 'count', 8 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						),
						1
					),
				),
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
