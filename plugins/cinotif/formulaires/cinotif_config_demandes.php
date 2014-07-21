<?php
/**
 * Plugin CINOTIF 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cinotif_commun');


function formulaires_cinotif_config_demandes_charger_dist() {

	if (!autoriser('configurer', 'configuration'))
		return false;

	// valeurs actuelles
	$valeurs = cinotif_tableau_meta();

	return $valeurs;
}

function formulaires_cinotif_config_demandes_verifier_dist(){
	
	return $erreurs;
}

function formulaires_cinotif_config_demandes_traiter_dist(){
	
	include_spip('inc/meta');
	$cinotif_meta = cinotif_tableau_meta();

	$champs = array('abo_tous','abo_non_auth','auto_notif','lien_abo_uniquement_en_page_accueil');
	foreach ($champs AS $champ){
		$valeur_saisie = _request($champ);
		if ($valeur_saisie AND in_array($valeur_saisie,array('oui','non')))
			$cinotif_meta[$champ] = $valeur_saisie;
	}

	ecrire_meta('cinotif', @serialize($cinotif_meta));
	
	// pour que les squelettes puissent savoir facilement
	// s'il faut afficher dans un objet les liens pour s'abonner / se desabonner
	$ci_objets = array('rubrique','article');;	
	foreach ($ci_objets AS $objet) {
		if (cinotif_form_theme_ou_simple()){
			if (isset($cinotif_meta['lien_abo_uniquement_en_page_accueil']) AND $cinotif_meta['lien_abo_uniquement_en_page_accueil']=='oui'){
				ecrire_meta('cinotif_'.$objet, 'non');		
			} else {
				ecrire_meta('cinotif_'.$objet, 'oui');
			}		
		} else {
			if (isset($cinotif_meta['lien_abo_uniquement_en_page_accueil']) AND $cinotif_meta['lien_abo_uniquement_en_page_accueil']=='oui'){
				ecrire_meta('cinotif_'.$objet, 'non');		
			} elseif (isset($cinotif_meta['abonnements_'.$objet]) AND $cinotif_meta['abonnements_'.$objet]){
				ecrire_meta('cinotif_'.$objet, 'oui');
			} else {
				ecrire_meta('cinotif_'.$objet, 'non');
			}
		}
	}
	
	
	$res['message_ok'] = "";
	$res['redirect'] = "";
	
	return $res;	
}

?>