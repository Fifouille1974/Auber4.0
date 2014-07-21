<?php
/**
 * Plugin CINOTIF 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cinotif_commun');


function formulaires_cinotif_config_themes_charger_dist() {

	if (!autoriser('configurer', 'configuration'))
		return false;

	if (cinotif_sympa_actif())
		$valeurs['sympa_actif'] = 'oui';
	else
		$valeurs['sympa_actif'] = 'non';
	
	return $valeurs;
}

function formulaires_cinotif_config_themes_verifier_dist(){
	return $erreurs;
}

function formulaires_cinotif_config_themes_traiter_dist(){
	
	$res['message_ok'] = "";
	$res['redirect'] = "";
	
	return $res;	
}

?>