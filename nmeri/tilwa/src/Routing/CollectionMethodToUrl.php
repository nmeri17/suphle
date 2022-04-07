<?php
	namespace Tilwa\Routing;

	use Tilwa\Routing\Structures\MethodPlaceholders;

	class CollectionMethodToUrl {

		private $caughtPlaceholders = [],

		$pattern = "(
			(_)?#literal to literal i.e. no placeholder in between
			(?<one_word>
				[A-Z0-9]+# one word match
				(
					(
						_{2}[A-Z0-9]+)+# chain as many uppercase characters
						(?<merge_delimiter>[hu])?# double underscores with uppercase letters ending with any of these will be replaced with their counterparts
				)?# compound word
			)
		)?# literal match
		(
			(?:_)(?<is_index>
				index$
			)# should come before next group so placeholder doesn't grab it. must be at end of the string
		)?
		(
			(?:_)?# path segments delimited by single underscores
			(?<placeholder>
				[a-z0-9]+
				(?<is_optional>[O])?
			)
			_?# possible trailing slash before next literal
		)?";

		/* given hypothetical path: PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, clean and return a path similar to a real life path; but still in a regex format so optional segments can be indicated as such
		PATH/$replacement/EDIT/$replacement/EDIT-SAME-OKJ/?($replacement/)?TOMP
		*/
		public function replacePlaceholders (string $routeState, string $replacement):MethodPlaceholders {

			$regexified = preg_replace_callback(
				"/" . $this->pattern . "/x",

			function ($matches) use ( $replacement) {

				$builder = "";
				
				if ($literal = @$matches["one_word"])

					$builder .= $this->handleLiteralMatch($builder, $literal, $matches);

				if ($foundPlaceholder = @$matches["placeholder"])

					$builder .= $this->handlePlaceholderMatch($foundPlaceholder, $matches, $replacement);

				if (isset($matches["is_index"]))

					$builder .= "";

				return $builder;
			}, $routeState);

			if (str_ends_with($regexified, "/"))

				$regexified = trim($regexified, "/") . "/?"; // make trailing slash optional
			
			$result = new MethodPlaceholders($regexified, $this->caughtPlaceholders);

			$this->caughtPlaceholders = [];

			return $result;
		}

		private function handleLiteralMatch (string $builder, string $literal, array $matches):string {

			if ($delimiter = @$matches["merge_delimiter"]) {

				$segmentDelimiters = ["h" => "-", "u" => "_"];

				$literal = implode(
					$segmentDelimiters[$delimiter], explode(
						"__", rtrim($literal, $delimiter) // trailing "h"
					)
				);
			}

			return "$literal/";
		}

		private function handlePlaceholderMatch (string $currentString, string $placeholder, array $matches, string $replacement):string {

			if (!empty($matches["is_optional"])) {

				$placeholder = rtrim($placeholder, "O");

				$currentString .= "?" . // weaken trailing slash of preceding pattern

				"($replacement/?)?";

				$this->caughtPlaceholders[] = $placeholder;
			}

			else {

				$currentString .= "$replacement/";

				$this->caughtPlaceholders[] = $placeholder;
			}

			return $currentString;
		}
	}
?>