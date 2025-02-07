# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target
# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact

ARG PHP_VERSION=8.2

# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
		gifsicle \
		vim \
		imagemagick \
	;

ARG APCU_VERSION=5.1.21
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-data-full \
		icu-dev \
		libzip-dev \
		zlib-dev \
		libpng-dev \
		imagemagick-dev \
		jpeg-dev \
		libwebp-dev \
		freetype-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp; \
	docker-php-ext-install -j$(nproc) \
		intl \
		zip \
		gd \
		opcache \
		pdo \
		pdo_mysql \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
		imagick \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
		imagick \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY php/conf.d/symfony.prod.ini $PHP_INI_DIR/conf.d/symfony.ini

COPY php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

VOLUME /var/run/php

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/app

COPY . .

VOLUME /srv/app/var

CMD ["php-fpm"]
