---
title: "Playground"
metaTitle: "WooGraphQL Playground | WooGraphQL Docs | AxisTaylor"
metaDescription: "Tests the queries and mutation using the WooGraphQL playground."
---

import GraphiQL from '../src/components/graphiql'

Test queries and mutations as well as view the WooGraphQL schema, using the playground below :point_down:.

<GraphiQL
  endpoint="https://docs.axistaylor.com/graphql"
  authButtons={{
    Customer: ['customer', 'graphql_rocks'],
    ShopManager: ['shop_manager', 'graphql_rocks'],
  }}
  query="
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
  "
  withDocs={true}
  showJoyride={false}
/>

> This is a playground connected to the most stable release of the extension.

WooGraphQL has many features not available to anomymous user. Try selecting one of the users above and once the indicator on the button is green your logged in.

Here an example queries exclusive to registered customer.

```
query {
  customer {
    firstName
    lastName
  }
  cart {
    contents {
      nodes {
        key
        product {
          id
          productId
          name
          sku
        }
      }
    }
  }
}
```

Here a query exclusive to shop managers and administrators.

```
query {
  orders {
    edges {
      cursor
      node {
        id
        orderId
        lineItems {
          nodes {
            itemId
            product {
              id
              name
              type
              ... on SimpleProduct {
                price
                salePrice
                regularPrice
              }
              ... on VariableProduct {
                price
                salePrice
                regularPrice
              }
            }
            quantity
            total
          }
        }
        subtotal
        total
      }
    }
  }
}
```