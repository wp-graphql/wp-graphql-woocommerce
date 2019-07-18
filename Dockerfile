FROM ryanshoover/wp-browser

RUN a2enmod rewrite && \
service apache2 restart