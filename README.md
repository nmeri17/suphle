Details about what Suphle's capabilities and why it was built have been migrated [here](https://dev.to/mmayboy_/introducing-suphle-the-tale-of-a-modern-php-framework-54i9). This repo is the dev-facing project intended for contribution of Suphle itself. Its user-facing project can be found at https://github.com/nmeri17/suphle-starter.

## Testing

Project can either be installed using Composer,

```bash

composer require nmeri17/suphle
```

Or, can simply be cloned -- in which case, we'd have to manually install dependencies:

```bash

git clone https://link/to/repo.git

cd cloned/path

composer install
```

Either way, after installation, initiation and test can be triggered using the following command:

```bash

php suphle_cli project:contribute_test
```

For it to run successfully, both an active Internet and MYSQL connection must be available. The former is for downloading the server binary while the latter is for running database migrations. The default modules contain an `.env` with the following entries:

```
DATABASE_NAME = suphle
DATABASE_USER = root
DATABASE_PASS = 
DATABASE_HOST = localhost
```

That database must be created; otherwise, an alternative must be provided.

All interaction with the Framework should be conducted through tests and by extension, the command line. Those unaccustomed to reading the source code/tests or running tests, or those who are impatient and would prefer seeing something on the browser, should fire up the Roadrunner server:

```bash

php suphle_cli server:start --rr_config_path="/project/path/test-rr.yaml" --insane
```

Then, visit any of the routes available at:

- http://localhost:8080/
- http://localhost:8080/segment
- http://localhost:8080/module-three/{any_integer}

They don't require any database connection and only demonstrate the relatively basic ability to route incoming requests to an attached action handler, taking higher-level constraints like modules and prefixing into account.

Documentation in progress over at https://github.com/nmeri17/suphle-docs/.

I haven't defined a formal set of contribution rules yet using csfixer and editor-config since no one has expressed interest yet. Let me know if the prevalent coding style isn't glaring.

## Security

[Security Policy](SECURITY.md)