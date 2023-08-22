#!/bin/bash

if [ "$USING_XDEBUG" == "1"  ]; then
    echo "Enabling XDebug 3"
    mv /usr/local/etc/php/conf.d/disabled/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/
fi

# Run WordPress docker entrypoint.
. docker-entrypoint.sh 'apache2'

set +u

# Ensure mysql is loaded
wait-for-it -s -t 300 ${DB_HOST}:${DB_PORT} -- echo "Application database is operationally..."

# Setup tester scripts.
if [ ! -f $WP_ROOT_FOLDER/setup-database.sh ]; then
	ln -s $PROJECT_DIR/bin/setup-database.sh $WP_ROOT_FOLDER/setup-database.sh
	chmod +x $WP_ROOT_FOLDER/setup-database.sh
fi

# Update our domain to just be the docker container's IP address
export WORDPRESS_DOMAIN=${WORDPRESS_DOMAIN-$( hostname -i )}
export WORDPRESS_URL="http://$WORDPRESS_DOMAIN"
if [ -f $PROJECT_DIR/.env.docker ]; then
	rm $PROJECT_DIR/.env.docker
fi
echo "WORDPRESS_DOMAIN=$WORDPRESS_DOMAIN" >> $PROJECT_DIR/.env.docker
echo "WORDPRESS_URL=$WORDPRESS_URL" >> $PROJECT_DIR/.env.docker

# Config WordPress
if [ -f "${WP_ROOT_FOLDER}/wp-config.php" ]; then
	echo "Deleting old wp-config.php"
	rm ${WP_ROOT_FOLDER}/wp-config.php
fi

echo "Creating wp-config.php..."
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

# Install WP if not yet installed
if ! $( wp core is-installed --allow-root ); then
	echo "Installing WordPress..."
	wp core install \
		--path="${WP_ROOT_FOLDER}" \
		--url="${WORDPRESS_URL}" \
		--title='Test' \
		--admin_user="${ADMIN_USERNAME}" \
		--admin_password="${ADMIN_PASSWORD}" \
		--admin_email="${ADMIN_EMAIL}" \
		--allow-root
fi

echo "Activating plugins..."
wp plugin activate \
woocommerce woocommerce-gateway-stripe \
wp-graphql wp-graphql-jwt-authentication \
wp-graphql-woocommerce \
--allow-root

wp theme activate twentytwentyone --allow-root

if ! wp config has GRAPHQL_JWT_AUTH_SECRET_KEY --allow-root; then
	echo "Adding WPGraphQL-JWT-Authentication salt..."
    wp config set GRAPHQL_JWT_AUTH_SECRET_KEY 'test' --allow-root
fi

if ! wp config has GRAPHQL_WOOCOMMERCE_SECRET_KEY --allow-root; then
	echo "Adding WooGraphQL JWT Session Handler salt..."
	wp config set GRAPHQL_WOOCOMMERCE_SECRET_KEY 'testestestestest' --allow-root
fi

if wp config has GRAPHQL_DEBUG --allow-root; then
	echo "Setting GRAPHQL_DEBUG flag"
    wp config delete GRAPHQL_DEBUG --allow-root
fi
if [[ -n "$GRAPHQL_DEBUG" ]]; then
	wp config set GRAPHQL_DEBUG $GRAPHQL_DEBUG --allow-root
fi

if [[ -n "$IMPORT_WC_PRODUCTS" ]]; then
	echo "Installing & Activating WordPress Importer..."
	wp plugin install wordpress-importer --activate --allow-root
	echo "Importing store products..."
	wp import \
		${PLUGINS_DIR}/woocommerce/sample-data/sample_products.xml \
		--authors=skip --allow-root
fi

echo "Setting pretty permalinks..."
wp rewrite structure '/%year%/%monthnum%/%postname%/' --allow-root

echo "Prepare for app database dump..."
if [ ! -d "${PROJECT_DIR}/local/db" ]; then
	mkdir ${PROJECT_DIR}/local/db
fi
if [ -f "${PROJECT_DIR}/local/db/app_db.sql" ]; then
	rm ${PROJECT_DIR}/local/db/app_db.sql
fi

echo "Dumping app database..."
wp db export "${PROJECT_DIR}/local/db/app_db.sql" \
	--dbuser="root" \
	--dbpass="${ROOT_PASSWORD}" \
	--skip-plugins \
	--skip-themes \
	--allow-root

# Create the "uploads" directory and set public permissions.
if [ ! -d "wp-content/uploads" ]; then
	mkdir wp-content/uploads
fi
chmod 777 -R wp-content/uploads

if [ -n "$RUNNING_TEST_STANDALONE" ]; then
	service apache2 start
fi

echo "Running WordPress version: $(wp core version --allow-root) at $(wp option get home --allow-root)"

exec "$@"
