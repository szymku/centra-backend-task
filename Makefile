SERVER_PORT = 8888
DOCKER_IMAGE = php74
SERVER_IMAGE_NAME = nice_one

.PHONY: tests

run: build-image composer-update run-server

build-image:
	docker build -t ${DOCKER_IMAGE} --build-arg USER_ID=$$(id -u) --build-arg GROUP_ID=$$(id -g) .

composer-update:
	docker run --rm -v $$PWD:/app -w /app ${DOCKER_IMAGE} update

composer-install:
	docker run --rm -v $$PWD:/app -w /app ${DOCKER_IMAGE} composer install

run-server:
	docker run --name ${SERVER_IMAGE_NAME} --rm -v $$PWD:/app -w /app -p ${SERVER_PORT}:${SERVER_PORT} ${DOCKER_IMAGE} \
	php -S 0.0.0.0:${SERVER_PORT} -t public

stop-server:
	docker stop ${SERVER_IMAGE_NAME}

fix-code:
	docker run --rm -v $$PWD:/app -w /app ${DOCKER_IMAGE} vendor/bin/php-cs-fixer fix --allow-risky=yes

tests:
	docker run --rm -v $$PWD:/app -w /app ${DOCKER_IMAGE} vendor/bin/phpunit tests

bash:
	docker run --rm -v $$PWD:/app -w /app -ti ${DOCKER_IMAGE} bash
