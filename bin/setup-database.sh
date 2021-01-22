#!/usr/bin/env bash

set +u

##
# Use this script through Composer scripts in the package.json.
# To quickly build and run the docker-compose scripts for an app or automated testing
# run the command below after run `composer install --no-dev` with the respectively
# flag for what you need.
##
print_usage_instructions() {
	echo "Usage: $0 [main|testing]";
	echo "       main  Configures wp-config.php to use main database connection";
	echo "       testing  Configures wp-config.php to use testing database connection";
	exit 1
}

if [ -z "$1" ]; then
	print_usage_instructions
fi

command=$1; shift
case "$command" in
    "main" )
        wp config set DB_HOST app_db --allow-root
        ;;
    "testing" )
        wp config set DB_HOST testing_db --allow-root
        ;;

    \? ) print_usage_instructions;;
    * ) print_usage_instructions;;
esac

# Set WP_SITEURL and WP_HOME constants if env exists.
if wp config has WP_SITEURL --allow-root; then
    wp config delete WP_SITEURL --allow-root
fi
if wp config has WP_HOME --allow-root; then
    wp config delete WP_HOME --allow-root
fi

if [ -n "$WP_SITEURL" ]; then
	wp config set WP_SITEURL ${WP_SITEURL} --allow-root
fi
if [ -n "$WP_HOME" ]; then
	wp config set WP_HOME ${WP_HOME} --allow-root
fi

# If secondary command passed execute it.
if [ $# -ne 0 ]; then
	exec "$@"
fi
