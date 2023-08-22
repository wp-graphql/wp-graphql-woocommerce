<?php

use WPGraphQL\Type\WPEnumType;

class ProductVariationQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function expectedProductVariationData( $id ) {
		$data = new WC_Product_Variation( $id );

		return [
			$this->expectedField( 'productVariation.id', $this->toRelayId( 'product_variation', $id ) ),
			$this->expectedField( 'productVariation.databaseId', $data->get_id() ),
			$this->expectedField( 'productVariation.name', $data->get_name() ),
			$this->expectedField( 'productVariation.date', $data->get_date_created()->__toString() ),
			$this->expectedField(
				'productVariation.modified',
				! empty( $data->get_date_created() )
					? $data->get_date_created()->__toString()
					: self::IS_NULL
			),
			$this->expectedField( 'productVariation.description', ! empty( $data->get_description() ) ? $data->get_description() : self::IS_NULL ),
			$this->expectedField( 'productVariation.sku', $data->get_sku() ),
			$this->expectedField( 'productVariation.price', ! empty( $data->get_price() ) ? \wc_graphql_price( $data->get_price() ) : self::IS_NULL ),
			$this->expectedField( 'productVariation.regularPrice', ! empty( $data->get_regular_price() ) ? \wc_graphql_price( $data->get_regular_price() ) : self::IS_NULL ),
			$this->expectedField( 'productVariation.salePrice', ! empty( $data->get_sale_price() ) ? \wc_graphql_price( $data->get_sale_price() ) : self::IS_NULL ),
			$this->expectedField(
				'productVariation.dateOnSaleFrom',
				! empty( $data->get_date_on_sale_from() )
					? $data->get_date_on_sale_from()
					: self::IS_NULL
			),
			$this->expectedField(
				'productVariation.dateOnSaleTo',
				! empty( $data->get_date_on_sale_to() )
					? $data->get_date_on_sale_to()
					: self::IS_NULL
			),
			$this->expectedField( 'productVariation.onSale', $data->is_on_sale() ),
			$this->expectedField( 'productVariation.status', $data->get_status() ),
			$this->expectedField( 'productVariation.purchasable', ! empty( $data->is_purchasable() ) ? $data->is_purchasable() : self::IS_NULL ),
			$this->expectedField( 'productVariation.virtual', $data->is_virtual() ),
			$this->expectedField( 'productVariation.downloadable', $data->is_downloadable() ),
			$this->expectedField( 'productVariation.downloadLimit', ! empty( $data->get_download_limit() ) ? $data->get_download_limit() : self::IS_NULL ),
			$this->expectedField( 'productVariation.downloadExpiry', ! empty( $data->get_download_expiry() ) ? $data->get_download_expiry() : self::IS_NULL ),
			$this->expectedField( 'productVariation.taxStatus', strtoupper( $data->get_tax_status() ) ),
			$this->expectedField(
				'productVariation.taxClass',
				! empty( $data->get_tax_class() )
					? WPEnumType::get_safe_name( $data->get_tax_class() )
					: 'STANDARD'
			),
			$this->expectedField(
				'productVariation.manageStock',
				! empty( $data->get_manage_stock() )
					? WPEnumType::get_safe_name( $data->get_manage_stock() )
					: self::IS_NULL
			),
			$this->expectedField( 'productVariation.stockQuantity', ! empty( $data->get_stock_quantity() ) ? $data->get_stock_quantity() : self::IS_NULL ),
			$this->expectedField( 'productVariation.stockStatus', ProductHelper::get_stock_status_enum( $data->get_stock_status() ) ),
			$this->expectedField(
				'productVariation.backorders',
				! empty( $data->get_backorders() )
					? WPEnumType::get_safe_name( $data->get_backorders() )
					: self::IS_NULL
			),
			$this->expectedField( 'productVariation.backordersAllowed', $data->backorders_allowed() ),
			$this->expectedField( 'productVariation.weight', ! empty( $data->get_weight() ) ? $data->get_weight() : self::IS_NULL ),
			$this->expectedField( 'productVariation.length', ! empty( $data->get_length() ) ? $data->get_length() : self::IS_NULL ),
			$this->expectedField( 'productVariation.width', ! empty( $data->get_width() ) ? $data->get_width() : self::IS_NULL ),
			$this->expectedField( 'productVariation.height', ! empty( $data->get_height() ) ? $data->get_height() : self::IS_NULL ),
			$this->expectedField( 'productVariation.menuOrder', $data->get_menu_order() ),
			$this->expectedField( 'productVariation.purchaseNote', ! empty( $data->get_purchase_note() ) ? $data->get_purchase_note() : self::IS_NULL ),
			$this->expectedField( 'productVariation.shippingClass', ! empty( $data->get_shipping_class() ) ? $data->get_shipping_class() : self::IS_NULL ),
			$this->expectedField(
				'productVariation.catalogVisibility',
				! empty( $data->get_catalog_visibility() )
					? WPEnumType::get_safe_name( $data->get_catalog_visibility() )
					: self::IS_NULL
			),
			$this->expectedField( 'productVariation.hasAttributes', ! empty( $data->has_attributes() ) ? $data->has_attributes() : self::IS_NULL ),
			$this->expectedField( 'productVariation.type', WPEnumType::get_safe_name( $data->get_type() ) ),
			$this->expectedField( 'productVariation.parent.node.id', $this->toRelayId( 'product', $data->get_parent_id() ) ),
		];
	}

	// tests
	public function testVariationQuery() {
		// Create product variations.
		$products     = $this->factory->product_variation->createSome(
			$this->factory->product->createVariable()
		);
		$variation_id = $products['variations'][0];
		$id           = $this->toRelayId( 'product_variation', $variation_id );

		// Create query.
		$query        = '
            query ($id: ID, $idType: ProductVariationIdTypeEnum) {
                productVariation(id: $id, idType: $idType) {
                    id
                    databaseId
                    name
                    date
                    modified
                    description
                    sku
                    price
                    regularPrice
                    salePrice
                    dateOnSaleFrom
                    dateOnSaleTo
                    onSale
                    status
                    purchasable
                    virtual
                    downloadable
                    downloadLimit
                    downloadExpiry
                    taxStatus
                    taxClass
                    manageStock
                    stockQuantity
                    stockStatus
                    backorders
                    backordersAllowed
                    weight
                    length
                    width
                    height
                    menuOrder
                    purchaseNote
                    shippingClass
                    catalogVisibility
                    hasAttributes
                    type
                    parent {
						node { id }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Tests "ID" ID type.
		 */
		$variables = [
			'id'     => $id,
			'idType' => 'ID',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = $this->expectedProductVariationData( $variation_id );

		$this->assertQuerySuccessful( $response, $expected );

		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Two
		 *
		 * Tests "DATABASE_ID" ID type.
		 */
		$variables = [
			'id'     => $variation_id,
			'idType' => 'DATABASE_ID',

		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = $this->expectedProductVariationData( $variation_id );

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testVariationsQueryAndWhereArgs() {
		// Create product variations.
		$products     = $this->factory->product_variation->createSome(
			$this->factory->product->createVariable()
		);
		$variation_id = $products['variations'][0];
		$id           = $this->toRelayId( 'product', $products['product'] );
		$product      = wc_get_product( $products['product'] );
		$variations   = $products['variations'];
		$prices       = $product->get_variation_prices( true );

		$query = '
            query (
                $id: ID!,
                $minPrice: Float,
                $parent: Int,
                $parentIn: [Int],
                $parentNotIn: [Int]
            ) {
                product( id: $id ) {
                    ... on VariableProduct {
                        price
                        regularPrice
                        salePrice
                        variations( where: {
                            minPrice: $minPrice,
                            parent: $parent,
                            parentIn: $parentIn,
                            parentNotIn: $parentNotIn
                        } ) {
                            nodes {
                                id
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Test query with no arguments
		 */
		$this->loginAsShopManager();
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'product_variation', $variations[0] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'product_variation', $variations[1] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'product_variation', $variations[2] ) ),
			$this->expectedField(
				'product.price',
				\wc_graphql_price( current( $prices['price'] ) )
					. ' - '
					. \wc_graphql_price( end( $prices['price'] ) )
			),
			$this->expectedField(
				'product.regularPrice',
				\wc_graphql_price( current( $prices['regular_price'] ) )
					. ' - '
					. \wc_graphql_price( end( $prices['regular_price'] ) )
			),
			$this->expectedField( 'product.salePrice', self::IS_NULL )
		];

		$this->assertQuerySuccessful( $response, $expected );

		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Two
		 *
		 * Test "minPrice" where argument
		 */
		$variables = [
			'id'       => $id,
			'minPrice' => 15,
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->not()->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'product_variation', $variations[0] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'product_variation', $variations[1] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'product_variation', $variations[2] ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductVariationToMediaItemConnections() {
		// Create product variations.
		$products     = $this->factory->product_variation->createSome(
			$this->factory->product->createVariable()
		);
		$variation_id = $products['variations'][1];
		$id           = $this->toRelayId( 'product_variation', $variation_id );
		$product      = wc_get_product( $variation_id );

		// Create query.
		$query = '
			query ($id: ID!) {
				productVariation(id: $id) {
					id
					image {
						id
					}
				}
			}
		';

		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'productVariation.id', $id ),
			$this->expectedField( 'productVariation.image.id', $this->toRelayId( 'post', $product->get_image_id() ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductVariationDownloads() {
		// Create product variations.
		$products     = $products     = $this->factory->product_variation->createSome(
			$this->factory->product->createVariable()
		);
		$variation_id = $products['variations'][0];
		$id           = $this->toRelayId( 'product_variation', $variation_id );
		$product      = wc_get_product( $variation_id );
		$downloads    = (array) array_values( $product->get_downloads() );

		// Create query.
		$query = '
			query ($id: ID!) {
				productVariation(id: $id) {
					id
					downloads {
						name
						downloadId
						filePathType
						fileType
						fileExt
						allowedFileType
						fileExists
						file
					}
				}
			}
		';

		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'productVariation.id', $id ),
			$this->expectedNode(
				'productVariation.downloads',
				[
					$this->expectedField( 'name', $downloads[0]->get_name() ),
					$this->expectedField( 'downloadId', $downloads[0]->get_id() ),
					$this->expectedField( 'filePathType', $downloads[0]->get_type_of_file_path() ),
					$this->expectedField( 'fileType', $downloads[0]->get_file_type() ),
					$this->expectedField( 'fileExt', $downloads[0]->get_file_extension() ),
					$this->expectedField( 'allowedFileType', $downloads[0]->is_allowed_filetype() ),
					$this->expectedField( 'fileExists', $downloads[0]->file_exists() ),
					$this->expectedField( 'file', $downloads[0]->get_file() ),
				]
			)
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductsQueriesWithVariations() {
		// Create noise products.
		$product_id   = $this->factory->product->createVariable(
			[
				'attribute_data' => [ $this->factory->product->createAttribute( 'print', [ 'polka-dot', 'stripe', 'flames' ] ) ],
			],
		);
		$variation_id = $this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [
					'pattern' => 'polka-dot',
				],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);


		$other_variation_id = $this->factory->product_variation->create(
			[
				'parent_id'     => $product_id,
				'attributes'    => [
					'pattern' => 'stripe',
				],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);

		$query = '
			query ( $type: ProductTypesEnum, $typeIn: [ProductTypesEnum], $includeVariations: Boolean ) {
				products( where: { type: $type, typeIn: $typeIn includeVariations: $includeVariations } ) {
					nodes {
						id
					}
				}
			}
		';

		/**
		 * Assert default results without "type", or "typeIn" excludes product variations.
		 */
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'products.nodes.0.id', $this->toRelayId( 'product', $product_id ) ),
			$this->not()->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product_variation', $variation_id ) ),
			$this->not()->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product_variation', $other_variation_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assert result with "type" set to "VARIATION" only return variations.
		 */
		$variables = [ 'type' => 'VARIATION' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->not()->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product_variation', $variation_id ) ),
			$this->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product_variation', $other_variation_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assert result with "typeIn" set to "VARIATION" & "VARIATION" products and variations are returned.
		 */
		$variables = [ 'includeVariations' => true ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product_variation', $variation_id ) ),
			$this->expectedField( 'products.nodes.#.id', $this->toRelayId( 'product_variation', $other_variation_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
