dockerComposeDirectory = "./docker"

init: down \
	build \
	up \
	composer-install \
	npm-install \
	npm-build

restart: down up

own-template: get-template npm-build

build:
	@cd $(dockerComposeDirectory) && \
	docker-compose build

up:
	@cd $(dockerComposeDirectory) && \
	docker-compose up -d

down:
	@cd $(dockerComposeDirectory) && \
	docker-compose down

ps:
	@cd $(dockerComposeDirectory) && \
	docker-compose ps

bash:
	@cd $(dockerComposeDirectory) && \
	docker-compose run --rm php-cli bash

composer-install:
	@cd $(dockerComposeDirectory) && \
	docker-compose run --rm php-cli composer install

npm-cli:
	@cd $(dockerComposeDirectory) && \
	docker-compose run --rm node-cli bash

npm-install:
	@cd $(dockerComposeDirectory) && \
	docker-compose run --rm node-cli npm install

npm-build:
	@cd $(dockerComposeDirectory) && \
	docker-compose run --rm node-cli npm run build

npm-watch:
	@cd $(dockerComposeDirectory) && \
	docker-compose run --rm node-cli npm run watch

get-template:
	@cd $(dockerComposeDirectory) && \
	docker-compose run --rm ssh bash -c "php own_template.php"
