# Wayne lifts

A project with using Symfony 5 as framework MVC and running with php7 and MySQL 8.

## Specs

<a href="https://dberniell.github.io/firestorm-operation/" target="blank">Specs</a>

## Documentation

[Buses](https://github.com/dberniell/firestorm-operation/blob/master/docs/GetStarted/Buses.md)

[Creating an Application Use Case](https://github.com/dberniell/firestorm-operation/blob/master/docs/GetStarted/UseCases.md)

[Adding Projections](https://github.com/dberniell/firestorm-operation/blob/master/docs/GetStarted/Projections.md)

[Xdebug configuration](https://github.com/dberniell/firestorm-operation/blob/master/docs/GetStarted/Xdebug.md)

## Workflow
[WorkFlow](https://github.com/dberniell/firestorm-operation/blob/master/docs/WorkFlow.md)

## Architecture

![Architecture](https://github.com/dberniell/firestorm-operation/blob/master/docs/architecture.png)

## Implementations

- [x] Environment in Docker
- [x] Command Bus, Query Bus, Event Bus
- [x] Event Store
- [x] Read Model
- [x] Rest API
- [x] Event Store Rest API 
- [x] Swagger API Doc

## Use Cases

#### Area
- [x] Calculate Area
- [x] Get Area by id

![API Doc](https://github.com/dberniell/firestorm-operation/blob/master/docs/swagger.png)

## Stack

- PHP 7.3
- Mysql 8.0

## Project Setup
Build Environment

`make build`

Up environment:

`make start`

Execute tests:

`make phpunit`

Static code analysis:

`make style`

Code style fixer:

`make cs`

Code style checker:

`make cs-check`

Enter in php container:

`make s=php sh`

Disable\Enable Xdebug:

`make xoff`

`make xon`

## PHPStorm integration

PHPSTORM has native integration with Docker compose. That's nice but will stop your php container after run the test scenario. That's not nice when using fpm. A solution could be use another container just for that purpose. But I don't want. For that reason I use ssh connection.

IMPORTANT

> ssh in the container it's ONLY for that reason, if you've ssh installed in your production container, you're doing it wrong... 

Use ssh remote connection.
---

Host: 
- Docker: `localhost`

Port: 
 - `2323`

Filesystem mapping:
 - `{PROJECT_PATH}` -> `/app`

## Missing

- Some unit tests
- Tests e2e
- More comments on classes methods