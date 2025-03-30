## Contribute to WPGraphQL for WooCommerce

WPGraphQL for WooCommerce (WooGraphQL) welcomes community contributions, bug reports and other constructive feedback.

When contributing please ensure you follow the guidelines below so that we can keep on top of things.

## Getting Started

* __Do not report potential security vulnerabilities here. Email them privately to our security team at 
[support@axistaylor.com](mailto:support@axistaylor.com)__
* Before submitting a ticket, please be sure to replicate the behavior with no other plugins active and on a base theme like Twenty Seventeen.
* Submit a ticket for your issue, assuming one does not already exist.
  * Raise it on our [Issue Tracker](https://github.com/wp-graphql/wp-graphql-woocommerce/issues)
  * Clearly describe the issue including steps to reproduce the bug.
  * Make sure you fill in the earliest version that you know has the issue as well as the version of WordPress you're using.

## Making Changes

* Fork the repository on GitHub
* Make the changes to your forked repository
  * Ensure you stick to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
* When committing, reference your issue (if present) and include a note about the fix
* If possible, and if applicable, please also add/update unit tests for your changes
* Push the changes to your fork and submit a pull request to the 'develop' branch of this repository

### Setting up an environment to run the WPUnit tests

1. Create a `.env` file from the provided `.env.testing` and update the following section.

    ```env
    # WordPress database configurations
    DB_NAME=wordpress
    DB_HOST=app_db
    DB_PORT=3306
    DB_USER=wordpress
    DB_PASSWORD=password
    ROOT_PASSWORD=password
    WP_TABLE_PREFIX=wp_
    SKIP_DB_CREATE=true
    SKIP_WP_SETUP=true

    ```

    The variable should be point to a local instance of MySQL and `SKIP_DB_CREATE` and `SKIP_WP_SETUP` should be set to `false`.
2. Run `composer installTestEnv` from the terminal in the plugin root directory. This will run the setup script and install all the required dependencies.
3. Run the tests by running `vendor/bin/codecept run wpunit` from the root directory. To learn about the libraries used to write the tests see [Codeception](https://codeception.com/), [WPBrowser](https://wpbrowser.wptestkit.dev/modules/WPBrowser/), and [WPGraphQL TestCase](https://github.com/wp-graphql/wp-graphql-testcase)

### Setting up a Docker environment to run the E2E tests

1. Run `composer installTestEnv` from the terminal in the plugin root directory to install all the required dependencies.
2. Next run `composer dRunApp`. This will build and start the docker network at http://localhost:8080 based off the configuration found in the `docker-compose.yml` in the project root.
3. Last run the test by running `composer dRunTest`. This runs the test suite by utilizing the `docker exec` command to run `vendor/bin/codecept run` from the project root within the docker container for wordpress site. You can pass further parameter to this comment by using the `FILTER` environment variable like so `FILTER="acceptance NewCustomerCheckingOutCept --debug" composer dRunTest`.

> **NOTE:** If you have no interest in the generated docker environment outside of automated test you can simply the process by just running the `dRunTestStandalone` command after the `installTestEnv` command combining steps 2 & 3, `composer dRunTestStandalone`. The command also reads the `FILTER` variables as well to so filter is possible `FILTER="acceptance NewCustomerCheckingOutCept --debug" composer dRunTestStandalone`.


## Code Documentation

* We strive for full doc coverage and follow the standards set by phpDoc
* Please make sure that every function is documented so that when we update our API Documentation things don't go awry!
    * If you're adding/editing a function in a class, make sure to add `@access {private|public|protected}`
* Finally, please use tabs and not spaces.

At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.

> **NOTE:** This CONTRIBUTING.md file was forked from [WPGraphQL](https://github.com/wp-graphql/wp-graphql/blob/master/CONTRIBUTING.md)