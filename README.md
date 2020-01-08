# Visial Web

The main purpose of this application is to allow users to create financial simulations about their social building from year N to year N+50.

**PHP w/ PHPUnit**

[![coverage report](https://git.thecodingmachine.com/tcm-projects/visial-web/badges/develop/coverage.svg)](https://git.thecodingmachine.com/tcm-projects/visial-web/commits/develop)

## Setup

Windows is not supported, period.

On MacOS, update your `/etc/hosts` file with:

```
127.0.0.1   visial-web.localhost
127.0.0.1   phpmyadmin.visial-web.localhost
```

Next run the following commands:

```bash
$ make up
```

Once the database is initialized, open a terminal in the `web` service with:

```bash
$ make bash
```

And build the frontend application:

```bash
$ yarn dev
```

**Note:** there is also a watcher available (`yarn watch`).

And finally load some test data:

```bash
$ php bin/console fixtures:load:test
```

**Before each commit, don't forget to run some static analyzers with:**

```bash
$ composer csfix && composer cscheck && composer phpstan
```

**Also run PHP Unit tests with:**

```bash
$ php bin/phpunit --coverage-text
```

**NOW READ THE REST OF THIS README!**

## Available commands

```bash
$ make up
```

Starts the local stack.

---

```bash
$ make down
```

Stops the local stack.

---

```bash
$ make bash
```

Opens a terminal inside `web` service container.

## Working with the database

**Note:** all the following commands must be executed in the `web` service (`make bash`).

### Updating the database STRUCTURE and/or adding REAL DATA

First, create a `patch`:

```bash 
$ php bin/console doctrine:migrations:generate
```

Once your `patch` is done, execute it:

```bash
$ php bin/console doctrine:migrations:migrate
```

Finally regenerate the `Daos` and `Models` with:

```bash
$ php bin/console tdbm:generate
```

### Adding TEST data

You may add some test data using `fixtures`.

The command `App\Fixtures\LoadTestCommand` allow you to add a class which implements the `App\Fixtures\Fixture` interface.
For instance:

```php
public function __construct(RoleDao $roleDao, UtilisateurDao $utilisateurDao)
{
    parent::__construct();
    $this->fixtures = [
        new CreateSuperAdministrateur($roleDao, $utilisateurDao),
        // add your instance here!
    ];
}
```

You may then load your test data using:

```bash
$ php bin/console fixtures:load:test
```
