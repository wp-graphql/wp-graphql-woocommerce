---
title: "Testing (Quick-Start Guide)"
metaTitle: "WooGraphQL Testing Quick-Start Guide | WooGraphQL Docs | AxisTaylor"
metaDescription: "A simple guide to get started testing with WooGraphQL."
---

## Unit Tests

Until the documentation is in full effect, it's recommended that a [GraphiQL](https://github.com/graphql/graphiql)-based tool like **WPGraphQL IDE** be used to view the GraphQL schema, an alternative to this is viewing the unit tests located in `tests/wpunit` directory. Which are constantly updated along with the project. If you're interested in contributing when I begin accepting contribution or simply want to run the tests. Follow the instruction below.

### Prerequisties

- Shell/CMD access
- [Composer](https://getcomposer.org/)
- [WP-CLI](https://wp-cli.org/)
- [PHP](https://php.net)
### Setup

1. First copy  the `.env.testing` file as `.env`
2. Next open `.env` and alter to make you usage.
  ```bash
	# WordPress admin configurations
  ADMIN_EMAIL=admin@woographql.local
  ADMIN_USERNAME=admin
  ADMIN_PASSWORD=password
  ADMIN_PATH=/wp-admin

  # WordPress local configurations
  WP_CORE_DIR=local/public
  PLUGINS_DIR=local/public/wp-content/plugins
  MUPLUGINS_DIR=local/public/wp-content/mu-plugins
  THEMES_DIR=local/public/wp-content/themes

  # WordPress database configurations
  DB_NAME=wordpress
  DB_HOST=app_db
  DB_PORT=3306
  DB_USER=wordpress
  DB_PASSWORD=password
  ROOT_PASSWORD=password
  WP_TABLE_PREFIX=wp_
  SKIP_DB_CREATE=false

  # Extra variables/constants.
  GRAPHQL_JWT_AUTH_SECRET_KEY=testingtesting123
  STRIPE_API_PUBLISHABLE_KEY=""
  STRIPE_API_SECRET_KEY=""
  ```
  - Typical you should only have to change the **WordPress database configurations** to use local testing.
3. Once you have finished modifying the `.env` file. Run `composer install-test-env` from the project directory. This will install WordPress + Codeception w/ WPBrowser, as well as setup the database if needed.
4. Upon success you can begin running the tests.

### Running tests locally

To run the tests use the command `vendor/bin/codecept run [suite [test [:test-function]]]`.
If you use the command with at least a `suite` specified, **Codeception** will run all tests, however this is not recommended. Running a suite `vendor/bin/codecept run wpunit` or a test `vendor/bin/codecept run CouponQueriesTest` is recommended. Running a single `test-function` like `vendor/bin/codecept run ProductQueriesTest:testProductsQueryAndWhereArgs` is also possible.

To learn more about the usage of Codeception with WordPress view the [Documentation](https://codeception.com/for/wordpress)

## Functional and Acceptance Tests (Server or Docker required)

Running functional and acceptance tests requires that WordPress installation being used for testing be accessible thru some kind of **URL/Address** and then setting that **URL/Address** in the **codeception.dist.yml** file or in the **.env** file as **WORDPRESS_DOMAIN/WORDPRESS_URL**.

_Note: The **codeception.dist.yml** should be left unchanged and a copy named **codeception.yml** should be used._

Running the `install-test-env` alone does not configure a server to point at the Wordpress installation it creates, you're two options for doing this.

1. Configure an Apache or Nginx server block and point it at the WordPress installation created by the `install-test-env` script. This isn't a very flexible or quick method.
2. Use the Docker configurations in the project to push the installation into a docker network and expose it's docker container's IP as the **URL/Address**. This is the recommended option if **Docker/Docker-Compose** is available to you. The project includes some simple composer scripts that enable you to run all at once or filter specific tests for speed, test isolation, or **XDebug Stepping Debug**.

The composer scripts for using dockers are.

- `docker-build` builds this the Docker Image for the `woographql/wordpress` container that will house the WordPress installation and tests.
- `docker-run-app` spins up the docker network. This can be used for live debug as well as cli test. The WordPress installation will be accessible from a URL provided in the `woographql/wordpress` container log in you terminal.
- `docker-run-testing-db` add a cloned MySQL instance of the one created in `docker-run-app` for testing, this is to be run after the docker network as been created using `docker-run-app` and before the tests are run inside of the `woographql/wordpress` container. _Note: This should not be used directly. See `docker-run-test` below._
- `docker-set-main-db` configures the wp-config.php of wordpress installation to point at the main MySQL container. This is for returning to live development using the browser after running the tests in cli.
- `docker-set-testing-db` is essentially the same `docker-set-main-db` just for the testing database. However this script stalls and looks for the testing database to be available. _Note: This should not be used directly. See `docker-run-test` below._
- `docker-run-test` is the primary tester script and the script you'll probably call the most. This script essentially just runs `codecept run $FILTER` in the `woographql/wordpress`, however before that it runs `docker-run-testing-db`, and `docker-set-testing-db` to ensure all the need players are set. `FILTER` is a shell variable that can be what parameter you what to pass to the run command except the `--env` or `--no-exit` options. For usage see this example. `FILTER="wpunit CartMutationsTest:testAddToCartMutationWithProduct --debug" composer docker-run-test`
- `docker-run-test-standalone` setups the docker network, run all the tests, and pulls down the network. Primarily for CI.

### Running tests with Docker/Docker-Compose

Running the tests in rather simple, but you may need two terminal windows depending on you method.

- _(Requires two terminals in the project root directory)_ In one terminal run `composer docker-build && composer docker-run-app`. Wait until you see the log `testable_app_x_xxxxxxxxxxxx | WordPress app located at http://xxx.xxx.xxx.xxx`, then you're ready to run the tests and can leave this terminal running and move to your second one. In your second terminal you can run the by executing `composer run-test` This will run all the tests at once with no options passed to the `codecept run` command. This can be alter with `FILTER` variable mention in the last section. The first time you run this command it will be delayed due to having to setup the test database. Also one last thing to note is if you when to switch to live development/debugging in the browser have running the test this way. Run `composer docker-set-main-db` before you do.
- This other method is even alot more streamline and only needs one command `composer docker-run-test-standalone`. See description of what it does in the last section.

### Using docker-compose to run a local installation for live testing

This is rather simple just like with testing using docker ensure that `env.dist` and `codeception.dist.yml` are untouched.

1. Run `composer docker-build && composer docker-run-app`.
2. Wait until you see the log `testable_app_x_xxxxxxxxxxxx | WordPress app located at http://xxx.xxx.xxx.xxx`.
3. Navigate to the provided address.
