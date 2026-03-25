<?php

/**
 * Tests for internationalization (i18n) compatibility.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/637
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/409
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/403
 */
class I18nCompatibilityTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Test that non-latin tax class names do not break schema introspection
	 * when transliteration is disabled.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/637
	 */
	public function testNonLatinTaxClassDoesNotBreakSchemaWhenTransliterationDisabled() {
		// Ensure transliteration is disabled.
		$existing = get_option( 'woographql_settings', [] );
		update_option(
			'woographql_settings',
			array_merge( is_array( $existing ) ? $existing : [], [ 'enable_transliteration' => 'off' ] )
		);

		// Create tax classes with non-latin characters.
		\WC_Tax::create_tax_class( 'Сниженная ставка', 'reduced-cyrillic' );
		\WC_Tax::create_tax_class( '减税率', 'chinese-rate' );
		\WC_Tax::create_tax_class( 'Valid Rate', 'valid-rate' );

		$this->clearSchema();

		// Run an introspection query — this should not throw a 500 error.
		$query = '{
			__schema {
				types {
					name
				}
			}
		}';

		$response = $this->graphql( compact( 'query' ) );
		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( '__schema.types', static::NOT_FALSY ) ]
		);

		// Query the TaxClassEnum values.
		$query = '{
			__type(name: "TaxClassEnum") {
				enumValues {
					name
				}
			}
		}';

		$response    = $this->graphql( compact( 'query' ) );
		$enum_values = $this->lodashGet( $response, 'data.__type.enumValues' );
		$enum_names  = array_column( $enum_values, 'name' );

		// Non-latin names produce underscore-only values when transliteration is off.
		// These should be skipped to avoid collisions and meaningless enum entries.
		$this->assertNotContains( '_', $enum_names, 'Underscore-only enum values should be skipped.' );

		// The valid latin class should still be present.
		$this->assertContains( 'VALID_RATE', $enum_names, 'Valid latin tax class should be in the enum.' );
	}

	/**
	 * Test that enabling transliteration converts non-latin tax class names
	 * into valid, meaningful GraphQL enum values.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/637
	 */
	public function testNonLatinTaxClassTransliteratedWhenEnabled() {
		if ( ! function_exists( 'transliterator_transliterate' ) ) {
			$this->markTestSkipped( 'intl extension not available.' );
		}

		// Enable transliteration setting.
		$existing = get_option( 'woographql_settings', [] );
		update_option(
			'woographql_settings',
			array_merge( is_array( $existing ) ? $existing : [], [ 'enable_transliteration' => 'on' ] )
		);

		// Create tax classes with non-latin names.
		\WC_Tax::create_tax_class( 'Ставка НДС', 'nds-rate' );
		\WC_Tax::create_tax_class( '减税率', 'chinese-rate' );
		\WC_Tax::create_tax_class( 'Valid Rate', 'valid-rate' );

		$this->clearSchema();

		// Query the TaxClassEnum values via introspection.
		$query = '{
			__type(name: "TaxClassEnum") {
				enumValues {
					name
				}
			}
		}';

		$response = $this->graphql( compact( 'query' ) );
		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( '__type.enumValues', static::NOT_FALSY ) ]
		);

		$enum_values = $this->lodashGet( $response, 'data.__type.enumValues' );
		$enum_names  = array_column( $enum_values, 'name' );

		// Non-latin classes should be transliterated, not produce underscore-only names.
		$this->assertNotContains( '_', $enum_names, 'Transliterated enum values should not be underscore-only.' );

		// Verify all enum values have at least one alphanumeric character.
		foreach ( $enum_values as $value ) {
			$this->assertMatchesRegularExpression(
				'/[A-Za-z0-9]/',
				$value['name'],
				sprintf( 'Enum value "%s" must contain at least one alphanumeric character.', $value['name'] )
			);
		}

		// The Cyrillic class should be transliterated.
		$this->assertContains( 'STAVKA_NDS', $enum_names, 'Cyrillic tax class should be transliterated.' );

		// The Chinese class should be transliterated.
		$this->assertContains( 'JIAN_SHUI_LU', $enum_names, 'Chinese tax class should be transliterated.' );

		// The valid latin class should still be present.
		$this->assertContains( 'VALID_RATE', $enum_names, 'Valid latin tax class should be in the enum.' );
	}

	/**
	 * Test that product slug resolution uses WordPress's standard query mechanisms
	 * which i18n plugins (WPML, Polylang) can hook into.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/403
	 */
	public function testProductSlugResolutionUsesNodeResolver() {
		$product_id = $this->factory->product->createSimple();
		$slug       = get_post_field( 'post_name', $product_id );

		$query = '
			query ($slug: ID!) {
				product(id: $slug, idType: SLUG) {
					... on SimpleProduct {
						databaseId
						name
						slug
					}
				}
			}
		';

		$response = $this->graphql(
			[
				'query'     => $query,
				'variables' => [ 'slug' => $slug ],
			]
		);

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'product.databaseId', $product_id ),
				$this->expectedField( 'product.slug', $slug ),
			]
		);
	}

}
