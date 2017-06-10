Patroleum watches your home, business or enterprise live.

DESCRIPTION

App works with 2 DB: 1 for the app, another for the mail storage, needed to retrive camera alerts

REQUIREMENTS

Project requires:
- MySql
- memcache installed with PHP 5.6+
- php_mailparse ext installed
- php_curl ext installed
- ffmpeg
- php_zmq (sebsockets) 


INSTALLATION

- Install ffmpeg and dependences (used to proxy camera streams and record all viewed feeds)
- Install composer and bower
- Adjust composer global requirement:
    composer global require "fxp/composer-asset-plugin"
- Adjust /common/config/params.php MAIL DB credentials
- Initialize Yii2 and Adjust local configs
- Run migraion: 
    ./yii migrate

- Add to email trigger ./yii cron/check-alert-email
- Add to cron ./yii cron/check-camera-status every minute to check camera statuses
- Add to cron ./yii cron/check-dropped-alerts   every 15 seconds do this check in cron

- Start WS server ./yii perform/start-ws-server

DIRECTORY STRUCTURE
-------------------

common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides