# Changelog

## [v0.10.5](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.5) (2021-10-26)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.4...v0.10.5)

**New Features:**

- feat: applied coupon description field added [\#572](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/572) ([kidunot89](https://github.com/kidunot89))
- chore: CartItem product connection resolvers updated. [\#571](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/571) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- fix: order item connection cursor fixed. [\#574](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/574) ([kidunot89](https://github.com/kidunot89))
- fix: updateReview input requirements fixed [\#570](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/570) ([kidunot89](https://github.com/kidunot89))
- fix: respect woocommerce tax display settings in cart [\#567](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/567) ([florianbepunkt](https://github.com/florianbepunkt))
- make order processing methods static [\#566](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/566) ([oskarmodig](https://github.com/oskarmodig))
- Product and order connections queryClass set. [\#550](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/550) ([kidunot89](https://github.com/kidunot89))

## [v0.10.4](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.4) (2021-09-08)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.3...v0.10.4)

**Fixed:**

- fix: session expiration extended to 2 weeks [\#551](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/551) ([kidunot89](https://github.com/kidunot89))
- Replace deprecated function nonce\_user\_logged\_out [\#547](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/547) ([kpoelhekke](https://github.com/kpoelhekke))
- Fix raw pricing returning null [\#544](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/544) ([dpacmittal](https://github.com/dpacmittal))
- bugfix: customer call returns session token for guest users [\#541](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/541) ([johnforte](https://github.com/johnforte))
- Fix: pass expected checkout validation WP\_Error instance [\#531](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/531) ([jeebay](https://github.com/jeebay))

**Other Changes:**

- Update Quick Install in README.md [\#538](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/538) ([ramyareye](https://github.com/ramyareye))

## [v0.10.3](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.3) (2021-08-11)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.2...v0.10.3)

**New Features:**

- Updates to be compatible with WPGraphQL v1.6.1 [\#537](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/537) ([kidunot89](https://github.com/kidunot89))

## [v0.10.2](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.2) (2021-07-07)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.1...v0.10.2)

**Fixed:**

- Bugfix/global autoloader support [\#524](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/524) ([kidunot89](https://github.com/kidunot89))

## [v0.10.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.1) (2021-07-06)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.0...v0.10.1)

**New Features:**

- CartItem Product edge field "simpleAttributes" implemented and tested. [\#521](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/521) ([kidunot89](https://github.com/kidunot89))
- Support for custom order statuses. [\#518](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/518) ([kidunot89](https://github.com/kidunot89))
- Coupon mutations added. [\#510](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/510) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Fix: product connection sorting [\#522](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/522) ([kidunot89](https://github.com/kidunot89))
- Fix: Access denied state for coupon and order connections. [\#523](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/523) ([kidunot89](https://github.com/kidunot89))

## [v0.10.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.0) (2021-06-14)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.8.1...v0.10.0)

**Fixed:**

- "Customer\_Connection\_Resolver" deprecated. [\#511](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/511) ([kidunot89](https://github.com/kidunot89))
- Cart\_Item\_Connection\_Resolver pagination fixed. [\#509](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/509) ([kidunot89](https://github.com/kidunot89))
- Update class-checkout-mutation.php [\#507](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/507) ([manuelsampl](https://github.com/manuelsampl))
- "Order/Refund\_Connection\_Resolver" classes deprecated [\#500](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/500) ([kidunot89](https://github.com/kidunot89))
- "Coupon\_Connection\_Resolver" deprecated. [\#497](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/497) ([kidunot89](https://github.com/kidunot89))
- "Product\_Connection\_Resolver" class deprecated [\#495](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/495) ([kidunot89](https://github.com/kidunot89))
- Session Transaction Manager bugfixes and enhancements [\#492](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/492) ([jacobarriola](https://github.com/jacobarriola))
- Checkout mutation: Add or Update order metas [\#484](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/484) ([adrienpicard](https://github.com/adrienpicard))

**Other Changes:**

- update plugin URL to point to the actual repo [\#459](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/459) ([andrewminion-luminfire](https://github.com/andrewminion-luminfire))

## [v0.8.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.8.1) (2021-03-17)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.8.0...v0.8.1)

**New Features:**

- Added refund date field [\#450](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/450) ([hilmerx](https://github.com/hilmerx))

**Fixed:**

- Bugfix/product reviews connections [\#457](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/457) ([kidunot89](https://github.com/kidunot89))
- JSON stringify string to match expectation of "extraData" on cartItem [\#453](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/453) ([davevanhoorn](https://github.com/davevanhoorn))

**Other Changes:**

- \[Docs\]: homepage typo fixes [\#445](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/445) ([jacobarriola](https://github.com/jacobarriola))
- Release v0.8.0 [\#444](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/444) ([kidunot89](https://github.com/kidunot89))

## [v0.8.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.8.0) (2021-03-01)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.7.0...v0.8.0)

**Breaking changes:**

- Cart transaction queue refactored [\#398](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/398) ([kidunot89](https://github.com/kidunot89))

**New Features:**

- New cart mutations and cart bugfixes. [\#439](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/439) ([kidunot89](https://github.com/kidunot89))
- Docker/Codeception/CI configurations overhauled [\#416](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/416) ([kidunot89](https://github.com/kidunot89))
- implements metadata for customer register and update [\#402](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/402) ([believelody](https://github.com/believelody))

**Fixed:**

- Variation image: guard against null image\_id [\#441](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/441) ([jacobarriola](https://github.com/jacobarriola))
- Typecast added to "MetaData" type field resolvers return values [\#430](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/430) ([kidunot89](https://github.com/kidunot89))
- Bugfix variations [\#424](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/424) ([kidunot89](https://github.com/kidunot89))
- Variable product pricing ranges fixed. [\#387](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/387) ([kidunot89](https://github.com/kidunot89))

**Other Changes:**

- Update Slack link [\#417](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/417) ([jasonbahl](https://github.com/jasonbahl))
- \[Docs\]: Fix 'Edit on GitHub' link [\#395](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/395) ([jacobarriola](https://github.com/jacobarriola))
- \[Docs\]: Update sample queries with latest schema changes [\#394](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/394) ([jacobarriola](https://github.com/jacobarriola))
- WPGraphQLTestCase implemented [\#322](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/322) ([kidunot89](https://github.com/kidunot89))

## [v0.7.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.7.0) (2020-11-24)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.6.2...v0.7.0)

**Fixed:**

- Allow multiple "orderby" fields [\#374](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/374) ([lstellway](https://github.com/lstellway))

**Other Changes:**

- Release v0.7.0 [\#383](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/383) ([kidunot89](https://github.com/kidunot89))



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
