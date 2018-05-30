HOW TO TEST
===========

### 1. Grammar Check

    $ find src -name '*.php' -exec php -l '{}' \;


### 2. Unit Tests

    $ vendor/bin/phpunit tests
