<?php
function update($old_version, $current_version) {
	if ($old_version == $current_version) {
		return;
	}

	$key_start = array_search($old_version, Config::versions);
	$key_stop = array_search($current_version, Config::versions);

	for ($i = $key_start; $i < $key_stop; $i++) {
		$function = 'update_'.$versions[$i].'_'.$versions[$i+1];
		$function();
	}
}
