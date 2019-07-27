#!/bin/bash

# Run app entrypoint script.
. app-entrypoint.sh

# Ensure Apache is running
service apache2 start

# Ensure everything is loaded
dockerize \
    -wait tcp://${TEST_DB_HOST}:${TEST_DB_HOST_PORT:-3306} \
    -wait ${WP_URL} \
    -timeout 1m

# Download c3 for testing.
if [ ! -f "${PROJECT_DIR}/c3.php" ]; then
    echo 'Downloading c3.php'
    curl -L 'https://raw.github.com/Codeception/c3/2.0/c3.php' > "${PROJECT_DIR}/c3.php"
fi

# Run the tests
echo 'Moving to WooGraphQL directory.'
cd ${PROJECT_DIR}

if [ "$COVERAGE" == "1" -a "$DEBUG" == "1" ]; then
    vendor/bin/codecept run ${SUITE} --debug --coverage --coverage-xml
elif [ "$COVERAGE" == "1" ]; then
    vendor/bin/codecept run ${SUITE} --coverage --coverage-xml
elif [ "$DEBUG" == "1" ]; then
    vendor/bin/codecept run ${SUITE} --debug
else
    vendor/bin/codecept run ${SUITE}
fi

echo 'Setting Codeception output directory permissions'.
chmod 777 -R ${TESTS_OUTPUT}