---
title: "WPGraphQL for WooCommerce"
description: "Comprehensive documentation for WPGraphQL for WooCommerce (WooGraphQL) - the WPGraphQL extension that integrates WooCommerce with GraphQL for headless e-commerce solutions."
author: "Geoff Taylor"
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, headless, e-commerce"
---

<p align="center">
  <img src="../logo.svg" alt="WPGraphQL for WooCommerce" width="200" />
</p>

# WPGraphQL for WooCommerce

WPGraphQL for WooCommerce (WooGraphQL) is a free, open-source WordPress plugin that extends the WPGraphQL plugin, allowing you to access WooCommerce data through GraphQL queries and mutations.

## Table of Contents

### Getting Started

- [Installation](./installation.md)
- [Settings](./settings.md)
- [Configuring GraphQL Client For the User Session](./configuring-graphql-client-for-user-session.md)
- [Handling User Authentication](./handling-user-authentication.md)

### WooGraphQL

- [Routing by URI](./routing-by-uri.md)
- [Using Product Data](./using-product-data.md)
- [Creating Session Provider and using Cart Mutations](./handling-user-session-and-using-cart-mutations.md)
- [Using Cart Data](./using-cart-data.md)
- [Harmonizing with WordPress](./harmonizing-with-wordpress.md)
- [Using Checkout Mutation + Order Mutations](./using-checkout-mutation-and-order-mutations.md)
- [Using Order Data](./using-order-data.md)
- [Using Customer Data + Mutations](./using-customer-data-and-mutations.md)

### WooGraphQL Pro

For premium WooCommerce extension support (Subscriptions, Composite Products, Bundles, Add-ons), see the [WooGraphQL Pro Documentation](https://woographql.com/docs/woographql-pro).

### Contributing

- [Testing Quick-Start Guide](./testing-quick-start.md)
- [Local CLI Testing](./local-testing.md)
- [Development with Docker](./development-w-docker.md)
- [CLI Testing with Docker](./testing-w-docker.md)

## Introduction

WooGraphQL brings the power of GraphQL to WooCommerce, enabling developers to build modern, performant headless e-commerce applications. With WooGraphQL, you can:

- Query products, categories, tags, and attributes
- Manage shopping carts with session-based mutations
- Handle user authentication and customer accounts
- Process checkouts and manage orders
- Access WooCommerce settings and configurations

Whether you're building a React, Vue, or any other frontend application, WooGraphQL provides a flexible and efficient API to interact with your WooCommerce store.

### Key Features

- **Complete WooCommerce Integration**: Access all WooCommerce data types including products, orders, customers, coupons, and more
- **Session Management**: Built-in support for guest and authenticated user sessions
- **Cart Operations**: Full cart functionality with add, remove, update, and clear operations
- **Checkout Process**: Complete checkout mutation with payment gateway integration
- **Extensible**: Hooks and filters to customize the schema and responses

### Requirements

- WordPress 5.6+
- WooCommerce 6.0+
- WPGraphQL 1.14+
- PHP 7.4+

Get started by following the [Installation Guide](./installation.md).
