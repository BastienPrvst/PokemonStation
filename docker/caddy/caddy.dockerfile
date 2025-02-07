ARG CADDY_VERSION=2

FROM caddy:${CADDY_VERSION}-builder-alpine AS symfony_caddy_builder

RUN xcaddy build \
	--with github.com/dunglas/mercure \
	--with github.com/dunglas/mercure/caddy \
	--with github.com/dunglas/vulcain \
	--with github.com/dunglas/vulcain/caddy

FROM caddy:${CADDY_VERSION} AS symfony_caddy

WORKDIR /srv/app

COPY --from=dunglas/mercure:v0.11 /srv/public /srv/mercure-assets/
COPY --from=symfony_caddy_builder /usr/bin/caddy /usr/bin/caddy
COPY caddy/Caddyfile /etc/caddy/Caddyfile
