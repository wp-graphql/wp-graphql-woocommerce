<img src="./logo.svg" width="250px">

# WPGraphQL WooCommerce (WooGraphQL)
[![Build Status](https://travis-ci.org/wp-graphql/wp-graphql-woocommerce.svg?branch=develop)](https://travis-ci.org/wp-graphql/wp-graphql-woocommerce) [![Coverage Status](https://coveralls.io/repos/github/wp-graphql/wp-graphql-woocommerce/badge.svg?branch=develop)](https://coveralls.io/github/wp-graphql/wp-graphql-woocommerce?branch=develop)

## Quick Install
1. Install & activate [WooCommerce](https://woocommerce.com/)
2. Install & activate [WPGraphQL](https://www.wpgraphql.com/)
3. (Optional) Install & activate [WPGraphQL-JWT-Authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication) to add a `login` mutation that returns a JSON Web Token.
4. Clone or download the zip of this repository into your WordPress plugin directory & activate the **WP GraphQL WooCommerce** plugin

## What does this plugin do?
It adds WooCommerce functionality to the WPGraphQL schema using WooCommerce's [CRUD](https://github.com/woocommerce/woocommerce/wiki/CRUD-Objects-in-3.0) objects.

## Features
- Query product, customers, coupons, order, refund, product variations with complex filtering options.
- Add items to cart and process user store session using HTTP header defined by WooGraphQL's built-in session handler
- Create/process user checkout with the `checkout` mutation.

## Future Features
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
2. Next the copy 2 distributed files with the `.dist` in there filenames. For instance `.env.dist` becomes `.env` and `wpunit.suite.dist.yml` becomes `wpunit.suite.yml`. The distributed files and what their copied names should are as follows.
    - `codeception.dist.yml` => `codeception.yml`
    - `.env.dist` => `.env`
3. Next open `.env` and alter to make you usage.
	```
	# docker ENV variables
	DB_NAME=wordpress
	DB_HOST=app_db
	DB_USER=wordpress
	DB_PASSWORD=wordpress
	WP_TABLE_PREFIX=wp_
	WP_URL=http://localhost
	WP_DOMAIN=localhost
	ADMIN_EMAIL=admin@example.com
	ADMIN_USERNAME=admin
	ADMIN_PASSWORD=password
	ADMIN_PATH=/wp-admin

	# local codeception/install-wp-tests ENV variables
	TEST_DB_NAME=woographql_tests
	TEST_DB_HOST=127.0.0.1
	TEST_DB_USER=wordpress
	TEST_DB_PASSWORD=wordpress
	TEST_WP_TABLE_PREFIX=wp_

	# install-wp-tests ENV variables
	SKIP_DB_CREATE=false
	TEST_WP_ROOT_FOLDER=/tmp/wordpress
	TEST_ADMIN_EMAIL=admin@wp.test

	# codeception ENV variables
	TESTS_DIR=tests
	TESTS_OUTPUT=tests/_output
	TESTS_DATA=tests/_data
	TESTS_SUPPORT=tests/_support
	TESTS_ENVS=tests/_envs
	```
	- `docker ENV variables`: variables defined for use in the Docker/Docker-Compose setups. These are also used in `codeception.dist.yml` for testing within a Docker container. It's recommend that this file be left unchanged and a `codeception.yml` be created for local codeception unit testing.
	- `local codeception/install-wp-tests ENV variables`: variable defined for use with codeception testing w/o docker and the `install-wp-tests` script in the `bin` directory. As mentioned above a `codeception.yml` should be created from `codeception.dist.yml` and the variables in the `WPLoader` config should be set accordingly.
	- `install-wp-tests ENV variables`: variables specific to the `install-wp-tests` script. The script can be run using `composer install-wp-tests` in the terminal from project directory.
	- `codeception ENV variables`: variables used by codeception. This includes within the docker container as well.

4. Once you have finish modifying the `.env` file. Run `composer install-wp-tests` from the project directory.
5. Upon success you can begin running the tests.

### Running tests
To run test use the command `vendor/bin/codecept run [suite [test [:test-function]]]`.
If you use the command with at least a `suite` specified, **Codeception** will run all tests, however this is not recommended. Running a suite `vendor/bin/codecept run wpunit` or a test `vendor/bin/codecept run CouponQueriesTest` is recommended. Running a single `test-function` like `vendor/bin/codecept run ProductQueriesTest:testProductsQueryAndWhereArgs` is also possible.

To learn more about the usage of Codeception with WordPress view the [Documentation](https://codeception.com/for/wordpress)  

## Functional and Acceptance Tests (Docker/Docker-Compose required)
It's possible to run functional and acceptance tests, but is very limited at the moment. The script docker entrypoint script runs all three suites (acceptance, functional, and wpunit) at once. This will change eventually, however as of right now, this is the limitation.

### Running tests
Even though the two suite use a Docker environment to run, the docker environment relies on a few environmental variables defined in `.env.dist` and a volume source provided by the test install script and the configuration `codeception.dist.yml`. If you have created a `codeception.yml` file ensure it is identical to `codeception.dist.yml` or delete it.
Run the following in the terminal to run all three suites. Isolating specific suites should be simple to figure out.
```
docker-compose run --rm -e SUITE=acceptance;wpunit;functional -e DEBUG=1 -e COVERAGE=1 testing --scale app=0
```
- The `COVERAGE`, and `DEBUG` vars are optional flags for toggle codecoverage and debug output.
- `--scale app=0` ensures that the service running a local app doesn't create any instances. It must be added or a collision with `mysql` will occur. More on this service in the next section

## Using docker-compose to run a local installation for live testing.
This is rather simple just like with testing using docker ensure that `env.dist` and `codeception.dist.yml` are untouched.
1. Run `docker-compose up --scale testing=0 app`
2. wait for `app_1      | Success: Exported to '/var/www/html/wp-content/plugins/wp-graphql-woocommerce/tests/_data/dump.sql'.` to print to the terminal.
3. navigate to `http://localhost:8091`. And that's it.
You can view the configuration for the installation in the `docker-compose.yml`.
**NOTE: if you get redirected to `http://localhost` run `docker-compose down` to remove any existing containers related to the project, then re-run Step 1.**
- For more information about the docker-image uses in the service, it's on [Docker Hub](https://hub.docker.com/r/kidunot89/woographql-app). 


## HTTP Error 500 :construction: 
If you get HTTP 500 error upon activation or accessing the `endpoint` and have **CMD/Terminal** access with **Composer** installed. 
- Try deleting the `vendor` directory `rm -rf vendor` and regenerating the autoloading files `composer dumpautoload -o` in the `wp-graphql-woocommerce` directory in your WordPress installation's `plugins` directory.
- (Alternative) You can also try delete and cloning the repository again. The latest release should have fixed the issue.

## Support this extension
**@kidunot89** *(WooGraphQL Creator/Developer)* :point_right: **[Github Sponsors](https://github.com/sponsors/kidunot89)**

**WPGraphQL** :point_right: **[OpenCollective](http://opencollective.com/wp-graphql)**

**GraphQL-PHP** :point_right: **[OpenCollective](https://opencollective.com/webonyx-graphql-php)**

## Follow
[![alt text](http://i.imgur.com/tXSoThF.png)](https://twitter.com/woographql)
[![alt text](http://i.imgur.com/P3YfQoD.png)](https://www.facebook.com/woographql)
