<?php
function multiarray_search($field, $value, $array, $default_value) {
    /* Search for the first item with value $value for field $field in a 2D array.
     * Returns the sub-array or $default_value.
     */
    foreach($array as $key=>$val) {
        if($val[$field] == $value) {
            return $val;
        }
    }
    return $default_value;
}

function multiarray_filter($field, $value, $array) {
    /* Filters a 2D array returning all the entries where $field is not equal to $value
     */
    $return = array();
    foreach($array as $key=>$val) {
        if($val[$field] != $value) {
            $return[] = $val;
        }
    }
    return $return;
}

function startswith($haystack, $needle) {
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endswith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function list_templates() {
    /* List all the available templates */
    return array_map('ucfirst', array_filter(scandir(TPL_DIR), function($item) { return is_dir(TPL_DIR.$item) && !startswith($item, '.'); }));
}

function get_generation_time($start_generation_time) {
    $round = round(microtime(true) - $start_generation_time, 2).'s';
    if($round == '0s') {
        $round = round((microtime(true) - $start_generation_time)*1000, 3).'ms';
    }
    return $round;
}
