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
yarnpkg install
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

## Contributing Guide

The recommended way to install PHP CS Fixer is to use [Composer](https://getcomposer.org/download/)
in a dedicated `composer.json` file in your project, for example in the
`tools/php-cs-fixer` directory:

```console
mkdir -p tools/php-cs-fixer
composer require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer
```

Before commit, call `composer standardize` to apply code styling


