# Changelog

## [v0.8.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.8.1) (2021-03-17)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.8.0...v0.8.1)

**New Features:**

- Added refund date field [\#450](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/450) ([hilmerx](https://github.com/hilmerx))

**Fixed:**

- Bugfix/product reviews connections [\#457](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/457) ([kidunot89](https://github.com/kidunot89))
- JSON stringify string to match expectation of "extraData" on cartItem [\#453](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/453) ([davevanhoorn](https://github.com/davevanhoorn))

**Other Changes:**

- \[Docs\]: homepage typo fixes [\#445](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/445) ([jacobarriola](https://github.com/jacobarriola))

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

- Allow multiple "orderby" fields [\#374](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/374) ([loganstellway](https://github.com/loganstellway))

**Other Changes:**

- Release v0.7.0 [\#383](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/383) ([kidunot89](https://github.com/kidunot89))

## [v0.6.2](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.6.2) (2020-11-24)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.6.1...v0.6.2)

**New Features:**

- Better extension support [\#353](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/353) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Make the username field optional in registerCustomer mutation [\#381](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/381) ([kidunot89](https://github.com/kidunot89))

**Other Changes:**

- Feature/itemized cart tax [\#380](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/380) ([kidunot89](https://github.com/kidunot89))
- "Product\_Connection\_Resolver::set\_query\_arg\(\)" removed. [\#376](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/376) ([kidunot89](https://github.com/kidunot89))
- WPGraphQL v1 CI Fix [\#375](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/375) ([kidunot89](https://github.com/kidunot89))
- Guard against empty terms [\#373](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/373) ([jacobarriola](https://github.com/jacobarriola))
- support added for externally defined product type queries. [\#366](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/366) ([kidunot89](https://github.com/kidunot89))
- Guard against false terms when plucking IDs [\#364](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/364) ([jacobarriola](https://github.com/jacobarriola))
- Fix Syntax Error in php7.2 and 7.4 [\#355](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/355) ([namli](https://github.com/namli))
- Connect terms to their source [\#351](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/351) ([jacobarriola](https://github.com/jacobarriola))
- Return connected TermObjects from the PostObjectType [\#346](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/346) ([jacobarriola](https://github.com/jacobarriola))
- Adds demo/examples [\#344](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/344) ([imranhsayed](https://github.com/imranhsayed))

## [v0.6.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.6.1) (2020-10-15)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.5.1...v0.6.1)

## Release Summary

- [x] Demo/Examples sections added to README.md
- [x] More WooGraphQL + WPGraphQL extension integration bugfixes
- [x] Connection resolver classes support all new WPGraphQL v0.6.0+ features
- [x] Better cart validation and error messages
- [x] Replaces unauthorized queries with authorized queries for unauthorized queries instead of return `null`. For example `orders(...) {...}` should default to `customer{ orders(...) {...} }` when user is not authorized to execute `orders(...) {...}`

**New Features:**

- Support for some node interfaces added to the product and order models. [\#337](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/337) ([kidunot89](https://github.com/kidunot89))
- Field caps removed from product raw price and description fields. [\#332](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/332) ([kidunot89](https://github.com/kidunot89))
- "Root\_Query" class implemented. [\#331](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/331) ([kidunot89](https://github.com/kidunot89))
- "price" field added to "GroupProduct" type. [\#319](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/319) ([kidunot89](https://github.com/kidunot89))
- Two new fields added to the "ProductCategory" type. [\#318](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/318) ([kidunot89](https://github.com/kidunot89))
- Adds some label fields to the attribute types. [\#314](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/314) ([kidunot89](https://github.com/kidunot89))
- New error handling method introduced in the "addToCart" mutation [\#312](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/312) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Fixes downloadableItems accessibility bug. [\#316](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/316) ([kidunot89](https://github.com/kidunot89))
- Fixes cart item validation and error handling on checkout [\#315](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/315) ([kidunot89](https://github.com/kidunot89))
- "galleryImages" connection refactored. [\#311](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/311) ([kidunot89](https://github.com/kidunot89))
- Fixes some unit tests [\#302](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/302) ([kidunot89](https://github.com/kidunot89))
- Rating input type changed to "Int" [\#301](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/301) ([kidunot89](https://github.com/kidunot89))
- Fixed : missing static keyword for static variable [\#294](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/294) ([hwsiew](https://github.com/hwsiew))

**Other Changes:**

- Remove undefined codecept\_debug\(\) function [\#343](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/343) ([jacobarriola](https://github.com/jacobarriola))
- Fixed : \#303 [\#304](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/304) ([hwsiew](https://github.com/hwsiew))
- Skip conditional added to stripe test [\#298](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/298) ([kidunot89](https://github.com/kidunot89))
- Adds support for changes made in WPGraphQL v0.9.0 [\#288](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/288) ([kidunot89](https://github.com/kidunot89))
- Update some README.md links [\#287](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/287) ([kidunot89](https://github.com/kidunot89))
- Adds Local-Testing Contribution Guides [\#242](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/242) ([kidunot89](https://github.com/kidunot89))



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
