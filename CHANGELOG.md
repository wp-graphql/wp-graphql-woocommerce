# Changelog

## [v0.3.1-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.3.1-beta) (2019-11-26)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.3.0-beta...v0.3.1-beta)

**Enhancements:**

- Product post\_type should be set to `show\_in\_graphql` [\#85](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/85)
- QL Session Handler 2.0 [\#174](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/174) ([kidunot89](https://github.com/kidunot89))
- Testing/CI configurations upgrade. [\#173](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/173) ([kidunot89](https://github.com/kidunot89))
- QL Search support added. [\#172](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/172) ([kidunot89](https://github.com/kidunot89))
- Release v0.3.0 [\#155](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/155) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Pagination with orderby when fetching Products causes products to be skipped [\#153](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/153)
- Unneeded "register\_graphql\_connection\(\)" calls removed. [\#165](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/165) ([kidunot89](https://github.com/kidunot89))
- Removes potential trouble filter [\#163](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/163) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Price not showing on products query [\#176](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/176)

**Merged pull requests:**

- Codecoverage boost [\#177](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/177) ([kidunot89](https://github.com/kidunot89))

## [v0.3.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.3.0-beta) (2019-10-25)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.2.2-beta...v0.3.0-beta)

**Breaking changes:**

- Product\(Object\) to Product\(Interface\) [\#159](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/159) ([kidunot89](https://github.com/kidunot89))
- Fixes WPGraphQL ACF integration [\#158](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/158) ([kidunot89](https://github.com/kidunot89))
- Adds WPGraphQL v0.4.0 support [\#156](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/156) ([kidunot89](https://github.com/kidunot89))

**Enhancements:**

- Namespaces refactored. [\#160](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/160) ([kidunot89](https://github.com/kidunot89))
- Release-v0.2.2 [\#154](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/154) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Escape hatch added on non-GraphQL requests. [\#146](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/146) ([kidunot89](https://github.com/kidunot89))

## [v0.2.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.2.2-beta) (2019-10-24)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.2.1-beta...v0.2.2-beta)

**Enhancements:**

- Sorting of products, Categories etc [\#138](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/138)
- Accessing meta\_data product property? [\#121](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/121)
- Improved ordering for some connections [\#145](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/145) ([kidunot89](https://github.com/kidunot89))
- "product\_connection\_catalog\_visibility" hook implemented. [\#142](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/142) ([kidunot89](https://github.com/kidunot89))
- "format" arg added to Product description fields [\#139](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/139) ([kidunot89](https://github.com/kidunot89))
- Release v0.2.1 [\#114](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/114) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Ajax call /?wc-ajax=get\_refreshed\_fragments clears cart when plugin installed [\#143](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/143)
- subcategory shows empty when image field is present [\#140](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/140)
- Extension breaks the hierarchy between pages [\#122](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/122)
- Order id undefined [\#119](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/119)
- categoryNameIn not filtering [\#116](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/116)
- Fixes product taxonomy hierachy resolution [\#150](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/150) ([kidunot89](https://github.com/kidunot89))
- Adds WPGraphQL JWT Auth fields to Customer type and mutations [\#148](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/148) ([kidunot89](https://github.com/kidunot89))
- Session header bugfix [\#113](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/113) ([kidunot89](https://github.com/kidunot89))

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
- Checkout mutation bugfix/enhancements [\#132](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/132) ([kidunot89](https://github.com/kidunot89))
- Adds "taxonomyFilter" to product connections [\#126](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/126) ([kidunot89](https://github.com/kidunot89))
- MetaData type and queries [\#123](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/123) ([kidunot89](https://github.com/kidunot89))
- PaymentGateway type. [\#118](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/118) ([kidunot89](https://github.com/kidunot89))
- CI upgrade [\#115](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/115) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- customerRegister mutation resolves wrong object for `viewer` field [\#111](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/111)
- I cant see the category thumbnail  [\#93](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/93)
- Bug related to resolving product connections by taxonomies fixed. [\#125](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/125) ([kidunot89](https://github.com/kidunot89))
- PostObject hierarchy bugfix [\#124](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/124) ([kidunot89](https://github.com/kidunot89))

**Merged pull requests:**

- Add contributor [\#131](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/131) ([ranaaterning](https://github.com/ranaaterning))

## [v0.2.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.2.0-beta) (2019-07-11)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.2-beta...v0.2.0-beta)

**Enhancements:**

- Release v0.2.0 code cleanup [\#107](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/107) ([kidunot89](https://github.com/kidunot89))
- updateItemQuantities mutation [\#106](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/106) ([kidunot89](https://github.com/kidunot89))
- deleteOrderItems mutation [\#104](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/104) ([kidunot89](https://github.com/kidunot89))
- Adds NO\_QL\_SESSION\_HANDLER flag [\#103](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/103) ([kidunot89](https://github.com/kidunot89))
- Adds product category image to schema. [\#102](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/102) ([kidunot89](https://github.com/kidunot89))
- Query products by slug and sku [\#101](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/101) ([kidunot89](https://github.com/kidunot89))
- checkout mutation [\#100](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/100) ([kidunot89](https://github.com/kidunot89))
- deleteOrder mutation [\#99](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/99) ([kidunot89](https://github.com/kidunot89))
- updateOrder mutation [\#98](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/98) ([kidunot89](https://github.com/kidunot89))
- createOrder mutation [\#97](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/97) ([kidunot89](https://github.com/kidunot89))
- Release v0.1.2 [\#86](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/86) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Custom attributes of variable products cannot be queried. [\#87](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/87)

**Closed issues:**

- Customer id doesn't match user id [\#90](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/90)

**Merged pull requests:**

- CHANGELOG updated [\#92](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/92) ([kidunot89](https://github.com/kidunot89))

## [v0.1.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.2-beta) (2019-06-23)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.1-beta...v0.1.2-beta)

**Enhancements:**

- ProductAttribute null key error fixed. [\#91](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/91) ([kidunot89](https://github.com/kidunot89))
- VariationAttribute field "id" updated. [\#89](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/89) ([kidunot89](https://github.com/kidunot89))
- QL Session Handler [\#88](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/88) ([kidunot89](https://github.com/kidunot89))
- Release v0.1.1 [\#81](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/81) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Tests needed [\#46](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/46)

## [v0.1.1-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.1-beta) (2019-06-06)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.1.0-beta...v0.1.1-beta)

## Description
Some mutations, a type, and a slight folder structure change.

## Mutations
- **Customer**
  - `registerCustomer` #55  
  - `updateCustomer` #67 
- **Cart**
  - `addToCart` #56 
  - `updateItemQuantity` #68
  - `removeItemsFromCart` #57 #69 
  - `restoreItemsToCart` #58 #70 
  - `emptyCart` #59 
  - `applyCoupon` #60 
  - `removeCoupons` #61 #71 

## Types
- `CartFee` #62 

## Changes
- `src` directory renamed to `includes` #73 

**Breaking changes:**

- Release v0.1.0 [\#54](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/54) ([kidunot89](https://github.com/kidunot89))

**Enhancements:**

- Add format argument to product pricing fields [\#75](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/75)
- Customer mutations [\#48](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/48)
- Cart mutations [\#18](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/18)
- Cleanup/Code coverage update. [\#83](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/83) ([kidunot89](https://github.com/kidunot89))
- Resolve formatted price string for pricing fields  [\#82](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/82) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Provide guest user authentication [\#79](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/79)
- Codeception versioning problem in Travis-CI build [\#77](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/77) ([kidunot89](https://github.com/kidunot89))

## [v0.1.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.1.0-beta) (2019-05-11)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.4-beta...v0.1.0-beta)

#### Release Notes
- Querying products and variations by product attribute implemented. [#50](https://github.com/kidunot89/wp-graphql-woocommerce/pull/50)
- More fields added to the `ProductVariation` type schema. A new `WPObject` type `VariationAttribute` implemented. [#49](https://github.com/kidunot89/wp-graphql-woocommerce/pull/49)
- `restricted_cap` filter added. [#53](https://github.com/kidunot89/wp-graphql-woocommerce/pull/53)
- Fixed bug related to `ProductCategoryToProduct` and `ProductTagToProduct` connections [#44](https://github.com/kidunot89/wp-graphql-woocommerce/pull/45)

**Enhancements:**

- "src" directory renamed to "includes" [\#73](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/73) ([kidunot89](https://github.com/kidunot89))
- `edit\_shop\_order` cap check in addFee [\#72](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/72) ([kidunot89](https://github.com/kidunot89))
- removeCoupon changed to removeCoupons. [\#71](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/71) ([kidunot89](https://github.com/kidunot89))
- restoreCartItem changed to restoreCartItems [\#70](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/70) ([kidunot89](https://github.com/kidunot89))
- removeItemFromCart changed to removeItemsFromCart [\#69](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/69) ([kidunot89](https://github.com/kidunot89))
- updateItemQuantity mutation [\#68](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/68) ([kidunot89](https://github.com/kidunot89))
- updateCustomer mutation [\#67](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/67) ([kidunot89](https://github.com/kidunot89))
- addFee mutation [\#63](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/63) ([kidunot89](https://github.com/kidunot89))
- CartFee type and queries [\#62](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/62) ([kidunot89](https://github.com/kidunot89))
- removeCoupon mutation. [\#61](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/61) ([kidunot89](https://github.com/kidunot89))
- applyCoupon mutation [\#60](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/60) ([kidunot89](https://github.com/kidunot89))
- emptyCart mutation. [\#59](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/59) ([kidunot89](https://github.com/kidunot89))
- restoreCartItem mutation [\#58](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/58) ([kidunot89](https://github.com/kidunot89))
- removeItemFromCart mutation [\#57](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/57) ([kidunot89](https://github.com/kidunot89))
- addToCart mutation [\#56](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/56) ([kidunot89](https://github.com/kidunot89))
- Feature/register customer mutation [\#55](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/55) ([kidunot89](https://github.com/kidunot89))

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
- More ProductVariation fields [\#49](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/49) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Query products by categories returns an empty array. [\#44](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/44)
- "WC\_Product\_Query" replaced with "WP\_Query" for performance benefits. [\#64](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/64) ([kidunot89](https://github.com/kidunot89))
- Bugfix/\#44 [\#45](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/45) ([kidunot89](https://github.com/kidunot89))

**Merged pull requests:**

- to v0.0.3 [\#43](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/43) ([kidunot89](https://github.com/kidunot89))
- Release v0.0.3 beta [\#42](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/42) ([kidunot89](https://github.com/kidunot89))

## [v0.0.3-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0.3-beta) (2019-04-25)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0.2-beta...v0.0.3-beta)

**Enhancements:**

- Replaces WP\_Query to WC\_Order\_Query in Order connections [\#38](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/38) ([kidunot89](https://github.com/kidunot89))
- Replaces WP\_Query to WC\_Product\_Query in Product connections [\#37](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/37) ([kidunot89](https://github.com/kidunot89))
- Pagination fix for CPT-backed CRUD objects connections [\#36](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/36) ([kidunot89](https://github.com/kidunot89))
- Cart-type and queries and customer query [\#30](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/30) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Pagination broken [\#29](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/29)
- after\_success script added [\#32](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/32) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Unsetting "object\_ids" on all connections [\#39](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/39)

**Merged pull requests:**

- Master [\#35](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/35) ([kidunot89](https://github.com/kidunot89))
- Release v0.0.2 beta [\#34](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/34) ([kidunot89](https://github.com/kidunot89))
- travis.yml updated [\#1](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/1) ([kidunot89](https://github.com/kidunot89))

## [v0.0.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0.2-beta) (2019-04-22)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/0db77c26ab463e6203df99eed2679f79ce3e4d60...v0.0.2-beta)

**Enhancements:**

- TaxClass type  [\#27](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/27)
- Order-Item type queries [\#13](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/13)
- Order items, TaxRate, and ShippingMethod [\#28](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/28) ([kidunot89](https://github.com/kidunot89))
- Where args for Coupon, Order, and Refund connections [\#24](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/24) ([kidunot89](https://github.com/kidunot89))
- Polishing Product types and connections [\#22](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/22) ([kidunot89](https://github.com/kidunot89))
- Testing and CI renovation [\#21](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/21) ([kidunot89](https://github.com/kidunot89))
- WC Post-type re-expansion [\#11](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/11) ([kidunot89](https://github.com/kidunot89))
- Customer/Order/Refund models, data-loaders, connections, types, and queries [\#10](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/10) ([kidunot89](https://github.com/kidunot89))
- WPGraphQL v0.3.0 migration [\#9](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/9) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- no queries work [\#31](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/31)

**Merged pull requests:**

- Formatting code to WordPress Coding Standards [\#8](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/8) ([kidunot89](https://github.com/kidunot89))
- Feature/Product type [\#7](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/7) ([kidunot89](https://github.com/kidunot89))
- Feature/coupon type [\#6](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/6) ([kidunot89](https://github.com/kidunot89))
- Update issue templates [\#5](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/5) ([kidunot89](https://github.com/kidunot89))
- Create LICENSE [\#3](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/3) ([kidunot89](https://github.com/kidunot89))
- Create CODE\_OF\_CONDUCT.md [\#2](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/2) ([kidunot89](https://github.com/kidunot89))



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
