#!/bin/bash

# Run app entrypoint script.
. app-entrypoint.sh

# Ensure Apache is running
service apache2 start

# Ensure everything is loaded
dockerize \
    -wait tcp://${TEST_DB_HOST}:${TEST_DB_HOST_PORT:-3306} \
    -wait http://localhost \
    -timeout 1m


# Download c3 for testing.
if [ ! -f "${PROJECT_DIR}/c3.php" ]; then 
    curl -L 'https://raw.github.com/Codeception/c3/2.0/c3.php' > "${PROJECT_DIR}/c3.php"
fi

# Link codeception config if not yet linked
if [ ! -e codeception.dist.yml ]; then
	ln -s /var/www/config/codeception.dist.yml /var/www/html/codeception.dist.yml
fi

# Run the tests
if [ "$COVERAGE" == "1" ]; then
    codecept run ${SUITE} --debug --coverage --coverage-xml
elif [ "$DEBUG" == "1" ]; then
    codecept run ${SUITE} --debug
else
    codecept run ${SUITE}
fi

exec "$@"