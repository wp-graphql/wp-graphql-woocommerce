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

		$variables = [
			'where'      => [
				'attributes' => [
					'queries' => [
						[
							'taxonomy' => 'PA_COLOR',
							'terms'    => 'red',
							'operator' => 'IN',
						],
					],
				],
			],
			'taxonomies' => [
				[
					'taxonomy' => 'PA_COLOR',
					'relation' => 'AND',
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_COLOR' ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'red' ),
							$this->expectedField( 'count', 2 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						] 
					),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'blue' ),
							$this->expectedField( 'count', 2 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						] 
					),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'green' ),
							$this->expectedField( 'count', 2 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
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

	public function testCollectionStatsQueryWithWhereArgs() {
		// Create product attributes.
		$kind_attribute = $this->factory->product->createAttribute( 'kind', [ 'special', 'normal' ], 'Product type' );
		$normal_term_id = get_term_by( 'slug', 'normal', 'pa_kind' )->term_id;
		$special_term_id = get_term_by( 'slug', 'special', 'pa_kind' )->term_id;

		// Create attribute objects.
		$kind_attribute_normal_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			[ $normal_term_id ]
		);
		
		$kind_attribute_special_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			[ $special_term_id ]
		);

		$kind_attribute_both = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			[ $normal_term_id, $special_term_id ]
		);

		// Create taxonomies.
		$clothing_category_id = $this->factory->product->createProductCategory( 'clothing' );
		$shoes_tag_id         = $this->factory->product->createProductTag( 'shoes' );

		// Create products.
		$post_ids = $this->factory->product->createManySimple(
			20,
			[
				'attributes'         => [ $kind_attribute_normal_only ],
				'category_ids'       => [ $clothing_category_id ],
				'default_attributes' => [ 'pa_kind' => 'normal' ],
			]
		);
		$this->factory->product->createManySimple(
			5,
			[
				'category_ids'       => [ $clothing_category_id ],
				'attributes'         => [ $kind_attribute_special_only ],
				'default_attributes' => [ 'pa_kind' => 'special' ],
			]
		);
		$this->factory->product->createManySimple(
			5,
			[
				'category_ids'       => [ $clothing_category_id ],
				'tag_ids'            => [ $shoes_tag_id ],
				'attributes'         => [ $kind_attribute_special_only ],
				'default_attributes' => [ 'pa_kind' => 'special' ],
			]
		);
		$this->factory->product->createManySimple( 3, [ 'attributes' => [ $kind_attribute_both ] ] );


		$query = "
			query (\$where: CollectionStatsWhereArgs, \$taxonomies: [CollectionStatsQueryInput]) {
				collectionStats(
					calculateRatingCounts: true
					taxonomies: \$taxonomies
					where: \$where
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
		";

		/**
		 * Query for products with the "clothing" category and confirm correct count.
		 */
		$variables = [
			'calculateRatingCounts' => true,
			'where'                 => [
				'categoryIdIn' => [ $clothing_category_id ],
			],
			'taxonomies'            => [
				[
					'taxonomy' => 'PA_KIND',
					'relation' => "AND",
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 20 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						] 
					),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 10 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						] 
					),
				],
				0
			)
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Test again with the "categoryIn" where arg.
		$variables = [
			'calculateRatingCounts' => true,
			'where'                 => [
				'categoryIn' => [ 'clothing' ],
			],
			'taxonomies'            => [
				[
					'taxonomy' => 'PA_KIND',
					'relation' => "AND",
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Query for products with the "shoes" tag and confirm correct count.
		 */
		$variables = [
			'calculateRatingCounts' => true,
			'where'                 => [
				'tagIdIn' => [ $shoes_tag_id ],
			],
			'taxonomies'            => [
				[
					'taxonomy' => 'PA_KIND',
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this
						->not()
						->expectedNode( 'terms', [ $this->expectedField( 'node.slug', 'normal' ) ] ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 5 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						] 
					),
				],
				0
			)
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Test again with the "tagIn" where arg.
		$variables = [
			'calculateRatingCounts' => true,
			'where'                 => [
				'tagIn' => [ 'shoes' ],
			],
			'taxonomies'            => [
				[
					'taxonomy' => 'PA_KIND',
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Query for products with the "shoes" tag and confirm correct count.
		 */
		$variables = [
			'calculateRatingCounts' => true,
			'where'                 => [
				'categoryIn' => [ 'clothing' ],
				'attributes' => [
					'queries' => [
						[
							'taxonomy' => 'PA_KIND',
							'ids'      => $normal_term_id,
							'operator' => 'IN',
						],
					]
				],
			],
			'taxonomies'            => [
				[
					'taxonomy' => 'PA_KIND',
					'relation' => "AND",
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 20 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						] 
					),
					$this
						->not()
						->expectedNode( 'terms', [ $this->expectedField( 'node.slug', 'special' ) ] ),
				],
				0
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCollectionStatsQueryWithOrTaxQueries() {
		// Create product attributes.
		$kind_attribute    = $this->factory->product->createAttribute( 'kind', [ 'normal', 'special' ], 'Product type' );
		$pattern_attribute = $this->factory->product->createAttribute( 'pattern', [ 'polka-dot', 'striped' ], 'Product pattern' );

		$normal_term_id = get_term_by( 'slug', 'normal', 'pa_kind' )->term_id;
		$special_term_id = get_term_by( 'slug', 'special', 'pa_kind' )->term_id;
		$kind_attribute_normal_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			[ $normal_term_id ]
		);
		$kind_attribute_special_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			[ $special_term_id ]
		);

		$polka_dot_term_id = get_term_by( 'slug', 'polka-dot', 'pa_pattern' )->term_id;
		$striped_term_id = get_term_by( 'slug', 'striped', 'pa_pattern' )->term_id;
		$pattern_attribute_polka_dot_only = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			[ $polka_dot_term_id ]
		);
		$pattern_attribute_striped_only = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			[ $striped_term_id ]
		);

		// Create products.
		$this->factory->product->createManySimple(
			3,
			[
				'attributes'         => [ $kind_attribute_normal_only ],
				'default_attributes' => [ 'pa_kind' => 'normal' ],
			]
		);
		$this->factory->product->createManySimple(
			7,
			[
				'attributes'         => [ $kind_attribute_special_only ],
				'default_attributes' => [ 'pa_kind' => 'special' ],
			]
		);
		$this->factory->product->createManySimple(
			4,
			[
				'attributes'         => [ $pattern_attribute_polka_dot_only ],
				'default_attributes' => [ 'pa_pattern' => 'polka-dot' ],
			]
		);
		$this->factory->product->createManySimple(
			6,
			[
				'attributes'         => [ $pattern_attribute_striped_only ],
				'default_attributes' => [ 'pa_pattern' => 'striped' ],
			]
		);
		$this->factory->product->createManySimple(
			2,
			[
				'attributes'         => [
					$kind_attribute_normal_only,
					$pattern_attribute_polka_dot_only,
				],
				'default_attributes' => [ 'pa_kind' => 'normal', 'pa_pattern' => 'polka-dot' ],
			]
		);
		$this->factory->product->createManySimple(
			8,
			[
				'attributes'         => [
					$kind_attribute_special_only,
					$pattern_attribute_striped_only,
				],
				'default_attributes' => [ 'pa_kind' => 'special', 'pa_pattern' => 'striped' ],
			]
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

		$variables = [
			'taxonomies'            => [
				[
					'taxonomy' => 'PA_KIND',
					'relation' => 'OR',
				],
				[
					'taxonomy' => 'PA_PATTERN',
					'relation' => 'OR',
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 5 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						0
					),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 15 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						1
					),
				],
			),
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_PATTERN' ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'polka-dot' ),
							$this->expectedField( 'count', 6 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						0
					),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'striped' ),
							$this->expectedField( 'count', 14 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						1
					),
				],
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCollectionStatsQueryWithAndTaxQueries() {
		// Create product attributes.
		$kind_attribute    = $this->factory->product->createAttribute( 'kind', [ 'normal', 'special' ], 'Product type' );
		$pattern_attribute = $this->factory->product->createAttribute( 'pattern', [ 'polka-dot', 'striped' ], 'Product pattern' );

		$normal_term_id = get_term_by( 'slug', 'normal', 'pa_kind' )->term_id;
		$special_term_id = get_term_by( 'slug', 'special', 'pa_kind' )->term_id;
		$kind_attribute_normal_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			[ $normal_term_id ]
		);
		$kind_attribute_special_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			[ $special_term_id ]
		);

		$polka_dot_term_id = get_term_by( 'slug', 'polka-dot', 'pa_pattern' )->term_id;
		$striped_term_id = get_term_by( 'slug', 'striped', 'pa_pattern' )->term_id;
		$pattern_attribute_polka_dot_only = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			[ $polka_dot_term_id ]
		);
		$pattern_attribute_striped_only = $this->factory->product->createAttributeObject(
			$pattern_attribute['attribute_id'],
			$pattern_attribute['attribute_taxonomy'],
			[ $striped_term_id ]
		);

		// Create taxonomies.
		$clothing_category_id = $this->factory->product->createProductCategory( 'clothing' );

		// Create products.
		$this->factory->product->createManySimple(
			3,
			[
				'category_ids'       => [ $clothing_category_id ],
				'attributes'         => [ $kind_attribute_normal_only ],
				'default_attributes' => [ 'pa_kind' => 'normal' ],
			]
		);
		$this->factory->product->createManySimple(
			7,
			[
				'attributes'         => [ $kind_attribute_special_only ],
				'default_attributes' => [ 'pa_kind' => 'special' ],
			]
		);
		$this->factory->product->createManySimple(
			4,
			[
				'category_ids'       => [ $clothing_category_id ],
				'attributes'         => [ $pattern_attribute_polka_dot_only ],
				'default_attributes' => [ 'pa_pattern' => 'polka-dot' ],
			]
		);
		$this->factory->product->createManySimple(
			6,
			[
				'attributes'         => [ $pattern_attribute_striped_only ],
				'default_attributes' => [ 'pa_pattern' => 'striped' ],
			]
		);
		$this->factory->product->createManySimple(
			2,
			[
				'attributes'         => [
					$kind_attribute_normal_only,
					$pattern_attribute_polka_dot_only,
				],
				'default_attributes' => [ 'pa_kind' => 'normal', 'pa_pattern' => 'polka-dot' ],
			]
		);
		$this->factory->product->createManySimple(
			8,
			[
				'category_ids'       => [ $clothing_category_id ],
				'attributes'         => [
					$kind_attribute_special_only,
					$pattern_attribute_striped_only,
				],
				'default_attributes' => [ 'pa_kind' => 'special', 'pa_pattern' => 'striped' ],
			]
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

		$variables = [
			'where'      => [
				'categoryIn' => [ 'clothing' ],
			],
			'taxonomies' => [
				[
					'taxonomy' => 'PA_PATTERN',
					'relation' => 'OR',
				],
				[
					'taxonomy' => 'PA_KIND',
					'relation' => 'AND',
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_KIND' ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'normal' ),
							$this->expectedField( 'count', 3 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						0
					),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'special' ),
							$this->expectedField( 'count', 8 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						1
					),
				],
			),
			$this->expectedNode(
				'collectionStats.attributeCounts', 
				[
					$this->expectedField( 'slug', 'PA_PATTERN' ),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'polka-dot' ),
							$this->expectedField( 'count', 4 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						0
					),
					$this->expectedNode(
						'terms',
						[
							$this->expectedField( 'node.slug', 'striped' ),
							$this->expectedField( 'count', 8 ),
							$this->expectedField( 'termId', static::NOT_FALSY ),
						],
						1
					),
				],
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
