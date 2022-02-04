#!/usr/bin/env bash

set +u

install_wordpress() {
	if [ -f $WP_CORE_DIR/wp-config.php ]; then
		echo "Wordpress already installed."
		return;
	fi

	# Set the wordpress install directory and plugin paths in the composer.json
	composer config --unset extra.wordpress-install-dir;
	composer config extra.wordpress-install-dir $WP_CORE_DIR;

	composer config --unset extra.installer-paths;
	composer config --json extra.installer-paths "{
	\"$PLUGINS_DIR/{\$name}/\": [\"type:wordpress-plugin\"],
	\"$MUPLUGINS_DIR/{\$name}/\": [\"type:wordpress-muplugin\"],
	\"$THEMES_DIR/{\$name}/\": [\"type:wordpress-theme\"]
}"

	# Install Wordpress + integrated plugins for testing/development.
	composer install
	composer require --dev -W \
		composer/installers:~1.0 \
		johnpbloch/wordpress:~${WP_VERSION} \
        wp-graphql/wp-graphql-jwt-authentication \
        wpackagist-plugin/woocommerce \
        wpackagist-plugin/woocommerce-gateway-stripe \
        wpackagist-plugin/wp-graphql \
        wpackagist-theme/twentytwentyone \
		wp-cli/wp-cli-bundle:*
}

remove_wordpress() {
	# Uninstall woocommerce plugins.
	if [ -f $WP_CORE_DIR/wp-config.php ]; then
		wp plugin uninstall woocommerce --deactivate --path=${WP_CORE_DIR}
	fi

	# Remove WordPress dependencies
	composer remove --dev wp-graphql/wp-graphql-jwt-authentication \
        wpackagist-plugin/woocommerce-gateway-stripe \
        wpackagist-plugin/wp-graphql \
        wpackagist-theme/twentytwentyone \
        wpackagist-plugin/woocommerce \
		johnpbloch/wordpress \
		composer/installers \
		wp-cli/wp-cli-bundle
}

install_local_test_library() {
	# Install testing library dependencies.
	composer install
	composer require --dev \
		lucatume/wp-browser \
		codeception/module-asserts:^1.0 \
		codeception/module-rest:^1.0 \
		codeception/util-universalframework:^1.0  \
		wp-graphql/wp-graphql-testcase \
		stripe/stripe-php
}

remove_local_test_library() {
	# Remove testing library dependencies.
	composer remove --dev wp-graphql/wp-graphql-testcase \
		codeception/module-asserts \
		codeception/module-rest \
		codeception/util-universalframework \
		lucatume/wp-browser \
		stripe/stripe-php
}

install_db() {
	if [ ${SKIP_DB_CREATE} = "true" ]; then
		echo "Skipping database creation..."
		return 0
	fi

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# create database
	RESULT=`mysql -u $DB_USER --password="$DB_PASS" --skip-column-names -e "SHOW DATABASES LIKE '$DB_NAME'"$EXTRA`
	if [ "$RESULT" != $DB_NAME ]; then
			mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
	fi
}


configure_wordpress() {
	if [ ${SKIP_WP_SETUP} = "true" ]; then
		echo "Skipping WordPress setup..."
		return 0
	fi

    cd $WP_CORE_DIR

	echo "Setting up WordPress..."
    wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" --skip-check --force=true
    wp core install --url=wp.test --title="WooGraphQL Tests" --admin_user=admin --admin_password=password --admin_email=admin@woographql.local
    wp rewrite structure '/%year%/%monthnum%/%postname%/'
}

setup_plugin() {
	if [ ${SKIP_WP_SETUP} = "true" ]; then
		echo "Skipping WooGraphQL installation..."
		return 0
	fi

	# Move to project root directory
	cd $PROJECT_ROOT_DIR

	# Add this repo as a plugin to the repo
	if [ ! -d $WP_CORE_DIR/wp-content/plugins/wp-graphql-woocommerce ]; then
		echo "Installing WooGraphQL..."
		ln -s $PROJECT_ROOT_DIR $WP_CORE_DIR/wp-content/plugins/wp-graphql-woocommerce
	fi

	# Move to WordPress directory
	cd $WP_CORE_DIR

	# Activate the plugin, it's dependencies should be activated already.
	wp plugin activate wp-graphql-woocommerce

	# Flush the permalinks
	wp rewrite flush

	# Export the db for codeception to use
	if [ ! -d "$PROJECT_ROOT_DIR/local/db" ]; then
		mkdir ${PROJECT_ROOT_DIR}/local/db
	fi
	if [ -f "$PROJECT_ROOT_DIR/local/db/app_db.sql" ]; then
		echo "Deleting old DB dump..."
		rm -rf ${PROJECT_ROOT_DIR}/local/db/app_db.sql
	fi

	wp db export ${PROJECT_ROOT_DIR}/local/db/app_db.sql
}
