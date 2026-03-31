<!--
title: "Building a WooCommerce Admin App with WooGraphQL"
description: "Learn how to use WooGraphQL's admin-level queries and mutations to build a WooCommerce management interface, including WC Settings, product CRUD, product attribute management, and refund operations."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, admin, settings, products, refunds, mutations"
author: "Geoff Taylor"
-->

# Building a WooCommerce Admin App

WooGraphQL v1.0.0 introduces several admin-level queries and mutations that enable building custom WooCommerce management interfaces through GraphQL. This guide covers the WC Settings API, product and product attribute CRUD mutations, and refund operations.

All operations in this guide require `manage_woocommerce` or equivalent capabilities. Authenticate your requests using [WPGraphQL JWT Authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication) or [WPGraphQL Headless Login](https://github.com/AxeWP/wp-graphql-headless-login) with an admin or shop manager account.

## WooCommerce Settings API

### Querying Setting Groups

Retrieve all available WooCommerce setting groups:

```graphql
query {
  wcSettingGroups {
    id
    label
    description
    parentId
  }
}
```

### Querying Settings in a Group

Retrieve all settings within a specific group:

```graphql
query {
  wcSettings(group: "general") {
    id
    label
    description
    type
    value
    default
    options {
      key
      value
    }
  }
}
```

Common groups include `general`, `products`, `tax`, `shipping`, `checkout`, `account`, and `email`.

### Updating a Single Setting

```graphql
mutation {
  updateWCSetting(input: {
    group: "general"
    id: "woocommerce_store_city"
    value: "New York"
  }) {
    setting {
      id
      label
      value
    }
  }
}
```

### Updating Multiple Settings

```graphql
mutation {
  updateWCSettings(input: {
    group: "general"
    settings: [
      { id: "woocommerce_store_city", value: "New York" }
      { id: "woocommerce_store_postcode", value: "10001" }
      { id: "woocommerce_default_country", value: "US:NY" }
    ]
  }) {
    settings {
      id
      value
    }
  }
}
```

## Product Mutations

### Creating a Simple Product

```graphql
mutation {
  createProduct(input: {
    name: "Premium T-Shirt"
    type: SIMPLE
    regularPrice: "29.99"
    description: "A high-quality cotton t-shirt."
    shortDescription: "Premium cotton tee."
    sku: "TSHIRT-001"
    stockQuantity: 100
    manageStock: true
    categories: [{ id: 15 }]
  }) {
    product {
      databaseId
      name
      ... on SimpleProduct {
        price
        regularPrice
        sku
        stockQuantity
      }
    }
  }
}
```

### Updating a Product

```graphql
mutation {
  updateProduct(input: {
    id: "cHJvZHVjdDoxMDA="
    salePrice: "24.99"
    stockQuantity: 85
  }) {
    product {
      databaseId
      ... on SimpleProduct {
        price
        salePrice
        stockQuantity
      }
    }
  }
}
```

### Deleting a Product

```graphql
mutation {
  deleteProduct(input: {
    id: "cHJvZHVjdDoxMDA="
    forceDelete: true
  }) {
    product {
      databaseId
      name
    }
  }
}
```

## Product Attribute Management

### Creating a Product Attribute

```graphql
mutation {
  createProductAttribute(input: {
    name: "Material"
    slug: "material"
    hasArchives: true
    orderBy: "name"
  }) {
    attribute {
      id
      name
      slug
    }
  }
}
```

### Creating Attribute Terms

```graphql
mutation {
  createProductAttributeTerm(input: {
    attributeId: 1
    name: "Cotton"
    slug: "cotton"
  }) {
    term {
      id
      name
      slug
    }
  }
}
```

## Refund Operations

### Creating a Refund

Create a refund on a completed order:

```graphql
mutation {
  createRefund(input: {
    orderId: 123
    amount: "15.00"
    reason: "Customer requested partial refund"
  }) {
    refund {
      databaseId
      amount
      reason
      date
      refundedBy {
        name
      }
    }
    order {
      databaseId
      total
      status
    }
  }
}
```

The `createRefund` mutation supports these optional fields:

- `refundPayment` — When `true`, triggers the payment gateway's refund API (e.g., Stripe refund).
- `restockItems` — When `true`, restocks the refunded items.
- `metaData` — Attach custom metadata to the refund.

### Deleting a Refund

```graphql
mutation {
  deleteRefund(input: {
    id: "b3JkZXI6NDU="
  }) {
    refund {
      databaseId
      amount
    }
    order {
      databaseId
      total
    }
  }
}
```

**Note:** WooCommerce does not support updating refunds after creation. Refunds are immutable financial records and can only be created or deleted.

### Querying Refunds on an Order

Refunds are available through the `refunds` connection on Order types:

```graphql
query {
  order(id: "b3JkZXI6MTIz") {
    databaseId
    total
    refunds {
      nodes {
        databaseId
        amount
        reason
        date
        refundedBy {
          name
        }
      }
    }
  }
}
```

## Authorization

All admin operations require appropriate capabilities:

| Operation | Required Capability |
|-----------|-------------------|
| WC Settings queries | `manage_woocommerce` |
| WC Settings mutations | `manage_woocommerce` |
| Product create/update/delete | `edit_products` / `delete_products` |
| Product attribute CRUD | `manage_product_terms` |
| Create refund | `edit_shop_orders` |
| Delete refund | `delete_shop_orders` |

For client-side admin apps, authenticate with a shop manager or administrator account. For server-side applications, consider using [WordPress Application Passwords](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/) with the WPGraphQL introspection endpoint.
