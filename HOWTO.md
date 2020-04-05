# HOWTO

## Description
The application will be orchestrated with docker.  
Execution of docker commands is facilitated with wrappers that are in `Makefile`.  
PHP7.4 is used.  
Application is deployed and available under [http://51.38.134.29:8888](http://51.38.134.29:8888)  
As an example is used this project with their own board and milestones.

## Requirements
 - docker 
 - make utility - install one with `sudo apt install make`

## How to run
Clone repo:`git clone https://github.com/szymku/centra-backend-task.git`  
Go to: `cd centra-backend-task`  
Copy `.env.example` to `.env` and fill it with your data.  
To run app use `make run` - it will build container, install dependencies and run the server on port `8888`.  

Alternatively without `make` run:
```
docker build -t php74 --build-arg USER_ID=$(id -u) --build-arg GROUP_ID=$(id -g) .
docker run --rm -v $PWD:/app -w /app php74 composer install
docker run --rm -v $PWD:/app -w /app -p 8888:8888 php74 php -S 0.0.0.0:8888 -t public
```
 
## Tips
If we want to run a command in docker(e.g. add a package to composer.json) we can run:  
`docker run --rm -v $PWD:/app -w /app php74 composer require vlucas/phpdotenv`

## Links
[Repo in Github](https://github.com/szymku/centra-backend-task)  
[Project board](https://github.com/szymku/centra-backend-task/projects/1)  
[Milestones](https://github.com/szymku/centra-backend-task/milestones)
