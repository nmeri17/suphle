# Suphle PHP Framework (v2 Development)

> An advanced, resilient PHP framework engineered to solve application fragmentation and failure points in data-dense, concurrent systems.

---

## 🦾 Support the v2 Production Sprint (Through September)

Suphle is an independent open-source project moving toward its v2 production milestone. Unlike traditional architectures where a single service failure tanks an entire page, Suphle introduces native, decoupled architectural resilience.

### Our Flagship Engineering Feature: Attribute-Driven Service Proxies
With a simple attribute declaration, Suphle natively wraps your services in an intelligent proxy to handle complex high-concurrency problems out of the box:
*   **Atomic Transactions:** Automated database rollbacks on any method intercept failure.
*   **Isolated Degradation:** Granular, configurable fallback responses so one failing downstream service never crashes the user experience.
*   **Contextual Alerting:** Instant broadcast integration (e.g., Bugsnag) the moment an anomaly triggers.
*   **Concurrency Control:** Native soft and hard row/model locking to completely eliminate race conditions.

Version 2 strips away legacy inheritance dependencies, overhauling this engine into a modern declarative system while introducing high-performance route caching and native WebSocket integrations.

---

### 🚀 Sponsorship Tiers & Milestone Roadmap

We are running a hard sprint through **September** to finalize test coverage, revamp documentation UI, and launch two real-world pipeline applications to showcase these capabilities. Choose a tier that matches your scale and back a next-gen PHP ecosystem:

#### 🏢 Corporate & Enterprise
*   **$2,500 — Premier Enterprise Partner**
    Ultimate visibility. Your large organization logo sits at the absolute top of this README, the official documentation UI header, and all v2 release announcements.
*   **$1,000 — Core Infrastructure Sponsor**
    Prominent logo placement on this README, the documentation site footer, and dedicated attribution in the launch release notes.

#### 🛠️ Individual & Independent Engineers
*   **$500 — Elite Backer**
    Designed for senior independent consultants and elite developers funding high-level R&D. Permanent text-link attribution on the project website and recognition in our `SPONSORS.md` index.
*   **$100 — Architecture Insider**
    Get code-level visibility. Includes read-access to the private repositories of our two real-world pipeline showcase applications *while they are being built*, providing an architectural masterclass on how Suphle's proxies function in production.
*   **$25 — Ecosystem Booster**
    Permanent name credit within the repository's foundational `SPONSORS.md` file.

### 💳 [Click Here to Process Your One-Time Sponsorship via Flutterwave](https://flutterwave.com/donate/3hysvmaxgfu2)

*Note: This is our sole, verified gateway for funding. To prevent security friction or identity confusion, please do not engage with unverified third-party outreach representatives.*

Suphle Framework
==========================

## Introduction

This is the dev-facing project intended for contribution to Suphle itself. Its complete documentation is live at [netlify](https://angry-cray-9c191b.netlify.app).

High-level details about what Suphle's capabilities are and why it was built have been migrated [here](https://dev.to/mmayboy_/introducing-suphle-the-tale-of-a-modern-php-framework-54i9) and [here](https://nmeri.hashnode.dev/a-synopsis-of-the-suphle-framework).

## Testing

Typically, from your system's web folder:

```bash

composer create-project nmeri/suphle AwesomeProject

cd AwesomeProject

composer test -- "/path/to/AwesomeProject/tests"
```

The tests interact with the database, and would thus expect to find an active MySQL connection such as that gotten from running a WAMP equivalent. The server can be configured to use anything else, but for the purpose of this demo, we simply use MySQL.

Each of the modules contain an `.env` with the following entries:

```
DATABASE_NAME = suphle
DATABASE_USER = root
DATABASE_PASS = 
DATABASE_HOST = localhost
```

The database name is not important. All the command needs is for the credentials to match your local MySQL server, for migrations to be run.

When executed as is, the tests will leave behind seeded data. For the database to self-destruct after it's done executing, we have to supply the configuration schema.

```bash

composer test -- "/path/to/AwesomeProject/tests" -c=/path/to/AwesomeProject/phpunit.xml
```

### Parallel testing

The commands shown earlier will execute the tests synchronously, which may not be the most optimized for your machine. Those using systems with multiple cores can take advantage of concurrent testing and instead execute tests in parallel.

```bash

composer parallel-test -- "/path/to/AwesomeProject/tests" --processes=5
```

Above, we're enforcing 5 processes; however, when left blank, the runner will determine the optimum number of processes to use based on amount of cores available. Hence, this would equally work:

```bash

composer parallel-test -- "/path/to/AwesomeProject/tests"
```

As with using this runner in development, note that it swallows all PHPUnit output. Thus, if the test invocation fails before completion, you may require a synchronous run to understand what went wrong.

### Browser access

All interaction with the Framework should be conducted through tests and by extension, the command line. Those unaccustomed to reading the source code/tests or running tests, or those who are impatient and would prefer seeing something on the browser, should fire up the Roadrunner server:

```bash

php suphle_cli server:start Modules --insane  --ignore_static_correct
```

Then, visit any of the routes available at:

- http://localhost:8080/
- http://localhost:8080/segment
- http://localhost:8080/module-three/{any_integer}

They don't require any database connection and only demonstrate the relatively basic ability to route incoming requests to an attached action handler, taking higher-level constraints like modules and prefixing into account.

If you're window shopping, a *sort of* example application resides in the `tests/Mocks` folder. Emphasis is laid on "sort of" since `ModuleOne` there, is for testing majority of the framework's feature set and doesn't necessarily reflect what you'd expect from a real life Suphle module.

## Contributing to the Starter project

The [Starter project](https://github.com/nmeri17/suphle-starter) is the user-facing arm intended for bootstrapping fresh Suphle projects. If you have cause to contribute to it, it's much more convenient to install both side by side, such that over the course of development, your updates to this core project will reflect on your Starter installation.

This project must be installed, first.

```bash

composer create-project nmeri/suphle

git clone https://github.com/nmeri17/suphle-starter.git
```

Afterwards, the Starter is to derive its parent project from your local installation. Navigate to the `composer.json` of the Starter project and add the following entry:

```json

"repositories": [
	{
		"type": "path",
        "url": "../suphle"
    }
],
"minimum-stability": "dev"
```

Now, instruct Composer to interpret the local installation as parent, using the install command:

```bash

cd suphle-starter

composer install
```

Now, all is set! Checkout a new branch to implement your amazing feature. If you need to interact with the Roadrunner server as well, fetch its binary like so:

```bash

cd vendor/bin

rr get-binary
```

## Where to start contributing

The recommended place to render assistance is the existing issues with the [`help-wanted`](https://github.com/nmeri17/suphle/issues?q=is%3Aissue+is%3Aopen+label%3A%22help+wanted%22) label, which would facilitate us reaching our initial goals. As I do value your time, it's advisable for new additions outside that list to first be discussed on a new issue, and commissioned for implementation. Please see the Contribution guide for more details.

## Security

[Security Policy](SECURITY.md)
