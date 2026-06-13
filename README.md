# Suphle PHP Framework (v2 Development)

> An advanced, resilient PHP framework engineered to solve application fragmentation and failure points in data-dense, concurrent systems.

This is the dev-facing project intended for contribution to Suphle itself. Its complete documentation is live at [netlify](https://angry-cray-9c191b.netlify.app).

High-level details about what Suphle's capabilities are and why it was built have been migrated [here](https://dev.to/mmayboy_/introducing-suphle-the-tale-of-a-modern-php-framework-54i9) and [here](https://nmeri.hashnode.dev/a-synopsis-of-the-suphle-framework).

---

## 🦾 Support the v2 Production Sprint (Through September)

Suphle is an independent open-source project moving toward its v2 production milestone. Unlike traditional architectures where a single service failure tanks an entire page, Suphle introduces native, decoupled architectural resilience. It is built to eliminate the boilerplate and fragile glue-code common in enterprise PHP applications:

### Key Architectural Pillars

*   **Native Modular Monoliths:** A first for the PHP ecosystem. Build cleanly decoupled, domain-driven modules that scale independently without the operational complexity of microservices.

*   **Strict Compile-Time Safety (Zero-Tolerance Build Step):** Features a native build step powered by **Psalm** that scans userland code for static errors—completely refusing to boot the application server if errors exist.

*   **Enforced Request Validation:** Severe runtime/compile protection that automatically throws errors if any non-GET action method lacks a dedicated Validator attribute. Built-in constraints that programmatically force developers to define thumbnail generation or image resizing policies before files ever hit storage 

*   **Suphle Flows (Preemptive Background Caching Engine):** A groundbreaking, native caching architecture. When configured on a route (e.g., an index), Suphle automatically extracts entity references (like record IDs) from the outgoing response payload, spins up a background process to load those target destinations individually, and pre-caches them. This eliminates manual cache-warming entirely, making subsequent user navigation instantaneous. Features multiple architectural modes (e.g., ranges, glued IDs) to match diverse traffic patterns.

*   **Attribute-Driven Service Proxies:** Automatically wrap services to natively drive atomic database transactions, isolated fallback degradation, automated monitoring alerts (e.g., Bugsnag), and strict concurrency row-locking.

*   **Native Testing Infrastructure:** Comes bundled with a native testing library built directly on top of PHPUnit to make testing complex decoupled modules seamless.

*   **Modern V2 Productivity Engine:** Includes a native **Auto-Documentation Generator**, a brand new CLI route list command, native WebSockets, high-performance route caching, and flexible action method builders..

Version 2 strips away legacy inheritance dependencies, overhauling this engine into a modern declarative system while introducing high-performance route caching and native WebSocket integrations.

---

### 🚀 Sponsorship Tiers & Milestone Roadmap

We are running a hard sprint through **September** to finalize test coverage, revamp documentation UI, and launch two real-world pipeline applications to showcase these capabilities. Choose a tier that matches your scale:

| Tier | Investment | Target Backer | Core Benefits |
| :--- | :--- | :--- | :--- |
| **🏆 Premier Enterprise Partner** | **$2,500** *(One-Time)* | Mid-to-Large Corps / Hosting Providers | Ultimate visibility. Large logo at the absolute top of this README, the official documentation UI header, and all v2 release announcements. |
| **⚡ Core Infrastructure Sponsor** | **$1,000** *(One-Time)* | Dev Shops / Tech Startups | Prominent logo placement on this README, the documentation site footer, and dedicated attribution in the launch release notes. |
| **🛡️ Elite Backer** | **$500** *(One-Time)* | Senior Consultants / Independent Engineers | Dedicated medium logo/text-link attribution on the project website and prominent recognition in our foundational `SPONSORS.md` index. |
| **🔬 Architecture Insider** | **$100** *(One-Time)* | Senior Developers / Architects | Code-level access. Read-access to the private repositories of our two real-world pipeline showcase applications *while they are being built* to study Suphle's design patterns in production. |
| **🌱 Ecosystem Booster** | **$25** *(One-Time)* | Open-Source Enthusiasts | Permanent name credit within the repository's foundational `SPONSORS.md` file. |

### bank details 💳
Account holder : nmeri alphonsus 
ACCOUNT NUMBER : 42723560 
BANK NAME : Clear Junction Limited

*Note: This is our sole, verified gateway for funding. To prevent security friction or identity confusion, please do not engage with unverified third-party outreach representatives.*

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
