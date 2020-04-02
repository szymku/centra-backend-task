PHP_VERSION = 7.4
COMPOSER_IMG_VERSION = 1.10
SERVER_PORT = 8888

run: composer-update run-server

composer-update:
	docker run --rm -v $$PWD:/app -w /app -u $$(id -u):$$(id -g) composer:${COMPOSER_IMG_VERSION} composer update

composer-install:
	docker run --rm -v $$PWD:/app -w /app -u $$(id -u):$$(id -g) composer:${COMPOSER_IMG_VERSION} composer install

run-server:
	docker run --rm -v $$PWD:/app -w /app -u $$(id -u):$$(id -g) -p ${SERVER_PORT}:${SERVER_PORT} php:${PHP_VERSION} \
	php -S 0.0.0.0:${SERVER_PORT} -t src/public

fix-code:
	docker run --rm -v $$PWD:/app -w /app -u $$(id -u):$$(id -g) php:${PHP_VERSION} vendor/bin/php-cs-fixer fix --allow-risky=yes
