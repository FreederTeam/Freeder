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
