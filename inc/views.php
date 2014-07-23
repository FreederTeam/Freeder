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
	$ast = array();

	$word = '';
	foreach ($tokens as $token) {
		$word .= $token;

		// If next space is escaped, merge it with the next token
		if (substr($token, -1) == '\\') {
			$word = substr($word, 0, -1) . ' ';
			continue;
		}

		array_push($ast, parse_word($word));

		$word = '';
	}

	if ($word != '') {
		throw new ParseError("Unexpected trailing backslash in rule `$rule`");
	}

	return $ast;
}

/**
 * Build a SQL query from a view rule.
 * @param $rule is the rule as raw text.
 * @return a PDO-ready query and the binding array.
 *
 * TODO: Refactor me!!!
 */
function rule2sql($rule) {
	$ast = parse_rule($rule);
	$query = 'SELECT entry_id FROM tags_entries';
	
	$var_index = 0;
	$var_array = array();
	$subquery = '';
	$state = 'select';
	foreach ($ast as $word) {
		switch ($state) {
			case 'select':
				$tag_id = "(SELECT id FROM tags WHERE name = ':" . $var_index . "')";
				switch(strtolower($word[1])) {
					case '+':
						if ($subquery != '') {
							$subquery = "(" . $subquery . ") OR ";
						}
						$subquery .= "tag_id = $tag_id";
						$var_index++;
						array_push($var_array, $word[2]);
						break;

					case '-':
						if ($subquery != '') {
							$subquery = "(" . $subquery . ") AND ";
						}
						$subquery .= "tag_id != $tag_id";
						$var_index++;
						array_push($var_array, $word[2]);
						break;

					case 'by':
						if ($subquery != '') {
							$query .= " WHERE $subquery";
						}
						$subquery = '';
						$state = 'order';
						break;

					default:
						throw new ParseError("Unknown prefix `$word[1]`");
				}
				break;

			case 'order':
				$tag_count = "(SELECT COUNT(*) FROM tags WHERE name = ':" . $var_index . "')";
				switch(strtolower($word[1])) {
					case '+':
						if ($subquery != '') {
							$subquery .= ", ";
						}
						$subquery .= "$tag_count ASC";
						$var_index++;
						array_push($var_array, $word[2]);
						break;

					case '-':
						if ($subquery != '') {
							$subquery .= ", ";
						}
						$subquery .= "$tag_count DESC";
						$var_index++;
						array_push($var_array, $word[2]);
						break;

					case 'by':
						if ($subquery != '') {
							$query /= " ORDER BY $subquery";
						}
						$status = 'done';
						break;

					default:
						throw new ParseError("Unknown prefix `$word[1]`");
				}
				break;
		}
	}

	return array($query, $var_array);
}


