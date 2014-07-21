<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
if (!defined("_ECRIRE_INC_VERSION")) return;

function ciparam_lire_meta($cle) {
	static $ciparam_config;
	
	if (!$ciparam_config)
		$ciparam_config = @unserialize($GLOBALS['meta']['ciparam']);
	
	if (isset($ciparam_config[$cle])) {
		// compatibilite ascendante
		if ($cle=='ci_abonnement_xiti' AND $ciparam_config[$cle]=='_images/void.gif?') {
			$ci_dir_plugin_ciparam = _DIR_PLUGIN_CIPARAM;
			if (!is_dir(_DIR_RESTREINT_ABS))
				$ci_dir_plugin_ciparam = substr($ci_dir_plugin_ciparam,3);
			$ciparam_config[$cle] = $ci_dir_plugin_ciparam.'_images/void.gif?';
		}

		return $ciparam_config[$cle];
	} else {
		return '';
	}
}

function ciparam_dir_plugin_site_public() {
	$ci_dir_plugin = _DIR_PLUGIN_CIPARAM;

	if (substr($ci_dir_plugin,0,3)=='../')
		$ci_dir_plugin = substr($ci_dir_plugin,3);
		
	return $ci_dir_plugin;
}

?>