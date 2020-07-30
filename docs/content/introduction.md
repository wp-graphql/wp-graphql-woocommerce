---
title: "Introduction"
metaTitle: "Introduction | WooGraphQL Docs | AxisTaylor"
metaDescription: "What is WooGraphQL and why use it?"
---

import Link from '../src/components/link'

# What is WooGraphQL?

WPGraphQL WooCommerce, WooGraphQL for short, is a WPGraphQL/WooCommerce extension that exposes WooCommerce along with it’s functionality to the GraphQL server created by WPGraphQL, making it possible to create better written and performing client-side application.

```
query {
  products(first: 10) {
    edges {
      cursor
      node {
        id
        name
        averageRating
        ... on SimpleProduct {
          id
          name
          price
          salePrice
          regularPrice
        }
        ... on VariableProduct {
          id
          name
          price
          salePrice
          regularPrice
        }
        type
      }
    }
  }
}
```
This means the data for products, orders, coupons, items in the shopping cart, and much more can be retrieve through the use of queries, like in the example above. Retrieving only the data you require, making request speedy and efficient

```
mutation {
  addToCart(input: {clientMutationId: "someId" productId: 22, quantity: 2}) {
    clientMutationId
    cartItem {
      key
      product {
        id
        productId
        name
      }
      quantity
      subtotal
      total
    }
    cart {
      contents(first: 10) {
        edges {
          cursor
          node {
            key
            quantity
            subtotal
            total
          }
        }
      }
      subtotal
      total
    }
  }
}
```
You can also add items to cart, create orders, and more, using the mutations that WooGraphQL provides.

# Why Use WooGraphQL?

[benefits/advantage image]
WooGraphQL has neither the team or the development hours that WooCommerce (WC) REST does, but there are advantages in both application performance and design.

## GraphQL vs REST
- The shapes of the data object returned by WC REST are limited and more times than not include data that you don’t require. GraphQL only retrieves the data you want making requests speedy and efficient. 
- WC REST relies heavily on page-based pagination as do most WordPress REST APIs. If the store has a large amount of products, database requests with take much longer to process. WooGraphQL manipulates WordPress’ core querying functions implement [cursor-based pagination](https://dev.to/jackmarchant/offset-and-cursor-pagination-explained-b89), which handles large amount of products more efficiently.

## It comes with a Cart.
- WC REST doesn’t include any cart endpoints for manipulating the cart, meaning a cart has be implemented client-side, which is no small task. Cart-specific utilities are provided so you can rely on WooCommerce’s built-in cart to continue managing the end-user cart session data.
- Although WC REST doesn’t provide any support for managing the user session alternative options for doing this task do exist, however their usage can be limited to applications within the same origin as the WordPress installation. This is due to WooCommerce using cookies to store user session token. WooGraphQL provides a utility that changes this behavior during GraphQL request by passing the token to HTTP Header to be cached client-side and used like a HTTP Authorization header.

# Getting Started

WPGraphQL WooCommerce (WooGraphQL) is a both WPGraphQL extension and a WooCommerce extension so both plugin are required for it’s use.

1. Install and activate **[WPGraphQL](https://wpgraphql.com)** and **[WooCommerce](https://woocommerce.com)**
2. Clone or download the most stable **[release](https://github.com/wp-graphql/wp-graphql-woocommerce/releases)**  from the **[repository](https://github.com/wp-graphql/wp-graphql-woocommerce)** into your WordPress plugin directory & activate the plugin.
3. (Optional, but highly recommend) Install and activate **[WPGraphiQL](https://github.com/wp-graphql/wp-graphiql)**.
4. Set your GraphQL client endpoint to the GraphQL endpoint of your site. Typically, this is `your-store.domain/graphql`.

## What next?
- If you’re looking to develop a client-side application, take a look at the <Link to="/guides">Guides</Link>. They walk through the process of developing some common components in a React /Apollo application using data provided by WooGraphQL.
- If you wish to customize the WooGraphQL schema to fit your particular store, try the <Link to="/contributing">Contributing</Link> section. You can find tools/utilities for setting a development environment and testing locally and with Docker, as well as tips for extending and change the behavior of the WooGraphQL schema.

> ### A Quick Tip
> Use **[WPGraphiQL](https://github.com/wp-graphql/wp-graphiql)**  to build your queries during development. WPGraphiQL come  equipped with an Explorer component for quick query building and a Schema Viewer component so individual types’ information can be searched.
