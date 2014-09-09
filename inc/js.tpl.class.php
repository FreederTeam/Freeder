<?php
/**
 *  Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once(INC_DIR . 'rain.tpl.class.php');

class JsTPL extends RainTPL {
	protected function compileTemplate($template_code, $tpl_basedir){
		if (parent::$rewriteEngine) {
			$template_code = parent::$rewriteEngine->rewrite_js($template_code);
		}
		$compiled_code = $this->var_replace($template_code, '\{', '\}', '<?=', '?>');
		return $compiled_code;
	}
}