###############################################################################
# Pre-configured WordPress Installation w/ WooCommerce, WPGraphQL, WooGraphQL #
# For testing only, use in production not recommended.                        #
###############################################################################
ARG WP_VERSION
ARG PHP_VERSION

FROM wordpress:${WP_VERSION}-php${PHP_VERSION}-apache

ENV WP_VERSION=${WP_VERSION}
ENV PHP_VERSION=${PHP_VERSION}

LABEL author=kidunot89
LABEL author_uri=https://github.com/kidunot89

SHELL [ "/bin/bash", "-c" ]

# Install system packages
RUN apt-get update && \
    apt-get -y install \
    # CircleCI depedencies
    git \
    ssh \
    tar \
    gzip \
    wget \
    mariadb-client

# Install Dockerize
ENV DOCKERIZE_VERSION v0.6.1
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# Set project environmental variables
ENV WP_ROOT_FOLDER="/var/www/html"
ENV WORDPRESS_DB_HOST=${DB_HOST}
ENV WORDPRESS_DB_USER=${DB_USER}
ENV WORDPRESS_DB_PASSWORD=${DB_PASSWORD}
ENV WORDPRESS_DB_NAME=${DB_NAME}
ENV PLUGINS_DIR="${WP_ROOT_FOLDER}/wp-content/plugins"
ENV PROJECT_DIR="${PLUGINS_DIR}/wp-graphql-woocommerce"

# Remove exec statement from base entrypoint script.
RUN sed -i '$d' /usr/local/bin/docker-entrypoint.sh

# Set up Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Set up entrypoint
WORKDIR    /var/www/html
COPY       docker/app.entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN        chmod 755 /usr/local/bin/app-entrypoint.sh
ENTRYPOINT ["app-entrypoint.sh"]
CMD ["apache2-foreground"]