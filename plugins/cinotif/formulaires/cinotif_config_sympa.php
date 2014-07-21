<?php
/**
 * Plugin CINOTIF 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cinotif_commun');


function formulaires_cinotif_config_sympa_charger_dist() {

	if (!autoriser('configurer', 'configuration'))
		return false;
		
	if (!cinotif_form_theme_ou_simple())
		return false;
		
	// valeurs actuelles
	$valeurs = cinotif_tableau_meta();

	// initialiser typenotif
	if (!isset($valeurs['typenotif']) OR !$valeurs['typenotif'])
		$valeurs['typenotif'] = 'abonnes';
		
	// parametrage par constante dans un fichier d'options
	$valeurs['desactive_adresse_sympa'] = '';
	$valeurs['desactive_adresse_proprio_liste_sympa'] = '';
	$disabled =' disabled="disabled" ';
	
	if (defined("_CINOTIF_SYMPA") AND in_array(_CINOTIF_SYMPA, array('oui','non')))
		$valeurs['disabled_sympa_actif'] = $disabled;
	if (defined("_CINOTIF_ADRESSE_SYMPA") AND _CINOTIF_ADRESSE_SYMPA)
		$valeurs['disabled_adresse_sympa'] = $disabled;
	if (defined("_CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA") AND _CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA)
		$valeurs['disabled_adresse_proprio_liste_sympa'] = $disabled;
	if (defined("_CINOTIF_ABONNEMENT_SYMPA") AND in_array(_CINOTIF_ABONNEMENT_SYMPA, array('oui','non')))
		$valeurs['disabled_abo_sympa'] = $disabled;
	if (defined("_CINOTIF_TYPE_NOTIFICATION") AND in_array(_CINOTIF_TYPE_NOTIFICATION, array('abonnes','sympa','aucun')))
		$valeurs['disabled_typenotif'] = $disabled;

	if (cinotif_sympa_actif())
		$valeurs['sympa_actif'] = 'oui';
	else
		$valeurs['sympa_actif'] = 'non';
		
	return $valeurs;
}

function formulaires_cinotif_config_sympa_verifier_dist(){
	
	if (_request('sympa_actif')=='oui'){	
		
		// adresse mail invalide
		$emails = array();
		if (!(defined("_CINOTIF_ADRESSE_SYMPA") AND _CINOTIF_ADRESSE_SYMPA))
			$emails[] = 'adresse_sympa';
		if (!(defined("_CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA") AND _CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA))
			$emails[] = 'adresse_proprio_liste_sympa';
	
		foreach ($emails AS $email){
			$adresse = _request($email);	
			if ($adresse AND !email_valide($adresse)) 
				$erreurs[$email] = _T('form_email_non_valide');
		}
	}

	return $erreurs;
}

function formulaires_cinotif_config_sympa_traiter_dist(){
	
	include_spip('inc/meta');
	
	$cinotif_meta = cinotif_tableau_meta();

	$valeur_saisie = _request('sympa_actif');
	if ($valeur_saisie AND in_array($valeur_saisie,array('oui','non')))
		$cinotif_meta['sympa_actif'] = $valeur_saisie;
	
	if (_request('sympa_actif')=='oui'){	
		$valeur_saisie = _request('abo_sympa');
		if ($valeur_saisie AND in_array($valeur_saisie,array('oui','non')))
			$cinotif_meta['abo_sympa'] = $valeur_saisie;
	
		$valeur_saisie = _request('typenotif');
		if ($valeur_saisie AND in_array($valeur_saisie,array('sympa','abonnes','aucun')))
			$cinotif_meta['typenotif'] = $valeur_saisie;
	
		$emails = array();
		if (!(defined("_CINOTIF_ADRESSE_SYMPA") AND _CINOTIF_ADRESSE_SYMPA))
			$emails[] = 'adresse_sympa';
		if (!(defined("_CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA") AND _CINOTIF_ADRESSE_PROPRIO_LISTE_SYMPA))
			$emails[] = 'adresse_proprio_liste_sympa';
	
		foreach ($emails AS $email){
			$valeur_saisie = _request($email);
			if ($valeur_saisie AND email_valide($valeur_saisie))
				$cinotif_meta[$email] = $valeur_saisie;
		}
	}

	ecrire_meta('cinotif', @serialize($cinotif_meta));
	
	$res['message_ok'] = "";
	$res['redirect'] = "";
	
	return $res;	
}

?>