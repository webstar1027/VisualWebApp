FROM thecodingmachine/php:7.3-v2-apache-node10

COPY --chown=docker:docker . .

ENV APACHE_DOCUMENT_ROOT="public/" \
    PHP_EXTENSION_WEAKREF="1" \
    PHP_EXTENSION_GD="1" \
    APP_ENV="prod" \
    APP_SECRET="8d2a5c935d8ef1c0e2b751147382bc75"

# Dummy values for allowing composer install.
ENV DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

RUN composer install &&\
    yarn install &&\
    yarn build
