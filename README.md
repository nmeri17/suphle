The most important thing for me at the moment is indication of interest from the community. Just help by starring/watching the repo, opening an issue, or sharing it with your friends

<!-- ## Installation-->

## Testing
Clone project. Run `composer install && cd vendor/bin && rr get-binary && phpunit ../../tests`
It requires an active mysql connection

This is the dev-facing project intended for contribution of Suphle itself. The user-facing project can be found at https://github.com/nmeri17/suphle-starter. It requires unarchiving the roadrunner binary and running with the in-folder configuration 

## Introduction

**"What exactly is this?"**

Suphle is an opinionated PHP framework for enterprises and SAASes to build anything from command line applications, to robust APIs, to server rendered web apps, without compromising on the high fidelity of SPAs. Because the line between the last two in Suphle is nonexistent, you can virtually go from creating the back end of a web app to having a versioned, documented API in a heartbeat. Aside that, it guarantees your app will never break until you want it to; be it after product release or subsequent maintenance. Then, it has first class support for modular monoliths that can either be exhumed into their own microservice or used as template for extending modules to reuse

**"Interesting. So, it's not a jamboree for the author to understand how routing requests to controllers work?"**

Suphle is anything but that. Its intended audience is enterprises with a fleet of developers working hands-on to create solutions to positively impact users relying on its efficiency. It starts to shine during rapid application development or in environments experiencing feature creep

Whichever is the case, your resulting program should not compromise on being an architectural masterpiece – an art form in itself

**"Copy that. Tbf the overview sound great, but does it contain what my current framework does?"**

What are the regular culprits you expect at any serious back end framework? Routing, container, authentication, authorisation, events, DAL, testing, middleware, controllers. The rest are nice to haves: task scheduling, sessions, http, cache, templating, validation, exception handling, CLI. Remind me if I missed anything.
Suphle has all that, and more.

**"Bold statement. What could possibly be more than those?"**

Components themselves arguably don't make a framework. That is why indie developers can get away with cobbling random libraries that fit their needs together instead of starting with the gigantic household names. The first version of suphle handled middleware, authentication and routing in one class <=500 LOC. It was functionally, a framework that demonstrated the possibility of not formalizing components.

What has been done differently this time around is to 
1. Formalize the components to better accommodate their now larger capacity and recognise diverging responsibilities. 
1. More importantly, to take the spotlight away from components in and of themselves, and instead, point it onto the larger picture. This involves the following expectations:

1) Domain maintenance as perceived by business department
2) Seamless end user experience
3) Testability
4) Project evolution without strict reliance on:

i) Collaboration guidelines or
ii) Developer competence

5) Better managed errors

One of the factors that best exemplifies insignificance of components is that majority of suphle is democratised by being composed of contracts. There is no little to no vendor lock-in, as long as your component of choice fulfills expected contract

The main takeaway here is to repurpose those known components toward these goals or create new ones when the existing ones can be improved upon. While frameworks number in the dozen, core libraries in even greater abundance, suphle can be thought to take things a step further to bridge the gap left behind. Below, we look at real life examples of the bullet points outlined above:

- Internal feature release and archiving features
- Breaking unchanged parts of the code by modifying dependencies cuz No clearcut dependency chain, and certainly, no integration tests
- Requiring a full stack developer to work on our UIs (before they were ported to SPAs) 
- Entire pages crashing because of an error affecting an insignificant page segment/data node
- Waiting for negative customer feedback before knowing something went wrong, then wrangling error logs
- Sacrificing man hours after giving up on SSR. A front end dev was hired. The back end had to be duplicated into an API with slightly diverging functionality.
- Chasing and duplicating state and errors between the SPA and back end, for the sole purpose of a SPA-ey feel/fidelity
- Cluelessness when our callback URLs broke in transit
- API documentation, testing, breaking clients thanks to indiscriminate updates since there was no versioning
- Irresponsible practices such as requests without validators, fetching all database columns, improper or no model authorisation, dumping whatever we had nowhere else to put in middlewares, gigantic images ending up at the server, models without factories, stray entities floating about when their owner model gets deleted, not guarding negative integers from sneaking in from user input, I could go on
- Corrupted data when operations separated by logic, that should've been inserted together gets broken in between
- Gnarly merge conflicts among just a handful contributors 

If you or your team are struggling with any of these, you're in luck! They are some of the problems Suphle was built to solve

**"Haha! I am howling right now. Tbh, giving it a try is really tempting at this point, but I'm a little sceptical given its young age and obvious lack of libraries. Might check back, later?"**

![judge-me-by-my-size](judge-me-by-my-size-do-you.jpg)

Yes, it's brand new, but it doesn't lack any more libraries than laravel. The bridge component has adapters well defined to encourage development of plugins for other frameworks. But currently, the laravel adapter enables you access your laravel service providers, container bindings, routes, config, helpers, migrations, in suphle without the friction that should've come from combining two drastically contrasting frameworks. Mind you, each of them is active on an opt-in basis. Suphle doesn't operate an exclusive us vs them model. It's an inclusive us AND them. Don't throw away your existing apps or port them. 

## "Are you kidding me?!"

Thankfully, not. Matter of fact, There's an appendix in the docs that go into detail about the ins and outs of automating your tests, especially intended for those who don't know how to, tried and failed to wrap their heads /workflow around TDD, or who don't really think testing is important – that demographic of developers. It even includes articles for progressive development and inclusion of new features, replacing old ones etc.

"..."

"..."

![shut-up-and-take-my-money](shut-up-take-my-money.gif)

Ha! I think you mean "take all my existing and future codebases". <!-- So, head straight over to the documentation at [suphle.com](docs/v1/quick-start). However, if you mean what you said, you can donate to support continuous development of the project through these channels -->

Documentation in progress over at https://github.com/nmeri17/suphle-docs/.

I haven't defined a formal set of contribution rules yet using csfixer and editor-config since no one has expressed interest yet. Let me know if the prevalent coding style isn't glaring.

## Security

[Security Policy](SECURITY.md)
