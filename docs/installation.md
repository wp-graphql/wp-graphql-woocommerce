---
title: "WPGraphQL for WooCommerce Installation Guide"
author: "Geoff Taylor"
description: "Step-by-step instructions to install and set up WooGraphQL, the WPGraphQL extension that integrates WooCommerce with GraphQL for headless e-commerce solutions."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, installation, setup, headless e-commerce"
---

# Installation

This section will walk you through the process of installing and configuring WPGraphQL for WooCommerce for your WordPress website.

## Prerequisites

1. A WordPress website with WooCommerce installed and activated.
2. WPGraphQL plugin installed and activated.

## Step-by-step guide

### 1. Download the WPGraphQL for WooCommerce plugin

Visit the official WPGraphQL for WooCommerce website (https://woographql.com/) and download the latest release as a zip file.

### 2. Install the WPGraphQL for WooCommerce plugin

a. Log in to your WordPress admin dashboard.
b. Navigate to 'Plugins > Add New'.
c. Click on the 'Upload Plugin' button at the top of the page.
d. Click 'Choose File' and select the downloaded zip file.
e. Click 'Install Now' and wait for the installation process to complete.

### 3. Activate the WPGraphQL for WooCommerce plugin

After the installation is complete, click on the 'Activate Plugin' button to enable WPGraphQL for WooCommerce on your website.

### 4. Verify the plugin is working correctly

Visit the WPGraphQL endpoint (usually 'yourwebsite.com/graphql') and ensure that the WooCommerce types and fields have been added to the schema. You can use a GraphQL client like GraphiQL or GraphQL Playground for this purpose.

## Next Steps

### Configure WooCommerce and WPGraphQL for WooCommerce settings

If you want to customize any WooCommerce settings related to the GraphQL schema, you can do so by visiting 'WooCommerce > Settings' in your WordPress admin dashboard. Or customize you WPGraphQL for WooCommerce responses by visiting 'WPGraphQL > Settings' from the dashboard and selecting the 'WooGraphQL' tab. Learn about the WPGraphQL for WooCommerce settings in-depth [here](settings.md).

### Explore the WPGraphQL for WooCommerce schema

Familiarize yourself with the available queries, mutations, and types by visiting the official WPGraphQL for WooCommerce schema page (https://woographql.com/schema).

### Start using WPGraphQL for WooCommerce in your application

You can now use WPGraphQL for WooCommerce to build powerful applications that interact with your WooCommerce store data via GraphQL queries and mutations. This enables you to create custom storefronts, mobile apps, or third-party integrations with ease.
