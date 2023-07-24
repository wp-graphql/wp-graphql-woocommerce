---
title: "Using Subscription Data + Mutations"
description: ""
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, checkout, headless, shopping experience"
author: "Geoff Taylor"
---

# Using Subscription Data + Mutations

This section of the documentation will focus on how to use the Subscription functionality provided by WooGraphQL Pro. We will build upon the code written in `Using Product Data` and `Routing by URI` by rewriting the `ProductListing` and `SingleProduct` components to support `SubscriptionProduct` types.

Before we start, ensure that the `changeSubPaymentMethodUrl`, `renewSubPaymentMethodUrl`, and `Enable Subscriptions` options from the WooGraphQL settings page are checked and enabled. These settings will allow us to handle subscription renewals, payment method changes, and enable subscription functionality respectively.

## Understanding SubscriptionProduct Type

The `SubscriptionProduct` type represents a product that can be purchased on a recurring basis. It includes fields for the subscription price, interval, length, and sign-up fee, among others. Here is an example of how to query for a subscription product:

```javascript
const SUBSCRIPTION_PRODUCT_QUERY = gql`
  query SubscriptionProduct($id: ID!) {
    product(id: $id) {
      ... on SubscriptionProduct {
        id
        name
        price
        subscriptionPrice
        subscriptionPeriod
        subscriptionPeriodInterval
        subscriptionLength
        subscriptionSignUpee
      }
    }
  }
`;
```

## Modifying ProductListing and SingleProduct Components

To support `SubscriptionProduct` types, we need to modify our `ProductListing` and `SingleProduct` components. These components should be able to display the subscription details and allow users to add subscription products to their cart.

Here is an example of how to modify the `ProductListing` component:

```javascript
function ProductListing({ product }) {
  // ... other code

  return (
    <div>
      <h2>{product.name}</h2>
      <p>Price: {product.price}</p>
      <p>Subscription Price: {product.subscriptionPrice}</p>
      <p>Subscription Period: {product.subscriptionPeriod}</p>
      // ... other product details
      <button onClick={() => addToCart(product.id)}>Add to Cart</button>
    </div>
  );
};
```

The `SingleProduct` component can be modified in a similar way.

## Adding a Subscriptions Page

We can add a `Subscriptions` page to the account page clone made in `Using Customer Data + Mutations`. This page will utilize the `subscriptions` field on the `Customer` type that returns a list of `SubscriptionOrder`.

Here is an example of how to query for a customer's subscriptions:

```javascript
const CUSTOMER_SUBSCRIPTIONS_QUERY = gql`
  query CustomerSubscriptions($id: ID!) {
    customer(id: $id) {
      subscriptions {
        nodes {
          id
          status
          total
          lineItems {
            nodes {
              product {
                ... on SubscriptionProduct {
                  name
                  subscriptionPrice
                  subscriptionPeriod
                }
              }
              quantity
            }
          }
        }
      }
    }
  }
`;
```

And here is an example of how to display the subscriptions on the `Subscriptions` page:

```javascript
function SubscriptionsPage({ customer }) {
  return (
    <div>
      <h1>Your Subscriptions</h1>
      {customer.subscriptions.nodes.map(subscription => (
        <div key={subscription.id}>
          <h2>{subscription.status}</h2>
          <p>Total: {subscription.total}</p>
          {subscription.lineItems.nodes.map(lineItem => (
            <div key={lineItem.id}>
              <h3>{lineItem.product.name}</h3>
              <p>Price: {lineItem.product.subscriptionPrice}</p>
              <p>Period: {lineItem.product.subscriptionPeriod}</p>
              <p>Quantity: {lineItem.quantity}</p>
            </div>
          ))}
          <button onClick={() => renewSubscription(subscription.id)} hidden={subscription.status !== 'ACTIVE'}>Renew</button>
          <button onClick={() => changePaymentMethod(subscription.id)} hidden={subscription.status !== 'ACTIVE'}>Change Payment Method</button>
          <button onClick={() => cancelSubscription(subscription.id)} hidden={subscription.status !== 'ACTIVE' && subscription.status !== 'ON_HOLD'}>Cancel</button>
          <button onClick={() => reactivateSubscription(subscription.id)} hidden={subscription.status !== 'CANCELLED' && subscription.status !== 'PENDING_CANCEL'}>Reactivate</button>
        </div>
      ))}
    </div>
  );
};
```

In the `SubscriptionsPage` component, we have added `renew`, `change payment method`, `reactivate`, and `cancel` buttons. The `renew` and `change payment method` buttons use the `changeSubPaymentMethodUrl` and `renewSubPaymentMethodUrl` fields respectively. The `reactivate` and `cancel` buttons use the `reactivateSubscription` and `cancelSubscription` mutations respectively. The visibility of these buttons is controlled based on the status of the subscription.

To learn more about the `changeSubPaymentMethodUrl` and `renewSubPaymentMethodUrl` URLs, please refer to the [Harmonizing with WordPress](https://woographql.com/docs/harmonizing-with-wordpress#harmonizing-with-wordpress) section of the WooGraphQL documentation.

In the next sections, we will delve deeper into how to utilize other types of product data to further enhance our application.
