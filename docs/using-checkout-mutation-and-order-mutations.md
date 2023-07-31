---
title: "Using Checkout Mutation + Order Mutations with WooGraphQL"
description: "Learn how to handle repeat shoppers with existing payment methods using the `checkout` mutation, and side-stepping WooCommerce's management of the session completely by using the `createOrder` mutation."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, checkout mutation, createOrder mutation, session management"
author: "Geoff Taylor"
---

# Using Checkout Mutation and Order Mutations

In this section of the documentation, we will be building upon the knowledge gained from previous sections to explore more advanced functionalities provided by WooGraphQL. Specifically, we will delve into the `checkout` mutation and `createOrder` mutation, and how they can be used to handle repeat shoppers with existing payment methods and manage WooCommerce's session respectively.

The `checkout` mutation allows us to handle repeat shoppers who have existing payment methods. This is particularly useful in creating a seamless shopping experience for your customers, as they do not have to re-enter their payment details every time they shop.

On the other hand, the `createOrder` mutation allows us to bypass WooCommerce's session management completely. This is especially useful in scenarios where you want to have more control over the session management in your application.

In this section, we will provide detailed examples and code snippets to demonstrate how these mutations can be used in a real-world application. We will also discuss potential use cases and best practices for using these mutations.

Before proceeding, it is recommended that you have a good understanding of the basics of WooGraphQL and have gone through the previous sections of this documentation. This will ensure that you have the necessary background knowledge to fully understand the concepts and examples presented in this section.

Let's get started!

## Scenario 1: Handling Checkout for an Existing User

In this scenario, we are dealing with a returning user who has previously made a purchase on our application and has a payment method already stored. The `checkout` mutation will be used to handle this process.

### Using the `checkout` Mutation

The `checkout` mutation allows us to process the checkout for a user with an existing payment method. This mutation takes the `input` argument which should contain the `paymentMethod` field. The `paymentMethod` field should be the ID of the payment method the customer wishes to use. 

Here is an example of how to use the `checkout` mutation:

```graphql
mutation {
  checkout(input: { paymentMethod: "stripe" }) {
    clientMutationId
    order {
      id
      orderId
      total
    }
  }
}
```

In the case where the user wishes to use a new payment method, we have two options:

1. Redirect them to the traditional checkout page.
2. Redirect them to the "add new payment method" page.

The `addPaymentMethodUrl` field, which can be retrieved from the `customer` query, provides the URL to the "add new payment method" page. Here is an example of how to retrieve this URL:

```graphql
query {
  customer {
    addPaymentMethodUrl
  }
}
```

## Scenario 2: Handling Checkout for a New or Existing User with Client-Side Session Management

In this scenario, we are dealing with a new or existing user of our application, but we are not relying on WooCommerce to manage the session. Instead, all things pertaining to the cart are handled client-side until checkout. At this point, we use the `createOrder` mutation to generate the order on the backend.

### Using the `createOrder` Mutation

The `createOrder` mutation allows us to create an order on the backend without relying on WooCommerce's session management. This mutation takes the `input` argument which should contain the `paymentMethod` field and the `lineItems` field. The `lineItems` field should be an array of `LineItemInput` objects, each representing a product in the cart.

Here is an example of how to use the `createOrder` mutation:

```graphql
mutation {
  createOrder(input: {
    paymentMethod: "stripe",
    lineItems: [
      {
        productId: 1,
        quantity: 2
      },
      {
        productId: 3,
        quantity: 1
      }
    ]
  }) {
    clientMutationId
    order {
      id
      orderId
      total
    }
  }
}
```

This scenario is most common for clients that use a payment processor external to WooCommerce. By handling the cart client-side and only using WooCommerce for product data and order management, we can provide a seamless checkout experience for our users.

In the next sections, we will delve deeper into how to utilize order data, customer data, and various product data types to further enhance our application.
