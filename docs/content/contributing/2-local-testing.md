---
title: "Local Testing"
metaTitle: "Local Testing Guide | WooGraphQL Docs | AxisTaylor"
metaDescription: "An extensive guide on local testing w/ WooGraphQL."
---

When extending the **WPGraphQL** API can be tricky at times, even more so when dealing with a massive plugin like WooCommerce.
Don't let this discourage you though, it's possible simplify the WPGraphQL development with the use of **Test-Driven Development (TDD)**. Now if you've ever been told anything about **TDD**, it's probably that **TDD** doesn't always fit everyone's development process. Nonetheless, the argument I'm trying to make is here is that using **TDD** and follow this guide, you'll learn how to make proper changes to the **WPGraphQL/WooGraphQL** schema as well as read code that you know works regardless of where the GraphQL request came from.

# Codeception & the wp-browser module
**WPGraphQL/WooGraphQL**'s use **[Codeception](https://codeception.com/)** and the **[wp-browser](https://wpbrowser.wptestkit.dev/)** Codeception module created by [Luca Tumedei](https://www.theaveragedev.com/) for running the automated test suite. We'll be using Codeception scaffolding to generate all the tedious test code, but this will not be an in-depth guide on either of these libraries. It's not required to process with this tutorial, but it's highly recommended that after finishing this tutorial you take a look at the documentation for both.
- **[Codeception](https://codeception.com/docs/01-Introduction)**
- **[wp-browser](https://wpbrowser.wptestkit.dev/)**

# Setting up WordPress for testing.
Before we can begin testing we need a local WordPress installation. If you already have a local installation of WordPress for development, you can use that if you wish skip to **[Configuring Codeception Environmental](#Configuring Codeception Environmental)**. If you don't have a local installation or simply don't want to use your local installation, you can use the **[WP-CLI](https://wp-cli.org/)** plugin test environment.

## Prerequisties
Have install **PHP**, **MySQL** or **PostgreSQL**, **Composer**, and **[WP-CLI](https://wp-cli.org/)** installed as well as terminal or shell access.
1. Start by cloning **[WooGraphQL](https://github.com/wp-graphql/wp-graphql-woocommerce)**.
2. Open your terminal .
3. Copy the `.env.dist` to `.env` by execute the following in your terminal in the **WooGraphQL** root directory.
```
cp .env.dist .env
```
4. Open the .env and update the highlighted environmental variables to match your machine setup.
![.env example](2-local-testing/image-01.png)
5. Last thing to do is run the WordPress testing environment install script in the terminal
```
composer install-wp-tests
```

This will create and configure a WordPress installation in a temporary directory for the purpose of testing.

# Setting up Codeception.
Now that we have setup our testing environment lets run the tests. To do this will need to install the **Codeception** and the rest of our **devDependencies**
1. First run `composer install` in the terminal.
2. Next copy the `codeception.dist.yml` to `codeception.yml`
```
cp codeception.dist.yml codeception.yml
```
3. Open `codeception.yml` and make the following changes.
![codeception.yml params config](2-local-testing/image-02.png)
![codeception.yml WPLoader config](2-local-testing/image-03.png)

Now you all set to run the tests.

# Running the tests.
Now we're ready to get started with testing. There is a small issue you may have with our testing environment. The WordPress installation we created doesn't support **end-to-end (*e2e*)** testing, however this won't be a problem. **WPGraphQL** is an API and most of the time you can get away with just ensuring that your query is works, and **WPGraphQL** provides a few functions that will always us to do just that.

Well let's get started by running all the unit tests. Back in your terminal run the following
```
vendor/bin/codecept run wpunit
```
If everything is how it should be you should get all passing tests.
![WPUnit test results](2-local-testing/image-04.png)

# Creating a new WPUnit test file

## setUp/tearDown functions

## Writing your first test