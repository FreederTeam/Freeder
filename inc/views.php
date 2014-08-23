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
 * Subroutine for rule2sql building the selection subquery.
 * @param $prefix is the tag prefix (+, -, etc)
 * @param $tag is the tag to considere.
 * @param $subquery is the beginning of the selection subquery.
 * @param $bindings is a reference to an array of values bound inside the query.
 * @return the new subquery.
 */
function append_selection_query($prefix, $tag, $subquery, &$bindings) {
	if ($subquery != '') {
		$operator = $prefix == '+' ? 'OR' : 'AND';
		$subquery = "($subquery) $operator ";
	}

	// Handle virtual tags
	if (substr($tag, 0, 1) == '$') {
		// Designate all entries
		if ($tag == '$all') {
			return $prefix == '+' ? '' : '1=0'; // A little bit hacky
		}

		// Entries by parent feed
		if (substr($tag, 1, 5) == 'feed_') {
			$feed_id = (int)substr($tag, 6);
			return $subquery . "feed_id = $feed_id";
		}
	}

	$bindings[] = $tag;
	$tag_id = "(SELECT id FROM tags WHERE name = ?)";
	return $subquery . ($prefix == '+' ? '' : 'NOT ') . "EXISTS (SELECT tag_id FROM tags_entries WHERE tag_id = $tag_id AND entry_id = E.id)";
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
	$ast[] = array('by', 'by', ''); // Hacky
	$query = "SELECT $selection FROM entries E";

	$bindings = array();
	$subquery = '';
	$state = 'select';
	foreach ($ast as $word) {
		$prefix = strtolower($word[1]);
		$tag = $word[2];
		switch ($state) {
			case 'select':
				switch ($prefix) {
					case '+':
					case '-':
						$subquery = append_selection_query($prefix, $tag, $subquery, $bindings);
						break;

					// Go to next parsing state
					case 'by':
						if ($subquery != '') {
							$query .= " WHERE $subquery";
						}
						$subquery = '';
						$state = 'order';
						break;

					default:
						throw new ParseError("Unknown prefix `$prefix`");
				}
				break;

			case 'order':
				$bind = false;
				switch ($tag) {
					case '$pubDate':
						$tag_count = "E.pubDate";
						break;

					default:
						$tag_id = "(SELECT id FROM tags WHERE name = ?)";
						$tag_count = "(SELECT COUNT(*) FROM tags_entries WHERE tag_id = $tag_id AND entry_id = E.id)";
						$bind = true;
				}
				switch ($prefix) {
					case '+':
						if ($subquery != '') {
							$subquery .= ", ";
						}
						$subquery .= "$tag_count ASC";
						if ($bind) {
							$bindings[] = $tag;
						}
						break;

					case '-':
						if ($subquery != '') {
							$subquery .= ", ";
						}
						$subquery .= "$tag_count DESC";
						if ($bind) {
							$bindings[] = $tag;
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

	return array($query, $bindings);
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
	if (substr($view, 0, 1) == '$') {
		// Raw view rules : $raw_foobar -> use rule "foobar"
		// (to be avoided but useful for debug purpose)
		if (substr($view, 1, 4) == 'raw_') {
			return substr($view, 5);
		}

		// Tag specific view: $tag_foobar -> foobar-tagged entries
		if (substr($view, 1, 4) == 'tag_') {
			return '+' . substr($view, 5) . ' by -$pubDate';
		}

		// Feed specific view: $feed_2343 -> entries from feed whose id is 2343
		if (substr($view, 1, 5) == 'feed_') {
			return '+' . $view . ' by -$pubDate';
		}
	}

	$query = $dbh->prepare('SELECT rule FROM views WHERE name = ?');
	$query->execute(array($view));
	if (!($rule = $query->fetch(PDO::FETCH_ASSOC)['rule'])) {
		$rule = '';
	}

	return $rule;
}


