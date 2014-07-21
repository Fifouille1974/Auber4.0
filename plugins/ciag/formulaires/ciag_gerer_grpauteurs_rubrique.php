<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciag_commun');


function formulaires_ciag_gerer_grpauteurs_rubrique_charger_dist($id_rubrique,$retour='', $config_fonc='', $row=array(), $hidden='')
{		
	$id_rubrique = intval($id_rubrique);
	if (!$id_rubrique)
		return false;
	
	$valeurs['id_rubrique'] = $id_rubrique;
	$valeurs['tableau_groupes'] = array();
	$valeurs['heritage_grpauteurs'] = array();
	$valeurs['liste_groupes'] = "";

	if (!autoriser('modifier','rubrique',$id_rubrique))
		return false;

	// si plugin CIAR
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');

		// si la rubrique est un EC,
		// il faut etre eccma de cet EC
		if (ciar_ec_non_gere($id_rubrique))
			return false;
	
		// si la rubrique est dans un EC (mais sans etre l'EC)
		// ne pas lancer ce formulaire
		if (ciar_rub_ec($id_rubrique) AND !ciar_rub_ec_direct($id_rubrique))
			return false;
	} 
		
	// groupes actuels de cette rubrique
	if ($id_rubrique>0) {
		$oldgroupes = ciag_liste_grpauteurs_de_rubrique($id_rubrique);			
		if ($oldgroupes) {
			$valeurs['tableau_groupes'] = $oldgroupes;
			$valeurs['liste_groupes'] = implode(",", $oldgroupes);
		}

		if (!defined('_DIR_PLUGIN_CIAR')){
			$heritage_grpauteurs = ciag_liste_grpauteurs_de_rubrique_par_heritage($id_rubrique);	
			if ($heritage_grpauteurs)
				$valeurs['heritage_grpauteurs'] = $heritage_grpauteurs;
		}
	}

	$valeurs['_hidden'] = "<input type='hidden' name='id_rubrique' value='".$id_rubrique."' />"
						. "<input type='hidden' name='ciag_grpauteurs_img_avant' value='".$valeurs['liste_groupes']."' />";

	
	return $valeurs;
}

function formulaires_ciag_gerer_grpauteurs_rubrique_verifier_dist($id_rubrique,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciag_gerer_grpauteurs_rubrique_traiter_dist($id_rubrique,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	if (spip_version()>=3)
		$exec = "rubrique";
	else
		$exec = "naviguer";
	
	$grpauteurs = _request('groupes');
	$ctrl_concurrent = true;
	$image_avant = _request('ciag_grpauteurs_img_avant');
	$ciauteurs_concurrent = !ciag_modifier_grpauteurs_de_rubrique($grpauteurs,$id_rubrique,$ctrl_concurrent,$image_avant);

	$url = generer_url_ecrire($exec,"id_rubrique=$id_rubrique");
	if (_request('retour')) {
		if (_request('retour')=='ciar_rubrique_protection')
			$url = generer_url_ecrire("ciar_rubrique_protection","id_rubrique=$id_rubrique");
	}
	
	if ($ciauteurs_concurrent) {
		$res['message_erreur'] = _T("alerte_modif_info_concourante");
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = $url;
	}
	
	return $res;	
}

?>