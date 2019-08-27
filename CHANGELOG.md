# Change Log

## [v0.2.1-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.2.1-beta) (2019-08-27)
[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.2.0-beta...v0.2.1-beta)

**Implemented enhancements:**

- Hooks for mutations [\#108](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/108)
- productBy query should support querying by slug [\#95](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/95)
- Support update of multiple quantities in cart in a single mutation [\#94](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/94)
- Other mutations [\#19](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/19)
- Order mutations [\#16](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/16)
- Checkout mutation bugfix/enhancements [\#132](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/132) ([kidunot89](https://github.com/kidunot89))
- Adds "taxonomyFilter" to product connections [\#126](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/126) ([kidunot89](https://github.com/kidunot89))
- MetaData type and queries [\#123](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/123) ([kidunot89](https://github.com/kidunot89))
- PaymentGateway type. [\#118](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/118) ([kidunot89](https://github.com/kidunot89))
- CI upgrade [\#115](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/115) ([kidunot89](https://github.com/kidunot89))

**Fixed bugs:**

- customerRegister mutation resolves wrong object for `viewer` field [\#111](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/111)
- I cant see the category thumbnail  [\#93](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/93)

**Merged pull requests:**

- Add contributor [\#131](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/131) ([ranaaterning](https://github.com/ranaaterning))
- Bug related to resolving product connections by taxonomies fixed. [\#125](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/125) ([kidunot89](https://github.com/kidunot89))
- PostObject hierarchy bugfix [\#124](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/124) ([kidunot89](https://github.com/kidunot89))

## [v0.2.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.2.0-beta) (2019-07-11)
[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.2-beta...v0.2.0-beta)

**Fixed bugs:**

- Custom attributes of variable products cannot be queried. [\#87](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/87)

**Closed issues:**

- Customer id doesn't match user id [\#90](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/90)

## [v0.1.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.2-beta) (2019-06-23)
[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.1-beta...v0.1.2-beta)

**Closed issues:**

- Tests needed [\#46](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/46)

## [v0.1.1-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.1-beta) (2019-06-06)
[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.0-beta...v0.1.1-beta)

**Implemented enhancements:**

- Add format argument to product pricing fields [\#75](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/75)
- Customer mutations [\#48](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/48)
- Cart mutations [\#18](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/18)

**Fixed bugs:**

- Provide guest user authentication [\#79](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/79)

**Closed issues:**

- Release v0.1.0 Summary [\#74](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/74)

## [v0.1.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.0-beta) (2019-05-11)
[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.4-beta...v0.1.0-beta)

**Closed issues:**

- Release v0.0.4 Summary [\#66](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/66)

## [v0.0.4-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0.4-beta) (2019-05-10)
[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.3-beta...v0.0.4-beta)

**Implemented enhancements:**

- Add filter for restricted\_cap in models [\#51](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/51)
- Add name to ProductVariation [\#41](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/41)
- Cart type and queries [\#12](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/12)

**Fixed bugs:**

- Query products by categories returns an empty array. [\#44](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/44)

## [v0.0.3-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0.3-beta) (2019-04-25)
[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.2-beta...v0.0.3-beta)

**Fixed bugs:**

- Pagination broken [\#29](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/29)

**Closed issues:**

- Unsetting "object\_ids" on all connections [\#39](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/39)



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*