#!/usr/bin/env bash

set +u

if [[ -z "$DB_NAME" ]]; then
	echo "DB_NAME not found"
	print_usage_instruction
fi
if [[ -z "$DB_USER" ]]; then
	echo "DB_USER not found"
	print_usage_instruction
fi

DB_HOST=${DB_HOST-localhost}
DB_PASS=${DB_PASSWORD-""}
WP_VERSION=${WP_VERSION-6}
PROJECT_ROOT_DIR=$(pwd)
WP_CORE_DIR=${WP_CORE_DIR:-local/public}
PLUGINS_DIR=${PLUGINS_DIR:-"$WP_CORE_DIR/wp-content/plugins"}
MUPLUGINS_DIR=${MUPLUGINS_DIR:-"$WP_CORE_DIR/wp-content/mu-plugins"}
THEMES_DIR=${THEMES_DIR:-"$WP_CORE_DIR/wp-content/themes"}
SKIP_DB_CREATE=${SKIP_DB_CREATE-false}
