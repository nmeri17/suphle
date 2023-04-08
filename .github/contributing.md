# Contributing

Your interest to take time out of your schedule to invest in what makes Suphle tick is quite delighful. However, the breathtaking improvements you are bringing on board will be better received if it meets a few non-negotiable criteria. This is not to say your modalities are inaccurate, but it's an attempt to ensure a consistent, homogenic fundamental code style.

1. First and foremost, there is no need for includes or requires anywhere.

1. Composition/aggregation should be favored over traits. Traits should only be used when callers don't require unique instances of resulting object or for classes with different parents but intersecting functionality -- not a frequent occurence.

1. "Magical" behaviour should be kept at arm's length. If you have to decide between creating a fairly attractive API that performs arcane actions obscuring intuitive use of the language, sacrifice the beautiful API. It doesn't pay off in the long run when new persons are battling to grasp how things work.
Alternatively, propose such API in an issue if you're absolutely certain about its essence.

1. Standalone functions should be avoided at all costs. They either violate the include rule or live in global scopes that bring ridicule to this great language.

1. PHP 8 implements attributes as a core language feature, but please, avoid the temptation to support route definition in files. While it provides an opportunity for co-locating endpoints and their corresponding handlers, it quickly becomes undesirable when the need for tracing an endpoint arises.

1. Prefer creating DTOs over using associative arrays.

1. Method arguments should only be typed to one class or `?Type`. They cannot receive mysterious "mixed" types, union or intersection types where the parameter must be determined prior to logic execution.

1. Unless where arguments can only be derived at definition point, fully qualified class names should be supplied instead of concrete instances.

1. New inclusion or modification must be covered by tests.

These are philosophical coding guidelines. A more formal set of rules is enforced by a PHP-CS-FIXER configuration at the project root that should be included in an automated review of your pull request.

That's it. If you are in agreement, you can either lookup [existing issues](https://github.com/nmeri17/suphle/issues?q=is%3Aissue+is%3Aopen+label%3A%22help+wanted%22), or [create a new one](https://github.com/nmeri17/suphle/issues/new?assignees=&labels=&template=feature_request.md&title=) to discuss what you intend to add.

Thanks again.
