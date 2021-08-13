<img src="./logo.svg" width="250px" />

# WPGraphQL WooCommerce (WooGraphQL)

<a href="https://woographql.com/" target="_blank">Docs</a> • <a href="https://www.axistaylor.com" target="_blank">AxisTaylor</a> • <a href="https://join.slack.com/t/wp-graphql/shared_invite/zt-3vloo60z-PpJV2PFIwEathWDOxCTTLA" target="_blank">Join Slack</a>

[![Automated-Testing](https://github.com/wp-graphql/wp-graphql-woocommerce/workflows/Automated-Testing/badge.svg?branch=develop)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions?query=workflow%3A%22Automated-Testing%22) [![Coding-Standards](https://github.com/wp-graphql/wp-graphql-woocommerce/workflows/Coding-Standards/badge.svg?branch=develop)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions?query=workflow%3A%22Coding-Standards%22) [![Coverage Status](https://coveralls.io/repos/github/wp-graphql/wp-graphql-woocommerce/badge.svg?branch=develop)](https://coveralls.io/github/wp-graphql/wp-graphql-woocommerce?branch=develop) [![Financial Contributors on Open Collective](https://opencollective.com/woographql/all/badge.svg?label=financial+contributors)](https://opencollective.com/woographql)

## Quick Install

1. Install & activate [WooCommerce](https://woocommerce.com/)
2. Install & activate [WPGraphQL](https://www.wpgraphql.com/)
3. Download the latest release from [here](https://github.com/wp-graphql/wp-graphql-woocommerce/releases) or Clone / download the zip of this repository into your WordPress plugin directory, run `composer install` in plugin directory & activate the **WP GraphQL WooCommerce** plugin.
4. (Optional) Install & activate [WPGraphQL-JWT-Authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication) to add a `login` mutation that returns a JSON Web Token.
5. (Optional) Install & activate [WPGraphQL-CORS](https://github.com/funkhaus/wp-graphql-cors) to add an extra layer of security using HTTP CORS and some of WPGraphQL advanced functionality.

## What can you do with this extension?

- Query your shops products and variations with complex filtering options.
- Query customers, orders, coupons, and refunds (*).
- Manage a customer's session with JWTs and cart/customer queries and mutations(*).
- Create orders manually (*), automatically with the checkout mutation, or pass a customer's session to the WooCommerce checkout page in your theme for complete payment gateway support (#).

(*) These operations have user restrictions. Please read up on authenticating an user with [here](https://www.wpgraphql.com/docs/authentication-and-authorization/), then view [this React/Apollo example](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/88) with the added on usage of customer session Token.

(#) This is the recommended method on checkout. You can read it's usage in [this](https://jacobarriola.com/post/hosted-woocommerce-checkout-headless-application#load-the-session-in-woocommerce) excellent write-up by @jacobarriola

## Why don't the WooCommerce CPT GraphQL types support all the same features as most WordPress CPTs that WPGraphQL exposes?

The CPTs as well as most of the data objects that WooCommerce defines are wrapped in a object managers distributed by a data store system.

This data store system allows for each individual data object to be defined however needed. What this means is, although by out of the box objects like **products**, **orders**, and **coupons** are defined as WordPress CPTs they don't have to be.

This is what also enables WooCommerce to store most meta connected to these CPTs in separate tables. The object data doesn't even have to be in the same database if the object's data store designed to manage somewhere else, but we are getting out of the scope of this question.

What does all this :point_up: have to do with WooCommerce's CPTs' functionality? Well, the object managers distributed by the data store are WooGraphQL first point of contact for pretty much everything. Unlike the most common CPTs which use a **WP_Post** object as their data source and a **WPGraphQL\Model\Post** object as their model, WooGraphQL uses object managers as the data source for the CPTs and each individual has it's own model with it's own set of permissions and restrictions.

This has led to some friction is certain areas of the schema where WooGraphQL support is lacking. I'm sorry for the inconvience, myself and whole **WPGraphQL** org are working to reduce this friction and WooGraphQL properly integrated with all **WPGraphQL** + **WPGraphQL ACF** features.

Thank you for your patience
@kidunot89

## Future Features

- Product CRUD mutations.
- And some other stuff I'm sure :thinking_face:

## Playground

Feel free to test out the extension using this [GraphiQL Playground](https://woographql.com/playground). The playground allows you to execute queries and mutations, as well as view the schema (*).

(*) I have a tendency to forget to update the playground between releases :sweat_smile:, so if you believe this to be the case look me up somewhere on this page and lemme know :man_shrugging:

## Wanna help support WooGraphQL's future

- Sponsor **@kidunot89** *(WooGraphQL Creator/Developer)* on **[Github](https://github.com/sponsors/kidunot89)**
- Sponsor **WooGraphQL** on **[OpenCollective](https://opencollective.com/woographql)**
- Sponsor **WPGraphQL** on **[OpenCollective](http://opencollective.com/wp-graphql)**
- Sponsor **GraphQL-PHP** on **[OpenCollective](https://opencollective.com/webonyx-graphql-php)**
- Or **[Contribute](./CONTRIBUTING.md)**

## Follow [![alt text](http://i.imgur.com/tXSoThF.png)](https://twitter.com/woographql)[![alt text](http://i.imgur.com/P3YfQoD.png)](https://www.facebook.com/woographql)

## Demo/Examples

- Examples with Next.js
  - [Next.js WooCommerce Theme](https://github.com/imranhsayed/woo-next) [[source]](https://github.com/imranhsayed/woo-next) [[demo video]](https://youtu.be/EGjY3X868YQ)
- Examples with Gatsby
  - [Gatsby WooCommerce Theme](https://gatsby-woocommerce-theme.netlify.app/) [[source]](https://github.com/imranhsayed/gatsby-woocommerce-themes) [[demo video]](https://youtu.be/ygaE8ZdPEX8)

## Who using WooGraphQL

## Contributors

### Code Contributors

This project exists thanks to all the people who contribute. [[Contribute](CONTRIBUTING.md)].
<a href="https://github.com/wp-graphql/wp-graphql-woocommerce/graphs/contributors"><img src="https://opencollective.com/woographql/contributors.svg?width=890&button=false" /></a>

### Financial Contributors

Become a financial contributor and help us sustain our community. [[Contribute](https://opencollective.com/woographql/contribute)]

#### Individuals

<a href="https://opencollective.com/woographql"><img src="https://opencollective.com/woographql/individuals.svg?width=890"></a>

#### Organizations

Support this project with your organization. Your logo will show up here with a link to your website. [[Contribute](https://opencollective.com/woographql/contribute)]

<a href="https://opencollective.com/woographql/organization/0/website"><img src="https://opencollective.com/woographql/organization/0/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/1/website"><img src="https://opencollective.com/woographql/organization/1/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/2/website"><img src="https://opencollective.com/woographql/organization/2/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/3/website"><img src="https://opencollective.com/woographql/organization/3/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/4/website"><img src="https://opencollective.com/woographql/organization/4/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/5/website"><img src="https://opencollective.com/woographql/organization/5/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/6/website"><img src="https://opencollective.com/woographql/organization/6/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/7/website"><img src="https://opencollective.com/woographql/organization/7/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/8/website"><img src="https://opencollective.com/woographql/organization/8/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/9/website"><img src="https://opencollective.com/woographql/organization/9/avatar.svg"></a>
