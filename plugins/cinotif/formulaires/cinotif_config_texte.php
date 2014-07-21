<?php
/**
 * Plugin CINOTIF 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cinotif_commun');


function formulaires_cinotif_config_texte_charger_dist() {

	if (!autoriser('configurer', 'configuration'))
		return false;

	// valeurs actuelles
	$valeurs = cinotif_tableau_meta();

	return $valeurs;
}

function formulaires_cinotif_config_texte_verifier_dist(){
	return $erreurs;
}

function formulaires_cinotif_config_texte_traiter_dist(){
	
	include_spip('inc/meta');
	$cinotif_meta = cinotif_tableau_meta();
	$ok = false;
	
	if ($valeur_saisie = _request('abo_texte')){
		$cinotif_meta['abo_texte'] = $valeur_saisie;
		$ok = true;	
	}

	if ($valeur_saisie = _request('desabo_texte')){
		$cinotif_meta['desabo_texte'] = $valeur_saisie;
		$ok = true;	
	}

	if ($ok)
		ecrire_meta('cinotif', @serialize($cinotif_meta));

	$res['message_ok'] = "";
	$res['redirect'] = "";
	
	return $res;	
}

?>