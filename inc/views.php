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
	if ($rule == '') {
		return array();
	}

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
 * @param $selection designate what field to get from SQL table. Warning: it is not escaped.
 * @return a PDO-ready query and the binding array.
 *
 * TODO: Refactor me!!!
 */
function rule2sql($rule, $selection) {
	$ast = parse_rule($rule);
	array_push($ast, array('by', 'by', ''));
	$query = "SELECT $selection FROM entries E";
	
	$var_array = array();
	$subquery = '';
	$state = 'select';
	foreach ($ast as $word) {
		switch ($state) {
			case 'select':
				$tag_id = "(SELECT id FROM tags WHERE name = ?)";
				switch (strtolower($word[1])) {
					case '+':
						switch ($word[2]) {
							case '$all':
								$subquery = '';
								break;

							default:
								if ($subquery != '') {
									$subquery = "($subquery) OR ";
								}
								$subquery .= "EXISTS (SELECT tag_id FROM tags_entries WHERE tag_id = $tag_id AND entry_id = E.id)";
								array_push($var_array, $word[2]);
						}
						break;

					case '-':
						switch ($word[2]) {
							case '$all':
								$subquery = '1=0'; // A little bit hacky
								break;

							default:
								if ($subquery != '') {
									$subquery = "($subquery) AND ";
								}
								$subquery .= "NOT EXISTS (SELECT tag_id FROM tags_entries WHERE tag_id = $tag_id AND entry_id = E.id)";
								array_push($var_array, $word[2]);
						}
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
				$bind = false;
				switch ($word[2]) {
					case '$pubDate':
						$tag_count = "E.pubDate";
						break;

					default:
						$tag_id = "(SELECT id FROM tags WHERE name = ?)";
						$tag_count = "(SELECT COUNT(*) FROM tags_entries WHERE tag_id = $tag_id AND entry_id = E.id)";
						$bind = true;
				}
				switch (strtolower($word[1])) {
					case '+':
						if ($subquery != '') {
							$subquery .= ", ";
						}
						$subquery .= "$tag_count ASC";
						if ($bind) {
							array_push($var_array, $word[2]);
						}
						break;

					case '-':
						if ($subquery != '') {
							$subquery .= ", ";
						}
						$subquery .= "$tag_count DESC";
						if ($bind) {
							array_push($var_array, $word[2]);
						}
						break;

					case 'by':
						if ($subquery != '') {
							$query .= " ORDER BY $subquery";
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


