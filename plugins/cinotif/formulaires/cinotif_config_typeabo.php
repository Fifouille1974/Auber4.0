<?php
/**
 * Plugin CINOTIF 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cinotif_commun');


function formulaires_cinotif_config_typeabo_charger_dist() {

	if (!autoriser('configurer', 'configuration'))
		return false;

	// valeurs actuelles
	$valeurs = cinotif_tableau_meta();
	
	if (!isset($valeurs['typeabo']) OR !$valeurs['typeabo'])
		$valeurs['typeabo'] = 'defaut';

	return $valeurs;
}

function formulaires_cinotif_config_typeabo_verifier_dist(){
	return $erreurs;
}

function formulaires_cinotif_config_typeabo_traiter_dist(){
	
	include_spip('inc/meta');
	$cinotif_meta = cinotif_tableau_meta();

	$valeur_saisie = _request('typeabo');
	if ($valeur_saisie AND in_array($valeur_saisie,array('defaut','theme','simple'))){
		$cinotif_meta['typeabo'] = $valeur_saisie;
		ecrire_meta('cinotif', @serialize($cinotif_meta));
		
		// pour que les squelettes puissent savoir facilement le type de formulaire
		ecrire_meta('cinotif_formulaire', $valeur_saisie);
	}
	
	$res['message_ok'] = "";
	$res['redirect'] = "";
	
	return $res;	
}

?>