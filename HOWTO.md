# HOWTO

## Description
Application will be orchistrated with docker. To run application that way docker is needed.
For easy startup and execution of docker commands is prepared command wrapper in `Makefile`.
PHP7.4 will be used.

## How to run
Should be enough to run `make run` - it will update dependencies and run server on port `8888`

## Tips
Instead of PHP in docker local PHP could be used as well.

If we want run some command in docker(e.g add a package to composer.json) we can write : `docker run --rm -v $PWD:/app -w /app -u $(id -u):$(id -g) -p 8888:8888 composer:1.10 require vlucas/phpdotenv`

## Links
[Repo in Github](https://github.com/szymku/centra-backend-task)
[Project board](https://github.com/szymku/centra-backend-task/projects/1)
