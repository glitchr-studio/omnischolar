# PHP with what the sources need (intl for the Merger's titles, dom for
# arXiv's Atom) and Composer: the harness installs this package and every
# omnischolar/* source at start.
FROM php:8.4-cli-alpine
RUN apk add --no-cache git unzip icu-dev libzip-dev $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" intl zip \
    && apk del $PHPIZE_DEPS
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_CACHE_DIR=/harness/.cache
WORKDIR /harness
ENTRYPOINT ["/omnischolar/core/docker/entrypoint.sh"]
CMD ["sources"]
