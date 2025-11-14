# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plan & Review

### Before starting work
- Always in plan mode to make a plan
- After getting the plan, make sure you Write the plan to .claude/tasks/TASK_NAME.md
- The plan should be a detailed implementation plan and the reasoning behind them, as well as tasks broken down.
- If the task requires external knowledge or certain packages, also research to get the latest knowledge (Use Task tool for research)
- Don't over plan it, always think MVP
- Once you write the plan, firstly ask me to review it. Do not continue until I approve the plan.

### While implementing
- You should update the plan as you work.
- After you complete tasks in the plan, you should update and append detailed descriptions of the changes you made, so following tasks can be easily handed over to other engineers.

## Development Commands

### Testing
- **Run all tests**: `composer runWPUnitTest` or `vendor/bin/codecept run wpunit`
- **Run single test file**: `vendor/bin/codecept run wpunit tests/wpunit/CartMutationsTest.php`
- **Run with coverage**: Tests have coverage enabled by default in codeception.dist.yml

### Linting and Code Quality
- **Lint code**: `composer lint` (runs phpcs)
- **Fix linting issues**: `composer fix` (runs phpcbf)
- **Static analysis**: `composer stan` (runs phpstan)
- **Pre-commit cleanup**: `composer runPreCommitCleanup`

### Docker Development Setup
1. **Environment setup**: Copy `.env.testing` to `.env` OR set `SKIP_DB_CREATE=true` and `SKIP_WP_SETUP=true` in existing `.env`
2. **Install test environment**: `composer installTestEnv` (requires `.env` file to exist first)
3. **Build containers**: `composer dBuild`
4. **Run app with database**: `composer dRunApp`
5. **Run tests in Docker**: `composer dRunTest`
6. **Destroy containers**: `composer dDestroy`

## Architecture Overview

### Core Plugin Structure
- **Entry Point**: `wp-graphql-woocommerce.php` - Main plugin file with initialization logic
- **Core Class**: `includes/class-wp-graphql-woocommerce.php` - Singleton pattern, handles plugin setup and dependencies
- **Type Registry**: `includes/class-type-registry.php` - Registers all GraphQL types, connections, and mutations

### WooCommerce Integration Pattern
This plugin extends WPGraphQL to work with WooCommerce's data store system rather than standard WordPress CPTs. Key differences:
- Uses WooCommerce object managers as data sources (not WP_Post objects)
- Each WooCommerce object type has custom models with specific permissions
- Data may be stored in separate tables (like WooCommerce's HPOS)

### GraphQL Schema Architecture

#### Data Layer (`includes/data/`)
- **Loaders**: Custom data loaders for WooCommerce objects (`loader/`)
  - `WC_CPT_Loader` - For WooCommerce Custom Post Types
  - `WC_Customer_Loader` - For customer data
  - `WC_DB_Loader` - For direct database objects
- **Connection Resolvers**: Handle GraphQL connections (`connection/`)
  - Product connections, order connections, customer connections, etc.
- **Mutation Processors**: Business logic for GraphQL mutations (`mutation/`)
  - Cart mutations, checkout, order management, customer operations

#### Type System (`includes/type/`)
- **Enums**: WooCommerce-specific enumerations (order status, product types, etc.)
- **Interfaces**: Shared interfaces for products, cart items, attributes
- **Objects**: Concrete GraphQL object types for products, orders, customers, etc.
- **Inputs**: Input types for mutations and filtering

#### Connection System (`includes/connection/`)
- Registers GraphQL connections between different object types
- Handles pagination, filtering, and sorting for WooCommerce data

### Session Management
The plugin includes sophisticated session handling:
- **QL Session Handler**: `includes/utils/class-ql-session-handler.php`
- **Session Transaction Manager**: For concurrent cart operations
- **Protected Router**: For secure checkout URLs when enabled

### Key Features
- **Product Management**: Full GraphQL schema for WooCommerce products, variations, attributes
- **Order Operations**: Create, read, update orders with proper permissions
- **Cart Functionality**: Session-based cart with mutations for add/remove/update items
- **Customer Management**: Registration, authentication, profile updates
- **Checkout Process**: Complete checkout flow or integration with WooCommerce checkout page

### Testing Architecture
- **Codeception Framework**: Used for all testing
- **Test Suites**:
  - `wpunit` - Unit tests for WordPress environment
  - `functional` - Functional tests with database
  - `acceptance` - End-to-end browser tests
- **Factory Classes**: Helper classes for creating test data (`tests/_support/Factory/`)
- **Docker Support**: Full Docker setup for isolated testing environment

### Code Standards
- Follows WPGraphQL coding standards (configured in `phpcs.xml.dist`)
- PSR-4 autoloading with composer
- Namespace: `WPGraphQL\WooCommerce`
- Minimum PHP 7.3, WordPress 6.1+, WooCommerce 8.9.0+

### Dependencies
- **Required**: WPGraphQL 1.27.0+, WooCommerce 8.9.0+
- **Optional**: WPGraphQL JWT Authentication, WPGraphQL CORS
- **Vendor Dependencies**: firebase/php-jwt (prefixed to avoid conflicts)

### Extension Points
The plugin provides extensive filter and action hooks for customization:
- `graphql_woocommerce_post_types` - Modify registered post types  
- `graphql_woocommerce_product_types` - Control exposed product types
- `graphql_woocommerce_init` - Hook after plugin initialization

### Important Notes
- WooCommerce CPTs don't support all WPGraphQL features due to the data store system
- The plugin handles WooCommerce's High-Performance Order Storage (HPOS) 
- Session handling can be disabled via settings for custom implementations
- Protected routing is available for secure checkout flows