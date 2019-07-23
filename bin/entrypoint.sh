#!/bin/bash

# Run WordPress docker entrypoint.
. docker-entrypoint.sh 'apache2'

# Ensure mysql is loaded
dockerize -wait tcp://${DB_HOST}:${DB_HOST_PORT:-3306} -timeout 1m

# Config WordPress
if [ ! -f "${WP_ROOT_FOLDER}/wp-config.php" ]; then
    wp config create \
        --path="${WP_ROOT_FOLDER}" \
        --dbname="${DB_NAME}" \
        --dbuser="${DB_USER}" \
        --dbpass="${DB_PASSWORD}" \
        --dbhost="${DB_HOST}" \
        --dbprefix="${WP_TABLE_PREFIX}" \
        --skip-check \
        --quiet \
        --allow-root
fi

# Install WP if not yet installed
if ! $( wp core is-installed --allow-root ); then
	wp core install \
		--path="${WP_ROOT_FOLDER}" \
		--url="${WP_URL}" \
		--title='Test' \
		--admin_user="${ADMIN_USERNAME}" \
		--admin_password="${ADMIN_PASSWORD}" \
		--admin_email="${ADMIN_EMAIL}" \
		--allow-root
fi

# Install and activate WooCommerce
if [ ! -f "${PLUGINS_DIR}/woocommerce/woocommerce.php" ]; then
	wp plugin install woocommerce --activate --allow-root
fi

# Install and activate WPGraphQL
if [ ! -f "${PLUGINS_DIR}/wp-graphql/wp-graphql.php" ]; then
    wp plugin install \
        https://github.com/wp-graphql/wp-graphql/archive/master.zip \
        --activate --allow-root
fi

# Install and activate WPGraphQL JWT Authentication
if [ ! -f "${PLUGINS_DIR}/wp-graphql-jwt-authentication/wp-graphql-jwt-authentication.php" ]; then
    wp plugin install \
        https://github.com/wp-graphql/wp-graphql-jwt-authentication/archive/master.zip \
        --activate --allow-root
fi

if [ ! -z "$INCLUDE_WPGRAPHIQL" ]; then
    if [ ! -f "${PLUGINS_DIR}/wp-graphiql/wp-graphiql.php" ]; then
        wp plugin install \
            https://github.com/wp-graphql/wp-graphiql/archive/master.zip \
            --activate --allow-root
    fi
fi

# Activate WooGraphQL
wp plugin activate wp-graphql-woocommerce --allow-root

# Set pretty permalinks.
wp rewrite structure '/%year%/%monthnum%/%postname%/' --allow-root

wp db export "${PROJECT_DIR}/tests/_data/dump.sql" --allow-root

exec "$@"