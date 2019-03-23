<img src="./logo.svg" width="250px">

# WPGraphQL WooCommerce
[![Build Status](https://travis-ci.org/kidunot89/wp-graphql-woocommerce.svg?branch=master)](https://travis-ci.org/kidunot89/wp-graphql-woocommerce) [![Coverage Status](https://coveralls.io/repos/github/kidunot89/wp-graphql-woocommerce/badge.svg?branch=master)](https://coveralls.io/github/kidunot89/wp-graphql-woocommerce?branch=master)

## Note: This plugin is in early stages and is not quite ready for use.

## Quick Install
1. Install & activate [WooCommerce](https://woocommerce.com/)
2. Install & activate [WPGraphQL](https://www.wpgraphql.com/)
3. Clone this repository into your WordPress plugin directory & activate the **WP GraphQL WooCommerce** plugin

## Documentation
Coming soon...

## Unit Testing and Code Coverage 
Before anything is merged into the WPGraphQL code base it must pass all tests and have 100% code coverage. 
Travis-CI and Coveralls will check this when you create a pull request to the WPGraphQL repo. 
However, before that happens, you should ensure all of these requirements are met locally. 
The following will help you set up both testing and code coverage in your local environment.

### Prerequisites
To run unit tests and code coverage during development you'll need the following:

* [Composer](https://getcomposer.org/doc/00-intro.md)
    * [php-coveralls](https://github.com/php-coveralls/php-coveralls)
        * `composer global require php-coveralls/php-coveralls`
* [Xdebug](https://xdebug.org/docs/install)

### Test Database
In order for tests to run, you need MySQL setup locally. The test suite will need 2 databases for testing. 
One named `wpgraphql_serve` and the other you can name yourself. 
You can keep these databases around if you like and the test suite will use the existing databases, or you can delete them when you're done testing and the test suite will 
re-install them as needed the next time you run the script to install the tests.

*NOTE*: You'll want the test database to be a true test database, not a database with valuable, existing information. 
The tests will create new data and clear out data, and you don't want to cause issues with a database you're actually using for projects.

### Installing the Test Suite
To install the test suite/test databases, from the root of the plugin directory, in the command line run: 

`bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]`

For example: 

`bin/install-wp-tests.sh wpgraphql_woocommerce_test root '' 127.0.0.1 latest`

*DEBUGGING*: If you have run this command before in another branch you may already have a local copy of WordPress downloaded in your `/private/tmp` directory. 
If this is the case, please remove it and then run the install script again. Without removing this you may receive an error when running phpunit.

#### Local Environment Configuration for Codeception Tests

You may have different local environment configuration than what Travis CI has to run the tests, such as database username/password.


In the `/tests` directory you will find `*.suite.dist.yml` config files for each of the codeception test suites. 

You can copy those files and remove the `.dist` from the filename, and that file will be loaded locally _before_ the `.dist` file.

Do the same for the `.env.dist` in the root directory and copy the file as `.env`.

For example, if you wanted to update the `dbName` or `dbPassword` for your local tests, you could copy `wpunit.suite.dist.yml` to `wpunit.suite.yml` and update the `dbName` or `dbPassword` value to reflect your local database and password.

This file is .gitignored, so it will remain in your local environment but will not be added to the repo when you submit pull requests.

### Running the Tests
The tests are built on top of the Codeception testing framework. 

To run the tests, after you've installed the test suite, as described above, you need to also install the `wp-browser`. 

*@todo*: Make this easier than running all these steps, but for now this is what we've got to do.
Perhaps someone who's more of a Composer expert could lend some advise?:

- `rm -rf composer.lock vendor` to remove all composer dependencies and the composer lock file
- `composer require lucatume/wp-browser --dev` to install the Codeception WordPress deps
- `vendor/bin/codecept run` to run all the codeception tests
    - You can specify which tests to run like: 
        - `vendor/bin/codecept run wpunit`
        - `vendor/bin/codecept run functional`
        - `vendor/bin/codecept run acceptance`
    - If you're working on a class, or with a specific test, you can run that class/test with:
        - `vendor/bin/codecept run tests/wpunit/NodesTest.php`
        - `vendor/bin/codecept run tests/wpunit/NodesTest.php:testPluginNodeQuery`
