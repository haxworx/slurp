# Web Robot

Web crawling engine and database management engine.

The crawler is written in Python with the management user-interface
written with Symfony in PHP.

## Requirements
* Composer (https://getcomposer.org/)
* Yarn (https://yarnpkg.com/)
* Docker (https://docs.docker.com/engine/install/)

## Installation Guide

### Packages
```
composer install
yarnpkg encore dev
```

### Database

```
docker-compose build
docker-compose up
```

Create schema and run migrations to seed the database.

```
docker exec -it slurp-apache sh
```

Execute the following commands within the slurp-apache container:

```
php bin/console doctrine:schema:create
php bin/console doctrine:migrations:migrate
```

Bring down the application and restart.

```
docker-compose down
docker-compose up
````

The application is configured and ready to use.

Browse to http://localhost:8080.

