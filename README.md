Details about what Suphle's capabilities and why it was built have been migrated [here](https://dev.to/mmayboy_/introducing-suphle-the-tale-of-a-modern-php-framework-54i9). This repository is the dev-facing project intended for contribution to Suphle itself. Its user-facing project can be found at https://github.com/nmeri17/suphle-starter.

## Installation

Project can either be installed using Composer,

```bash

composer create-project nmeri/suphle AwesomeProject

cd AwesomeProject
```

## Testing

Project initiation and test can be triggered using the following command:

```bash

php suphle_cli project:contribute_test --phpunit_flags="-c=/project/path/phpunit.xml"
```

For it to run successfully, both an active Internet and MYSQL connection must be available. The former is for downloading the server binary while the latter is for running database migrations. The default modules contain an `.env` with the following entries:

```
DATABASE_NAME = suphle
DATABASE_USER = root
DATABASE_PASS = 
DATABASE_HOST = localhost
```

Supplying a configuration schema will cause the database to self-destruct after it's done executing, thus the database name is not important. All the command needs is for the credentials to match your local MYSQL server, for migrations to be run.

### Parallel testing

The command shown earlier will execute the tests synchronously, which may not be the most optimized for your machine. Those using systems with multiple cores can take advantage of concurrent testing and execute tests in parallel. Just as the `phpunit_flags` accepts options to forward to the underlying PHPUnit process, the `paratest_flags` should be used to send relevant options to the Paratest runner.

A basic indicator would look like this:

```bash

php suphle_cli project:contribute_test --phpunit_flags="-c=/project/path/phpunit.xml" --paratest_arg="--processes=5"
```

Above, we're enforcing 5 processes, however, when left blank, the runner will determine the optimum number of processes to use based on amount of cores available. Hence, this would equally work:

```bash

php suphle_cli project:contribute_test --phpunit_flags="-c=/project/path/phpunit.xml" --paratest_arg
```

As with using this runner in development, note that it swallows all PHPUnit output. Thus, if the test invocation fails before completion, you may require a synchronous run to understand what went wrong.

### Browser access

All interaction with the Framework should be conducted through tests and by extension, the command line. Those unaccustomed to reading the source code/tests or running tests, or those who are impatient and would prefer seeing something on the browser, should fire up the Roadrunner server:

```bash

php suphle_cli server:start --rr_config_path="/project/path/test-rr.yaml" --insane
```

Then, visit any of the routes available at:

- http://localhost:8080/
- http://localhost:8080/segment
- http://localhost:8080/module-three/{any_integer}

They don't require any database connection and only demonstrate the relatively basic ability to route incoming requests to an attached action handler, taking higher-level constraints like modules and prefixing into account.

If you're window shopping, a *sort of* example application resides in the `tests/Mocks` folder. Emphasis is laid on "sort of" since `ModuleOne` there, is for testing majority of the framework's feature set and doesn't necessarily reflect what you'd expect from a real life Suphle module.

Documentation is at [Suphle.com](https://suphle.com).

## Where to start contributing

The recommended place to render assistance is the existing issues with the [`help-wanted`](https://github.com/nmeri17/suphle/issues?q=is%3Aissue+is%3Aopen+label%3A%22help+wanted%22) label, which would facilitate us reaching our initial goals. As I do value your time, it's advisable for new additions outside that list to first be discussed on a new issue, and commissioned for implementation. Please see the Contribution guide for more details.

## Security

[Security Policy](SECURITY.md)
