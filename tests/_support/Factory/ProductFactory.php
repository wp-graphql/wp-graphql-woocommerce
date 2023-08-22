<?php
/**
 * Factory class for the WooCommerce's Product data objects.
 *
 * @since v0.8.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Product factory class for testing.
 */
class ProductFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'product_class' => '\WC_Product_Simple',
		];
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) {
			codecept_debug( $args );
		}
		$product_class = $args['product_class'];
		unset( $args['product_class'] );

		$product = new $product_class();

		foreach ( $args as $key => $value ) {
			if ( is_callable( [ $product, "set_{$key}" ] ) ) {
				$product->{"set_{$key}"}( $value );
			}
		}

		if ( ! empty( $args['attribute_data'] ) ) {
			$this->setVariationAttributes( $product, $args['attribute_data'] );
		}

		return $product->save();
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Product && 0 !== absint( $object ) ) {
			$object = $this->get_object_by_id( $object );
		}

		foreach ( $fields as $field => $field_value ) {
			if ( ! is_callable( [ $object, "set_{$field}" ] ) ) {
				throw new \Exception(
					sprintf( '"%1$s" is not a valid %2$s product field.', $field, $object->get_type() )
				);
			}

			$object->{"set_{$field}"}( $field_value );
		}

		$object->save();
	}

	public function get_object_by_id( $product_id ) {
		return \wc_get_product( absint( $product_id ) );
	}

	public function createSimple( $args = [] ) {
		$name          = Dummy::instance()->product();
		$price         = Dummy::instance()->price( 15, 200 );
		$regular_price = $price;

		$generation_definitions = [
			'name'              => $name,
			'slug'              => $this->slugify( $name ),
			'regular_price'     => $regular_price,
			'price'             => $price,
			'sku'               => uniqid(),
			'manage_stock'      => false,
			'tax_status'        => 'taxable',
			'downloadable'      => false,
			'virtual'           => false,
			'stock_status'      => 'instock',
			'weight'            => '1.1',
			'description'       => '[shortcode_test]',
			'short_description' => Dummy::instance()->sentence(),
			'product_class'     => '\WC_Product_Simple',
		];

		return $this->create( $args, $generation_definitions );
	}

	public function createExternal( $args = [] ) {
		$name  = Dummy::instance()->product();
		$price = Dummy::instance()->price( 15, 200 );

		$generation_definitions = [
			'name'          => $name,
			'slug'          => $this->slugify( $name ),
			'regular_price' => $price,
			'sku'           => uniqid(),
			'product_url'   => 'http://woocommerce.com',
			'button_text'   => 'Buy external product',
			'product_class' => '\WC_Product_External',
		];

		return $this->create( $args, $generation_definitions );
	}

	public function createGrouped( $args = [] ) {
		$name = Dummy::instance()->product() . 'Group';

		$generation_definitions = [
			'name'          => $name,
			'slug'          => $this->slugify( $name ),
			'sku'           => uniqid(),
			'product_class' => '\WC_Product_Grouped',
		];

		$args = array_merge(
			[ 'children' => [ $this->createSimple() ] ],
			$args
		);

		return $this->create( $args, $generation_definitions );
	}

	public function createVariable( $args = [] ) {
		$name = Dummy::instance()->product();

		$generation_definitions = [
			'name'          => $name,
			'slug'          => $this->slugify( $name ),
			'sku'           => uniqid(),
			'product_class' => '\WC_Product_Variable',
		];

		$args = array_merge(
			[
				'attribute_data' => [
					$this->createAttribute( 'size', [ 'small', 'medium', 'large' ] ), // Create Size attribute.
					$this->createAttribute( 'color', [ 'red', 'blue', 'green' ] ), // Create Color attribute.
					[
						'attribute_id'       => 0,
						'attribute_taxonomy' => 'logo',
						'term_ids'           => [ 'Yes', 'No' ],
					], // Create Logo attribute.
				],
			],
			$args
		);

		return $this->create( $args, $generation_definitions );
	}

	public function createAttribute( $raw_name = 'size', $terms = [ 'small' ] ) {
		global $wpdb, $wc_product_attributes;

		// Make sure caches are clean.
		delete_transient( 'wc_attribute_taxonomies' );
		\WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );

		// These are exported as labels, so convert the label to a name if possible first.
		$attribute_labels = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_name' );
		$attribute_name   = array_search( $raw_name, $attribute_labels, true );

		if ( ! $attribute_name ) {
			$attribute_name = wc_sanitize_taxonomy_name( $raw_name );
		}

		$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );

		if ( ! $attribute_id ) {
			$taxonomy_name = wc_attribute_taxonomy_name( $attribute_name );

			$attribute_id = wc_create_attribute(
				[
					'name'         => $raw_name,
					'slug'         => $attribute_name,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => 0,
				]
			);

			// Register as taxonomy.
			register_taxonomy(
				$taxonomy_name,
				apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy_name, [ 'product' ] ),
				apply_filters(
					'woocommerce_taxonomy_args_' . $taxonomy_name,
					[
						'labels'       => [
							'name' => $raw_name,
						],
						'hierarchical' => false,
						'show_ui'      => false,
						'query_var'    => true,
						'rewrite'      => false,
					],
				)
			);

			// Set product attributes global.
			$wc_product_attributes = [];

			foreach ( wc_get_attribute_taxonomies() as $taxonomy ) {
				$wc_product_attributes[ wc_attribute_taxonomy_name( $taxonomy->attribute_name ) ] = $taxonomy;
			}
		}

		$attribute = wc_get_attribute( $attribute_id );
		$return    = [
			'attribute_name'     => $attribute->name,
			'attribute_taxonomy' => $attribute->slug,
			'attribute_id'       => $attribute_id,
			'term_ids'           => [],
		];

		foreach ( $terms as $term ) {
			$result = term_exists( $term, $attribute->slug );

			if ( ! $result ) {
				$result               = wp_insert_term( $term, $attribute->slug );
				$return['term_ids'][] = absint( $result['term_id'] );
			} else {
				$return['term_ids'][] = absint( $result['term_id'] );
			}
		}

		return $return;
	}

	private function setVariationAttributes( \WC_Product_Variable $product, array $attribute_data = [] ) {
		$attributes = [];
		foreach ( $attribute_data as $index => $data ) {
			$attribute = new \WC_Product_Attribute();
			$attribute->set_id( $data['attribute_id'] );
			$attribute->set_name( $data['attribute_taxonomy'] );
			$attribute->set_options( $data['term_ids'] );
			$attribute->set_position( $index );
			$attribute->set_visible( true );
			$attribute->set_variation( true );
			$attributes[] = $attribute;
		}

		$product->set_attributes( $attributes );
	}

	public function createRelated( $args = [] ) {
		$cross_sell_ids     = [
			$this->createSimple(),
			$this->createSimple(),
		];
		$upsell_ids         = [
			$this->createSimple(),
			$this->createSimple(),
		];
		$tag_ids            = [ $this->createProductTag( 'related' ) ];
		$related_product_id = $this->createSimple( [ 'tag_ids' => $tag_ids ] );

		return [
			'product'    => $this->createSimple(
				[
					'tag_ids'        => $tag_ids,
					'cross_sell_ids' => $cross_sell_ids,
					'upsell_ids'     => $upsell_ids,
				]
			),
			'related'    => [ $related_product_id ],
			'cross_sell' => $cross_sell_ids,
			'upsell'     => $upsell_ids,
		];
	}

	public function createProductTag( $term ) {
		if ( term_exists( $term, 'product_tag' ) ) {
			$term = get_term( $term, 'product_tag', ARRAY_A );
		} else {
			$term = wp_insert_term( $term, 'product_tag' );
		}

		return ! empty( $term['term_id'] ) ? $term['term_id'] : null;
	}

	public function getStockStatusEnum( $status ) {
		$statuses = [
			'instock'     => 'IN_STOCK',
			'outofstock'  => 'OUT_OF_STOCK',
			'onbackorder' => 'ON_BACKORDER',
		];

		if ( in_array( $status, array_keys( $statuses ), true ) ) {
			return $statuses[ $status ];
		}

		return null;
	}

	public function createProductCategory( $term, $parent_id = 0 ) {
		if ( term_exists( $term, 'product_cat' ) ) {
			$term = get_term( $term, 'product_cat', ARRAY_A );
		} else {
			$args = [];
			if ( $parent_id ) {
				$args['parent'] = $parent_id;
			}
			$term = wp_insert_term( $term, 'product_cat', $args );
		}

		return ! empty( $term['term_id'] ) ? $term['term_id'] : null;
	}

	public function createDownload( $id = 0 ) {
		$download = new \WC_Product_Download();
		$download->set_id( wp_generate_uuid4() );
		$download->set_name( 'Test Name' );
		$download->set_file( 'http://example.com/file.jpg' );

		if ( $id ) {
			$product = \wc_get_product( $id );
			$product->set_downloads( [ $download ] );
			$product->save();
		}

		return $download;
	}

	public function createReview( $product_id, $args = [] ) {
		$firstName = Dummy::instance()->firstname();
		$data      = array_merge(
			[
				'comment_post_ID'      => $product_id,
				'comment_author'       => $firstName,
				'comment_author_email' => "{$firstName}@example.com",
				'comment_author_url'   => '',
				'comment_content'      => Dummy::instance()->text(),
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			],
			$args
		);

		$comment_id = wp_insert_comment( $data );

		$rating = ! empty( $args['rating'] ) ? $args['rating'] : Dummy::instance()->number( 0, 5 );
		update_comment_meta( $comment_id, 'rating', $rating );

		return $comment_id;
	}

	/**
	 * Simple slugify function
	 *
	 * Copied and cleaned up from
	 *
	 * @link https://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
	 *
	 * @param string $text
	 * @return string
	 */
	private function slugify( $text ) {
		$text = preg_replace( '~[^\pL\d]+~u', '-', $text );
		$text = iconv( 'utf-8', 'us-ascii//TRANSLIT', $text );
		$text = preg_replace( '~[^-\w]+~', '', $text );
		$text = trim( $text, '-' );
		$text = preg_replace( '~-+~', '-', $text );
		$text = strtolower( $text );

		if ( empty( $text ) ) {
			return 'n-a';
		}

		return $text;
	}
}
