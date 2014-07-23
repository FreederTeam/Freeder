<?php
/**
 * Freeder
 * -------
 * @file views.php
 * @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 * @brief Functions to handle views system.
 * @see RFF4
 */


class ParseError extends Exception { }

/**
 * Parse a rule word as a prefix and a content.
 * Throws a `ParseError` if something goes wrong.
 * @see RFF4
 * @param $word is the raw text word.
 * @return a word abstract tree which is an array composed of three strings,
 * namely (1) the whole word, (2) the prefix, (3) the content.
 */
function parse_word($word) {
	$prefixe = '\\+|-|BY';
	if(!preg_match('/^('.$prefixe.')(.*)$/i', $word, $matches)) {
		throw new ParseError("Unknown prefix in word `$word`");
	}

	return $matches;
}


/**
 * Parse a rule according to view rule format.
 * Throws a `ParseError` if something goes wrong.
 * @see RFF4
 * @param $rule is the raw text rule.
 * @return a rule abstract syntax tree which is an array of word abstract trees.
 */
function parse_rule($rule) {
	$tokens = explode(' ', $rule);
	$words = array();

	$word = '';
	foreach ($tokens as $token) {
		// If next space is escaped, merge it with the next token
		if (substr($token, -1) == '\\') {
			$word .= ' ';
			continue;
		}

		array_push($words, parse_word($word));

		$word = '';
	}

	if ($word != '') {
		throw new ParseError("Unexpected trailing backslash in rule `$rule`");
	}

	return $rule;
}

