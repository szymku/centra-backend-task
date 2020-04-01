run: composer-update run-server

composer-update:
	docker run --rm -v $$PWD:/app -w /app -u $$(id -u):$$(id -g) composer:1.10 composer update

composer-install:
	docker run --rm -v $$PWD:/app -w /app -u $$(id -u):$$(id -g) composer:1.10 composer install

run-server:
	docker run --rm -v $$PWD:/app -w /app -u $$(id -u):$$(id -g) -p 8888:8888 php:7.4 \
	php -S 0.0.0.0:8888 -t src/public
