<img src="./logo.svg" width="250px">

# WPGraphQL WooCommerce
[![Build Status](https://travis-ci.org/wp-graphql/wp-graphql-woocommerce.svg?branch=develop)](https://travis-ci.org/wp-graphql/wp-graphql-woocommerce) [![Coverage Status](https://coveralls.io/repos/github/wp-graphql/wp-graphql-woocommerce/badge.svg?branch=develop)](https://coveralls.io/github/wp-graphql/wp-graphql-woocommerce?branch=develop)

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
- Adminstrator mutations. Eg. Creating and deleting products, coupons, and refunds.

## Playground
Feel free to test out the extension using the [playground](https://docs.wpgraphql.com/extensions/wpgraphql-woocommerce/). The playground allows you to execute queries and mutations, as well as view the schema.

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
	SKIP_DB_CREATE=false

	# Codeception
	TEST_SITE_WP_ADMIN_PATH="/wp-admin"
	TEST_SITE_DB_NAME="wpgraphql_woocommerce_test"
	TEST_SITE_DB_HOST="127.0.0.1"
	TEST_SITE_DB_USER="root"
	TEST_SITE_DB_PASSWORD=""
	TEST_SITE_TABLE_PREFIX="wp_"
	TEST_TABLE_PREFIX="wp_"
	TEST_SITE_WP_URL="http://localhost"
	TEST_SITE_WP_DOMAIN="localhost"
	TEST_SITE_ADMIN_EMAIL="admin@localhost"
	TEST_SITE_ADMIN_USERNAME="admin"
	TEST_SITE_ADMIN_PASSWORD="password"
	```
	- `Shared` variables are as the comment implies, variables shared in both the `install-wp-tests` script and the **Codeception** configuration. The variable names should tell you what they mean.
	- `Install script` variables are specified to the `install-wp-tests` script, and most likely won't changed. I've listed their meaning below.
    	- `WP_VERSION` WordPress version used for testing
    	- `SKIP_DB_CREATE` Should database creation be skipped?
	- `Codeception` variables are specified to the **Codeception** configuration. View the config files and Codeception's [Docs](https://codeception.com/docs/reference/Configuration#Suite-Configuration) for more info on them.

4. Once you have finish modifying the `.env` file. Run `composer install-wp-tests` from the project directory.
5. Upon success you can begin running the tests.

### Running tests
To run test use the command `vendor/bin/codecept run [suite [test [:test-function]]]`.
If you use the command with at least a `suite` specified, **Codeception** will run all tests, however this is not recommended. Running a suite `vendor/bin/codecept run wpunit` or a test `vendor/bin/codecept run CouponQueriesTest` is recommended. Running a single `test-function` like `vendor/bin/codecept run ProductQueriesTest:testProductsQueryAndWhereArgs` is also possible.

To learn more about the usage of Codeception with WordPress view the [Documentation](https://codeception.com/for/wordpress)  

## Functional and Acceptance Tests (Docker/Docker-Compose required)
It's possible to run functional and acceptance tests, but is very limited at the moment. The script docker entrypoint script runs all three suites (acceptance, functional, and wpunit) at once. This will change eventually, however as of right now, this is the limitation.

### Running tests
Even though the two suite use a Docker environment to run, the docker environment relies on a few environmental variables defined in `.env` and a volume source provided by the test install script.
0. Ensure that you can copy `.env.dist` to `.env`.
1. First you must run `composer install-wp-tests` to ensure the required dependencies are available.
2. Next run `docker-compose build` from the terminal in the project root directory, to build the docker image for test environment.
3. And now you're ready to run the tests. Running `docker-compose run --rm wpbrowser` does just that.
You can rerun the tests by simply repeating step 3.

## HTTP Error 500 :construction: 
If you get HTTP 500 error upon activation or accessing the `endpoint` and have **CMD/Terminal** access with **Composer** installed. 
- Try deleting the `vendor` directory `rm -rf vendor` and regenerating the autoloading files `composer dumpautoload -o` in the `wp-graphql-woocommerce` directory in your WordPress installation's `plugins` directory.
- (Alternative) You can also try delete and cloning the repository again. The latest release should have fixed the issue.

## Support this extension
**WPGraphQL** :point_right: **[OpenCollective](http://opencollective.com/wp-graphql)**

**GraphQL-PHP** :point_right: **[OpenCollective](https://opencollective.com/webonyx-graphql-php)**

## Follow
[![alt text](http://i.imgur.com/tXSoThF.png)](https://twitter.com/woographql)
[![alt text](http://i.imgur.com/P3YfQoD.png)](https://www.facebook.com/woographql)
