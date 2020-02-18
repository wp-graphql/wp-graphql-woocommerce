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

# Setting up WP installation for testing.

# Installing Composer dependencies

# WPUnit Tests.

# Creating a new test

# setUp/tearDown functions

# Write your test