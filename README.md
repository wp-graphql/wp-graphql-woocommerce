<img src="./logo.svg" width="250px">

# WPGraphQL WooCommerce
[![Build Status](https://travis-ci.org/kidunot89/wp-graphql-woocommerce.svg?branch=master)](https://travis-ci.org/kidunot89/wp-graphql-woocommerce) [![Coverage Status](https://coveralls.io/repos/github/kidunot89/wp-graphql-woocommerce/badge.svg?branch=master)](https://coveralls.io/github/kidunot89/wp-graphql-woocommerce?branch=master)

## Note: This plugin is in early stages and is not quite ready for use.

## Quick Install
1. Install & activate [WooCommerce](https://woocommerce.com/)
2. Install & activate [WPGraphQL](https://www.wpgraphql.com/)
3. (Optional) Install & activate [WPGraphQL-JWT-Authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication) to add a `login` mutation that returns a JSON Web Token.
4. Clone or download the zip of this repository into your WordPress plugin directory & activate the **WP GraphQL WooCommerce** plugin

## What does this plugin do?
It adds WooCommerce functionality to the WPGraphQL schema using WooCommerce's [CRUD](https://github.com/woocommerce/woocommerce/wiki/CRUD-Objects-in-3.0) objects.

## Working Features
- Query product, customers, coupons, order, refund, product variations.

## Upcoming Features
- Adminstrator mutations. Eg. Creating and deleting products, coupons, orders and refunds
- Public/Customer mutations, Eg. Manipulating the cart and checking out.
View [Roadmap](https://github.com/kidunot89/wp-graphql-woocommerce/projects/1) to see progress... 

## Unit Tests 
Until the documentation is in full effect, it's recommended that a [GraphiQL](https://github.com/graphql/graphiql)-based tool like [WPGraphiQL](https://github.com/wp-graphql/wp-graphiql) be used to view the GraphQL schema, an alternative to this is viewing the unit tests located in `tests/wpunit` directory. Which are constantly updated along with the project. If you're interested in contributing when I begin accepting contribution or simply want to run the tests. Follow the instruction below.

### Prerequisties
- Shell/CMD access
- [Composer](https://getcomposer.org/)
- [WP-CLI](https://wp-cli.org/)

### Setup
1. Make sure all dependencies are install by running `composer install` from the CMD/Terminal in the project directory.
2. Next the copy 5 distributed files with the `.dist` in there filenames. For instance `.env.dist` becomes `.env` and `wpunit.suite.dist.yml` becomes `wpunit.suite.yml`. The distributed files and what their copied names should are as follows.
    - `tests/acceptance.suite.dist.yml` => `tests/acceptance.suite.yml`
    - `tests/functional.suite.dist.yml` => `tests/functional.suite.yml`
    - `tests/wpunit.suite.dist.yml` => `tests/wpunit.suite.yml`
    - `codeception.dist.yml` => `codeception.yml`
    - `.env.dist` => `.env`
3. Next open `.env` and alter to make you usage.
	```
	# Shared
	TEST_DB_NAME="wpgraphql_woocommerce_test"
	TEST_DB_HOST="127.0.0.1"
	TEST_DB_USER="root"
	TEST_DB_PASSWORD=""

	# Install script
	WP_VERSION=latest
	SKIP_DB_CREATE=false
	WP_GRAPHQL_BRANCH=develop

	# Codeception
	WP_ROOT_FOLDER="/tmp/wordpress"
	TEST_SITE_WP_ADMIN_PATH="/wp-admin"
	TEST_SITE_DB_NAME="wpgraphql_woocommerce_test"
	TEST_SITE_DB_HOST="127.0.0.1"
	TEST_SITE_DB_USER="root"
	TEST_SITE_DB_PASSWORD=""
	TEST_SITE_TABLE_PREFIX="wp_"
	TEST_TABLE_PREFIX="wp_"
	TEST_SITE_WP_URL="http://wp.test"
	TEST_SITE_WP_DOMAIN="wp.test"
	TEST_SITE_ADMIN_EMAIL="admin@wp.test"
	TEST_SITE_ADMIN_USERNAME="admin"
	TEST_SITE_ADMIN_PASSWORD="password"
	```
	- `Shared` variables are as the comment implies, variables shared in both the `install-wp-tests` script and the **Codeception** configuration. The variable names should tell you what they mean.
	- `Install script` variables are specified to the `install-wp-tests` script, and most likely won't changed. I've listed their meaning below.
    	- `WP_VERSION` WordPress version used for testing
    	- `SKIP_DB_CREATE` Should database creation be skipped?
    	- `WP_GRAPHQL_BRANCH` The branch in the `WPGraphQL` repository the tests should be run again. Ex. `origin/feature/model-layer`
	- `Codeception` variables are specified to the **Codeception** configuration. View the config files and Codeception's [Docs](https://codeception.com/docs/reference/Configuration#Suite-Configuration) for more info on them.

4. Once you have finish modifying the `.env` file. Run `composer install-wp-tests` from the project directory.
5. Upon success you can begin running the tests.

### Running tests
To run test use the command `vendor/bin/codecept run [suite [test [:test-function]]]`.
If you use the command with at least a `suite` specified, **Codeception** will run all tests, however this is not recommended. Running a suite `vendor/bin/codecept run wpunit` or a test `vendor/bin/codecept run CouponQueriesTest` is recommended. Running a single `test-function` like `vendor/bin/codecept run ProductQueriesTest:testProductsQueryAndWhereArgs` is also possible.

To learn more about the usage of Codeception with WordPress view the [Documentation](https://codeception.com/for/wordpress)  

## HTTP Error 500 :construction: 
If you get HTTP 500 error upon activation or accessing the `endpoint` and have **CMD/Terminal** access with **Composer** installed. 
- Try deleting the `vendor` directory `rm -rf vendor` and regenerating the autoloading files `composer dumpautoload -o` in the `wp-graphql-woocommerce` directory in your WordPress installation's `plugins` directory.
- (Alternative) You can also try delete and cloning the repository again. The latest release should have fixed the issue.

## Support this extension
**WP-GraphQL WooCommerce** :point_right: **[KickStarter](http://kck.st/2G268mx)**

**WPGraphQL** :point_right: **[OpenCollective](http://opencollective.com/wp-graphql)**

**GraphQL-PHP** :point_right: **[OpenCollective](https://opencollective.com/webonyx-graphql-php)**

## Follow
[![alt text](http://i.imgur.com/tXSoThF.png)](https://twitter.com/woographql)
[![alt text](http://i.imgur.com/P3YfQoD.png)](https://www.facebook.com/woographql)
