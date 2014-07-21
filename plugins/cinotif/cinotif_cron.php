<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

function cinotif_taches_generales_cron($taches){
	
	$taches['cinotif'] = 1800;
	
	if (defined('_CINOTIF_CRON')) {
		if (intval(_CINOTIF_CRON))
			$taches['cinotif'] = intval(_CINOTIF_CRON);
	}	
	
	return $taches;
}

?>