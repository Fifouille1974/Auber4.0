<?php
/**
 * Plugin CINOTIF 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/cinotif_commun');


function formulaires_cinotif_config_charger_dist() {

	if (!autoriser('configurer', 'configuration'))
		return false;

	// valeurs actuelles
	$valeurs = cinotif_tableau_meta();

	return $valeurs;
}

function formulaires_cinotif_config_verifier_dist(){
	return $erreurs;
}

function formulaires_cinotif_config_traiter_dist(){
	
	include_spip('inc/meta');
	$cinotif_objets = cinotif_objets();
	$cinotif_meta = cinotif_tableau_meta();

	foreach ($cinotif_objets AS $objet) {
		$cinotif_meta['abonnements_'.$objet] = array();
		$abonnements_configurables = cinotif_abonnements_configurables($objet);

		$abonnements_objet = _request('abonnements_'.$objet);
		if (isset($abonnements_objet) AND is_array($abonnements_objet)) {
			foreach ($abonnements_objet as $abonnement_objet) {
				if (in_array($abonnement_objet,$abonnements_configurables))
					$cinotif_meta['abonnements_'.$objet][] = $abonnement_objet;
			}
		}
	}
	
	ecrire_meta('cinotif', @serialize($cinotif_meta));


	// pour que les squelettes puissent savoir facilement
	// s'il faut afficher dans un objet les liens pour s'abonner / se desabonner
	reset($cinotif_objets);
	foreach ($cinotif_objets AS $objet) {
		if ($objet!='site' AND isset($cinotif_meta['lien_abo_uniquement_en_page_accueil']) AND $cinotif_meta['lien_abo_uniquement_en_page_accueil']=='oui')
			ecrire_meta('cinotif_'.$objet, 'non');			
		elseif (isset($cinotif_meta['abonnements_'.$objet]) AND $cinotif_meta['abonnements_'.$objet])
			ecrire_meta('cinotif_'.$objet, 'oui');
		else
			ecrire_meta('cinotif_'.$objet, 'non');
	}
	
	$res['message_ok'] = "";
	$res['redirect'] = "";
	
	return $res;	
}

function cinotif_config_checkbox($val=''){
	
	$objets = cinotif_objets();
	$valeurs_actuelles = cinotif_tableau_meta();
	$abonnements_configurables = cinotif_abonnements_configurables();
	
	$abo_configurables_objet = array();
	foreach ($objets AS $objet)
		$abo_configurables_objet[$objet] = cinotif_abonnements_configurables($objet);

	
	$return = '<table summary="'._T('cinotif:config_evenements').'" border=0 cellspacing=0 cellpadding=2 width="100%">
				<thead style="line-height: normal;">
				<tr class="titrem">
				<th id="col1" width="55%">'._T('cinotif:config_evenements').'</th>
				<th id="col2" class="verdana1" width="15%">'._T('cinotif:site').'</th>
				<th id="col3" class="verdana1" width="15%">'._T('cinotif:rubrique').'</th>
				<th id="col4" class="verdana1" width="15%">'._T('cinotif:article').'</th>
				</tr>
				</thead>
				<tbody>';
	
	foreach ($abonnements_configurables AS $abonnement) {
		$i=1;
		$return .= '<tr class="tr_liste"><td headers="col1" class="verdana1">'._T('cinotif:'.$abonnement).'</td>';
		
		foreach ($objets AS $objet) {
			$i++;
			$checked = '';	
			$disabled = '';
			if (isset($valeurs_actuelles['abonnements_'.$objet]) AND is_array($valeurs_actuelles['abonnements_'.$objet])){
				if (in_array($abonnement,$valeurs_actuelles['abonnements_'.$objet]))
					$checked = ' checked="checked"';
			}
			if (isset($abo_configurables_objet[$objet]) AND is_array($abo_configurables_objet[$objet])){
				if (!in_array($abonnement,$abo_configurables_objet[$objet]))
					$disabled = ' disabled="disabled"';
			}
			$return .= '<td headers="col'.$i.'" class="verdana1"><input id="'.$objet.'_'.$abonnement.'" type="checkbox" value="'.$abonnement.'" name="abonnements_'.$objet.'[]"'.$checked.$disabled.' /></td>';
		}				
		$return .= '</tr>';
	}
				
	$return .= '</tbody></table>';
	
	return $return;			
}
?>