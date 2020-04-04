# HOWTO

## Description
Application will be orchestrated with docker. To run application that way docker is needed.
For easy startup and execution of docker commands wrappers of them are in `Makefile`.
PHP7.4 will be used.

## Requirements
To run commands in easy way `make` utility is needed. To install one run `sudo apt install make`.

## How to run
Copy `.env.example` to `.env` and fill it with your data.

To run app use `make run` - it will build container, install dependencies and run server on port `8888`.

## Tips
Instead of PHP in docker local PHP could be used as well.

If we want to run a command in docker(e.g add a package to composer.json) we can run:
`docker run --rm -v $PWD:/app -w /app php74 composer require vlucas/phpdotenv`

## Links
[Repo in Github](https://github.com/szymku/centra-backend-task)
[Project board](https://github.com/szymku/centra-backend-task/projects/1)
[Milestones](https://github.com/szymku/centra-backend-task/milestones)
