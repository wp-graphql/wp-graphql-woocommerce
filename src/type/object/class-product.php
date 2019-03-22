<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Object;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use GraphQLRelay\Relay;

/**
 * Class Product
 *
 * Registers proper Product type and query
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\Object
 * @since   0.0.1
 */
class Product
{
    public static function register()
    {
        /**
        * Register Product Type
        */
        register_graphql_object_type(
            'Product',
            array(
                'description' => __('A product object', 'wp-graphql-woocommerce'),
                'fields'      => array(
                    'id'                => array(
                        'type'    => array( 'non_null' => 'ID' ),
                        'resolve' => function ($product) {
                            return ! empty($product) ? Relay::toGlobalId('product', $product->get_id()) : null;
                        },
                    ),
                    'productId'         => array(
                        'type'        => array(	'non_null' => 'Int' ),
                        'description' => __('Product ID', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_id();
                        },
                    ),
                    'slug'              => array(
                        'type'        => 'String',
                        'description' => __('Product slug', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_slug();
                        },
                    ),
                    'name'              => array(
                        'type'        => 'String',
                        'description' => __('Product name', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_name();
                        },
                    ),
                    'date'              => array(
                        'type'        => 'String',
                        'description' => __('Date product was created', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_date_created();
                        },
                    ),
                    'modified'          => array(
                        'type'        => 'String',
                        'description' => __('Date product was last modified', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_date_modified();
                        },
                    ),
                    'status'            => array(
                        'type'        => 'String',
                        'description' => __('Product status', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_status();
                        },
                    ),
                    'featured'          => array(
                        'type'        => 'Boolean',
                        'description' => __('If the product is featured', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_featured();
                        },
                    ),
                    'catalogVisibility' => array(
                        'type'        => 'CatalogVisibilityEnum',
                        'description' => __('Catalog visibility', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_catalog_visibility();
                        },
                    ),
                    'description'       => array(
                        'type'        => 'String',
                        'description' => __('Product description', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_description();
                        },
                    ),
                    'shortDescription'  => array(
                        'type'        => 'String',
                        'description' => __('Product short description', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_short_description();
                        },
                    ),
                    'sku'               => array(
                        'type'        => 'String',
                        'description' => __('Product SKU', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_sku();
                        },
                    ),
                    'price'             => array(
                        'type'        => 'String',
                        'description' => __('Product\'s active price', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_price();
                        },
                    ),
                    'regularPrice'      => array(
                        'type'        => 'String',
                        'description' => __('Product\'s regular price', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_regular_price();
                        },
                    ),
                    'salePrice'         => array(
                        'type'        => 'String',
                        'description' => __('Product\'s sale price', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_sale_price();
                        },
                    ),
                    'dateOnSaleFrom'    => array(
                        'type'        => 'String',
                        'description' => __('Date on sale from', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_date_on_sale_from();
                        },
                    ),
                    'dateOnSaleTo'      => array(
                        'type'        => 'String',
                        'description' => __('Date on sale to', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_date_on_sale_to();
                        },
                    ),
                    'totalSales'        => array(
                        'type'        => 'Int',
                        'description' => __('Number total of sales', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_total_sales();
                        },
                    ),
                    'taxStatus'         => array(
                        'type'        => 'TaxStatusEnum',
                        'description' => __('Tax status', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_tax_status();
                        },
                    ),
                    'taxClass'          => array(
                        'type'        => 'String',
                        'description' => __('Tax class', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_tax_class();
                        },
                    ),
                    'manageStock'       => array(
                        'type'        => 'Boolean',
                        'description' => __('If product manage stock', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_manage_stock();
                        },
                    ),
                    'stockQuantity'     => array(
                        'type'        => 'Int',
                        'description' => __('Number of items available for sale', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_stock_quantity();
                        },
                    ),
                    'stockStatus'       => array(
                        'type'        => 'StockStatusEnum',
                        'description' => __('Product stock status', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_stock_status();
                        },
                    ),
                    'backorders'        => array(
                        'type'        => 'BackorderEnum',
                        'description' => __('Product backorders status', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_backorders();
                        },
                    ),
                    'soldIndividually'  => array(
                        'type'        => 'Boolean',
                        'description' => __('If should be sold individually', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_sold_individually();
                        },
                    ),
                    'weight'            => array(
                        'type'        => 'String',
                        'description' => __('Product\'s weight', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_weight();
                        },
                    ),
                    'length'            => array(
                        'type'        => 'String',
                        'description' => __('Product\'s length', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_length();
                        },
                    ),
                    'width'             => array(
                        'type'        => 'String',
                        'description' => __('Product\'s width', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_width();
                        },
                    ),
                    'height'            => array(
                        'type'        => 'String',
                        'description' => __('Product\'s height', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_height();
                        },
                    ),
                    'reviewsAllowed'    => array(
                        'type'        => 'Boolean',
                        'description' => __('If reviews are allowed', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_reviews_allowed();
                        },
                    ),
                    'purchaseNote'      => array(
                        'type'        => 'String',
                        'description' => __('Purchase note', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_purchase_note();
                        },
                    ),
                    'menuOrder'         => array(
                        'type'        => 'Int',
                        'description' => __('Menu order', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_menu_order();
                        },
                    ),
                    'virtual'           => array(
                        'type'        => 'Boolean',
                        'description' => __('Is product virtual?', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_virtual();
                        },
                    ),
                    'downloadExpiry'    => array(
                        'type'        => 'Int',
                        'description' => __('Download expiry', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_download_expiry();
                        },
                    ),
                    'downloadable'      => array(
                        'type'        => 'Boolean',
                        'description' => __('Is downloadable?', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_downloadable();
                        },
                    ),
                    'downloadLimit'     => array(
                        'type'        => 'Int',
                        'description' => __('Download limit', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_download_limit();
                        },
                    ),
                    'ratingCount'       => array(
                        'type'        => array( 'list_of' => 'String' ),
                        'description' => __('Product rating count', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_rating_counts();
                        },
                    ),
                    'averageRating'     => array(
                        'type'        => 'Float',
                        'description' => __('Product average count', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_average_rating();
                        },
                    ),
                    'reviewCount'       => array(
                        'type'        => 'Int',
                        'description' => __('Product review count', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return $product->get_review_count();
                        },
                    ),
                    'parent'            => array(
                        'type'        => 'Product',
                        'description' => __('Parent product', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product) {
                            return \WC_Product($product->get_parent_id());
                        },
                    ),
                    'image'             => array(
                        'type'        => 'MediaItem',
                        'description' => __('Main image', 'wp-graphql-woocommerce'),
                        'resolve'     => function ($product, $args, AppContext $context, ResolveInfo $info) {
                            $thumbnail_id = $product->get_image_id();

                            return ! empty( $thumbnail_id ) ? DataSource::resolve_post_object( $thumbnail_id, $context ) : null;
                        },
                    ),
                ),
            )
        );

        /**
         * Register product queries
         */
        register_graphql_field(
            'RootQuery',
            'product',
            array(
                'type'        => 'Product',
                'description' => __('A Product object', 'wp-graphql-woocommerce'),
                'args'        => array(
                    'id' => array(
                        'type' => array( 'non_null' => 'ID' ),
                    ),
                ),
                'resolve'     => function ($source, array $args, $context, $info) {
                    $id_components = Relay::fromGlobalId($args['id']);
                    return Factory::resolve_product($id_components['id']);
                },
            )
        );

        register_graphql_field(
            'RootQuery',
            'productBy',
            array(
                'type'        => 'Product',
                'description' => __('A Product object', 'wp-graphql-woocommerce'),
                'args'        => array(
                    'productId' => array( 'type' => 'Int' ),
                    'slug'      => array( 'type' => 'String' ),
                ),
                'resolve'     => function ($source, array $args, $context, $info) {
                    if (! empty($args['productId'])) {
                        return Factory::resolve_product($args['productId']);
                    }
                    if (! empty($args['slug'])) {
                        $posts = get_posts(
                            array(
                                'name'           => $args['slug'],
                                'posts_per_page' => 1,
                                'post_type'      => 'product',
                                'post_status'    => 'publish',
                            )
                        );
                        return isset($posts[0]) ? Factory::resolve_product($posts[0]->ID) : null;
                    }
                    return null;
                },
            )
        );
    }
}
