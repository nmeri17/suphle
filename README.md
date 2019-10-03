# NOTES  
Does not ship with a default ORM.


# TODO  
-    Write automated tests
-    Write CLI for programmatic creation of data objects for templating engine

# Installation

# Who needs this?
Tilwa wasn't built to carry every single developer along. It doesn't promise an exhaustive panacea to your every imaginable need in web development. If you're coming from such framework, you will be expecting the notables -- security concerns, ORMs, application containers and auth. We understand not every application requires these. Instead, we guarantee a very low learning curve in exchange for intuitive routing across web pages and api endpoints, templating, caching, administration panel.

# File structure
In seconds, your project setup puts you well on your well on your way to a robust, flexible application

# Where the magic happens
You're mostly going to be extending the Model or PostController classes, depending on your specific need.

Template engine doesn't supports comments or front end frameworks with matching tags

# Template engine

## Data driven parser against component blocks

The objects in the first step are for each component. Step 1's size equals number of children on the root component.
*Note: Multiple children are labeled internally (see example)*

Step 2 determines each block in this specific context of component. For regular cases, step3 exposes the data itself. 

*Permissible structure per root component block:*

```
[
	[ 0 => [['foo' => 'bar']]
	],
	[1 => 
		0 => ['basicNest' => [ // data row
			['foo' => 'john'],
			['foo' => 'doe']
		]],
		1 => ['complexNest' => [
			[[ // select
				['option' => 'mary'],
				['option' => 'sue']
			]],
			[[ // list
				['li' => 'bar'],
				['li' => 'baz']
			]]
		]]
	]
]
```

The above will match a root component with two child blocks. Note the positioning of the child block labels (internal). String keys will be numerically indexed.

*Beware:* The above sample data structure is for educational purposes. Review your markup structure if your needs begin to demand you pass raw HTML from your code.

Then it gets interesting.


# Nested and Grouped structures
Components can be nested to accommodate markup such as tables, lists and combo boxes. Multiple components can equally be grouped into one rendering block. This lends dimension 2 to carrying sub arrays of data that must correspond to its designated spot in the view group.
The general rule of thumb for group structure is to dump a double layer numeric array at the index a flat value would've been. This double layer in turn houses the rows of iterable data.

Textual data and variables can stand between grouped data; provided the variable values reside in the root dimension of that context's data set.

# Debug
Any skipped view or group indicates a missing data set. In order to output a view in the event of no backing data, at least one key in that set must be set to an empty string.

# Structural requirements:
	- As from dimension 2, strings can be passed as values (rather than data rows), provided a matching placeholder at that dimension is present

	- The penultimate container must be numerically indexed
