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
	if(!preg_match('/^(\\+|-|BY)(.*)$/i', $word, $matches)) {
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
	$rem = $rule;
	$ast = array();

	while ($rem != '') {
		if (!preg_match('/^((?:[^ \\\\]|\\\\\\\\|\\\\ )*)(?: +(.*)|$)/', $rem, $matches)) {
			throw new ParseError("Error in rule `$rule` near `$rem`");
		}

		$word = preg_replace(array('/\\\\\\\\/', '/\\\\ /'), array('\\', ' '), $matches[1]);
		array_push($ast, parse_word($word));

		$rem = isset($matches[2]) ? $matches[2] : '';
	}

	return $ast;
}


/**
 * Build a SQL query from a view rule.
 * @param $rule is the rule as raw text.
 * @param $selection designate what field to get from SQL table. Warning: it is not escaped.
 * @param $limit specifies the maximum number of items to return. Ignored if negative.
 * @return a PDO-ready query and the binding array.
 *
 * TODO: Refactor me!!!
 */
function rule2sql($rule, $selection='*', $limit=-1) {
	$limit = (int)$limit;

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

	if ($limit >= 0) {
		$query .= " LIMIT $limit";
	}

	return array($query, $var_array);
}

/**
 * Get rule from view name.
 * The rule comes from database for regular views or is computed some
 * other way if it is recognized as a virtual view.
 * @param $view is the view name.
 */
function get_view_rule($view) {
	global $dbh;

	// Handle virtual views
	if (substr($view, 0, 1) == "$") {
		echo('virtual');
	}

	$query = $dbh->prepare('SELECT rule FROM views WHERE name = ?');
	$query->execute(array($view));
	if (!($rule = $query->fetch(PDO::FETCH_ASSOC)['rule'])) {
		$rule = '';
	}

	return $rule;
}


