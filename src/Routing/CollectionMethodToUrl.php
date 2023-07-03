<?php

namespace Suphle\Routing;

use Suphle\Routing\Structures\MethodPlaceholders;

class CollectionMethodToUrl
{
    final public const REPLACEMENT_TYPE_PLACEHOLDER = "placeholder",

    PLACEHOLDER_IDENTIFIER = "[a-z0-9]+";

    protected array $caughtPlaceholders = [];

    protected string $pattern;

    public function __construct()
    {

        $this->setPattern();
    }

    private function setPattern(): void
    {

        $this->pattern = "(
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
				(?:_?_)(?<is_index>
					index$
				)# should come before next group so placeholder doesn't grab it. must be at end of the string. the preceding underscore is intended to match collections combining indexes with prefixes
			)?
			(
				(?:_)?# path segments delimited by single underscores
				(?<placeholder>".
                self::PLACEHOLDER_IDENTIFIER .
            ")
				_?# possible trailing slash before next literal
			)?";
    }

    /* given hypothetical path: PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, clean and return a path similar to a real life path; but still in a regex format so optional segments can be indicated as such
    PATH/$replacement/EDIT/$replacement/EDIT-SAME-OKJ/?($replacement/)?TOMP
    */
    public function replacePlaceholders(string $collectionMethod, string $replacement): MethodPlaceholders
    {

        $regexified = preg_replace_callback(
            "/" . $this->pattern . "/x",
            function ($matches) use ($replacement) {

                $builder = "";

                if (isset($matches["one_word"]) && $literal = $matches["one_word"]) {

                    $builder .= $this->handleLiteralMatch($builder, $literal, $matches);
                }

                if (isset($matches["placeholder"]) && $foundPlaceholder = $matches["placeholder"]) {

                    $builder .= $this->handlePlaceholderMatch($foundPlaceholder, $matches, $replacement);
                }

                if (isset($matches["is_index"])) {

                    $builder .= "";
                }

                return $builder;
            },
            $collectionMethod
        );

        if (str_ends_with($regexified, "/")) {

            $regexified = trim($regexified, "/") . "/?";
        } // make trailing slash optional

        $result = new MethodPlaceholders($regexified, $this->caughtPlaceholders);

        $this->caughtPlaceholders = [];

        return $result;
    }

    private function handleLiteralMatch(string $builder, string $literal, array $matches): string
    {

        if ($delimiter = @$matches["merge_delimiter"]) {

            $segmentDelimiters = ["h" => "-", "u" => "_"];

            $literal = implode(
                $segmentDelimiters[$delimiter],
                explode(
                    "__",
                    rtrim($literal, $delimiter) // trailing "h"
                )
            );
        }

        return "$literal/";
    }

    private function handlePlaceholderMatch(string $placeholder, array $matches, string $replacement): string
    {

        $segment = "";

        $replaceWithPlaceholder = $replacement == self::REPLACEMENT_TYPE_PLACEHOLDER;

        if ($replaceWithPlaceholder) {

            $replacement = $placeholder;
        }

        $segment .= "$replacement/";

        $this->caughtPlaceholders[] = $placeholder;

        return $segment;
    }

    /**
     * @param {tokenizedUrl} Something like
     * SEGMENT/id/SEGMENT2/?(id2/?)?
     *
     * Notice the full method is transformed into a url but still retains its original placeholders
     *
     * @return Array [SEGMENT, id, SEGMENT2, id2]
    */
    public function splitIntoSegments(string $tokenizedUrl): array
    {

        preg_match_all("/(?:([\w-]+)\/)/", $tokenizedUrl, $matches);

        return $matches[1];
    }
}
