<img src="./logo.svg" width="250px" />

# WPGraphQL WooCommerce (WooGraphQL)

<a href="https://woographql.com/" target="_blank">Docs</a> • <a href="https://www.axistaylor.com" target="_blank">AxisTaylor</a> • <a href="https://wpgql-slack.herokuapp.com/" target="_blank">Join Slack</a>

[![Automated-Testing](https://github.com/wp-graphql/wp-graphql-woocommerce/workflows/continuous_integration/badge.svg)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions?query=workflow%3A%22Automated-Testing%22) [![Coding-Standards](https://github.com/wp-graphql/wp-graphql-woocommerce/workflows/lint_code/badge.svg)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions?query=workflow%3A%22Coding-Standards%22) [![Coverage Status](https://coveralls.io/repos/github/wp-graphql/wp-graphql-woocommerce/badge.svg?branch=develop)](https://coveralls.io/github/wp-graphql/wp-graphql-woocommerce?branch=develop) [![Financial Contributors on Open Collective](https://opencollective.com/woographql/all/badge.svg?label=financial+contributors)](https://opencollective.com/woographql)

## Quick Install

1. Install & activate [WooCommerce](https://woocommerce.com/)
2. Install & activate [WPGraphQL](https://www.wpgraphql.com/)
3. Clone or download the zip of this repository into your WordPress plugin directory & activate the **WP GraphQL WooCommerce** plugin.
4. (Optional) Install & activate [WPGraphQL-JWT-Authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication) to add a `login` mutation that returns a JSON Web Token.
5. (Optional) Install & activate [WPGraphQL-CORS](https://github.com/funkhaus/wp-graphql-cors) to add an extra layer of security using HTTP CORS and some of WPGraphQL advanced functionality.

## What does this plugin do?

It adds WooCommerce functionality to the WPGraphQL schema using WooCommerce's [CRUD](https://github.com/woocommerce/woocommerce/wiki/CRUD-Objects-in-3.0) objects.

## Features

- Query **product**, **product variations**, **customers**, **coupons**, **orders**, **refunds** and **more** with complex filtering options.
- Manipulate customer session data using customer and cart mutations while managing customer session token using HTTP headers or cookies *(not recommended)*. *[HTTP header example w/ React/Apollo](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/88)*
- Create orders using the `order` mutations with the `checkout` mutation.

## Future Features

- Payment Processing
- Adminstrator mutations. Eg. Creating and deleting products, coupons, and refunds.

## Playground

Feel free to test out the extension using this [GraphiQL Playground](https://woographql.com/playground). The playground allows you to execute queries and mutations, as well as view the schema.

## Wanna help support WooGraphQL's future.

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
