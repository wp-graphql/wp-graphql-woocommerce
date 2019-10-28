# Change Log

## [v0.3.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.3.0-beta) (2019-10-25)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.2.2-beta...v0.3.0-beta)

**Merged pull requests:**

- Release-v0.2.2 [\#154](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/154) ([kidunot89](https://github.com/kidunot89))

## [v0.2.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.2.2-beta) (2019-10-24)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.2.1-beta...v0.2.2-beta)

**Enhancements:**

- Sorting of products, Categories etc [\#138](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/138)
- Accessing meta\_data product property? [\#121](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/121)
- Improved ordering for some connections [\#145](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/145) ([kidunot89](https://github.com/kidunot89))
- "product\_connection\_catalog\_visibility" hook implemented. [\#142](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/142) ([kidunot89](https://github.com/kidunot89))
- "format" arg added to Product description fields [\#139](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/139) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Ajax call /?wc-ajax=get\_refreshed\_fragments clears cart when plugin installed [\#143](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/143)
- subcategory shows empty when image field is present [\#140](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/140)
- Extension breaks the hierarchy between pages [\#122](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/122)
- Order id undefined [\#119](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/119)
- categoryNameIn not filtering [\#116](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/116)
- Fixes product taxonomy hierachy resolution [\#150](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/150) ([kidunot89](https://github.com/kidunot89))
- Adds WPGraphQL JWT Auth fields to Customer type and mutations [\#148](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/148) ([kidunot89](https://github.com/kidunot89))
- Escape hatch added on non-GraphQL requests. [\#146](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/146) ([kidunot89](https://github.com/kidunot89))
- Release v0.2.1 [\#114](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/114) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Order Creation directly  [\#149](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/149)
- Customer checkout and order mutations are not working [\#147](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/147)
- Price Type, and name not showing up for product as Typed values [\#137](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/137)
- Add AND and OR statement to where clause.  [\#120](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/120)

**Merged pull requests:**

- FUNDING.yml added. [\#151](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/151) ([kidunot89](https://github.com/kidunot89))
- CONTRIBUTING.md added [\#144](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/144) ([kidunot89](https://github.com/kidunot89))
- v0.2.1 hotfix to develop [\#135](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/135) ([kidunot89](https://github.com/kidunot89))

## [v0.2.1-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.2.1-beta) (2019-08-27)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.2.0-beta...v0.2.1-beta)

**Breaking changes:**

- Release v0.2.0 [\#96](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/96) ([kidunot89](https://github.com/kidunot89))

**Enhancements:**

- Hooks for mutations [\#108](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/108)
- productBy query should support querying by slug [\#95](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/95)
- Support update of multiple quantities in cart in a single mutation [\#94](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/94)
- Other mutations [\#19](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/19)
- Order mutations [\#16](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/16)

**Fixed:**

- customerRegister mutation resolves wrong object for `viewer` field [\#111](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/111)
- I cant see the category thumbnail  [\#93](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/93)
- Session header bugfix [\#113](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/113) ([kidunot89](https://github.com/kidunot89))

## [v0.2.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.2.0-beta) (2019-07-11)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.2-beta...v0.2.0-beta)

**Fixed:**

- Custom attributes of variable products cannot be queried. [\#87](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/87)
- Release v0.1.2 [\#86](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/86) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Customer id doesn't match user id [\#90](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/90)

**Merged pull requests:**

- CHANGELOG updated [\#92](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/92) ([kidunot89](https://github.com/kidunot89))

## [v0.1.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.2-beta) (2019-06-23)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.1-beta...v0.1.2-beta)

**Fixed:**

- Release v0.1.1 [\#81](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/81) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Tests needed [\#46](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/46)

## [v0.1.1-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.1-beta) (2019-06-06)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.0-beta...v0.1.1-beta)

**Breaking changes:**

- Release v0.1.0 [\#54](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/54) ([kidunot89](https://github.com/kidunot89))

**Enhancements:**

- Add format argument to product pricing fields [\#75](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/75)
- Customer mutations [\#48](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/48)
- Cart mutations [\#18](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/18)

**Fixed:**

- Provide guest user authentication [\#79](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/79)
- Codeception versioning problem in Travis-CI build [\#77](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/77) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Release v0.1.0 Summary [\#74](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/74)

## [v0.1.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.0-beta) (2019-05-11)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.4-beta...v0.1.0-beta)

**Closed issues:**

- Release v0.0.4 Summary [\#66](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/66)

**Merged pull requests:**

- Release v0.0.4 [\#65](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/65) ([kidunot89](https://github.com/kidunot89))

## [v0.0.4-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0.4-beta) (2019-05-10)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.3-beta...v0.0.4-beta)

**Enhancements:**

- Add filter for restricted\_cap in models [\#51](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/51)
- Add name to ProductVariation [\#41](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/41)
- Cart type and queries [\#12](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/12)
- Adds "\*\_restricted\_cap" filters [\#53](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/53) ([kidunot89](https://github.com/kidunot89))
- ProductAttribute completion [\#50](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/50) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Query products by categories returns an empty array. [\#44](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/44)
- More ProductVariation fields [\#49](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/49) ([kidunot89](https://github.com/kidunot89))
- Bugfix/\#44 [\#45](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/45) ([kidunot89](https://github.com/kidunot89))

**Merged pull requests:**

- to v0.0.3 [\#43](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/43) ([kidunot89](https://github.com/kidunot89))

## [v0.0.3-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0.3-beta) (2019-04-25)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.2-beta...v0.0.3-beta)

**Enhancements:**

- Cart-type and queries and customer query [\#30](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/30) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Pagination broken [\#29](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/29)
- Pagination fix for CPT-backed CRUD objects connections [\#36](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/36) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Unsetting "object\_ids" on all connections [\#39](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/39)

**Merged pull requests:**

- Master [\#35](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/35) ([kidunot89](https://github.com/kidunot89))

## [v0.0.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0.2-beta) (2019-04-22)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/0db77c26ab463e6203df99eed2679f79ce3e4d60...v0.0.2-beta)

**Enhancements:**

- TaxClass type  [\#27](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/27)
- Order-Item type queries [\#13](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/13)
- Order items, TaxRate, and ShippingMethod [\#28](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/28) ([kidunot89](https://github.com/kidunot89))
- Where args for Coupon, Order, and Refund connections [\#24](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/24) ([kidunot89](https://github.com/kidunot89))
- Testing and CI renovation [\#21](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/21) ([kidunot89](https://github.com/kidunot89))
- WPGraphQL v0.3.0 migration [\#9](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/9) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- no queries work [\#31](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/31)
- after\_success script added [\#32](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/32) ([kidunot89](https://github.com/kidunot89))
- Polishing Product types and connections [\#22](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/22) ([kidunot89](https://github.com/kidunot89))

**Merged pull requests:**

- Formatting code to WordPress Coding Standards [\#8](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/8) ([kidunot89](https://github.com/kidunot89))
- Feature/Product type [\#7](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/7) ([kidunot89](https://github.com/kidunot89))
- Feature/coupon type [\#6](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/6) ([kidunot89](https://github.com/kidunot89))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*