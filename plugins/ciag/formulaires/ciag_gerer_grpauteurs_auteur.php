<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciag_commun');


function formulaires_ciag_gerer_grpauteurs_auteur_charger_dist($id_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{		
	$id_auteur = intval($id_auteur);
	if (!$id_auteur)
		return false;
		
	$valeurs['id_auteur'] = $id_auteur;
	$valeurs['tableau_groupes'] = array();
	$valeurs['liste_groupes'] = "";
	$valeurs['groupes_avec_ec_non_gere'] = array();

	if (!autoriser('modifier','auteur',$id_auteur))
		return false;

	// groupes actuels de cet auteur
	if ($id_auteur>0) {
		$oldgroupes = ciag_liste_grpauteurs_de_auteur($id_auteur);	
		
		if ($oldgroupes) {
			$valeurs['tableau_groupes'] = $oldgroupes;
			$valeurs['liste_groupes'] = implode(",", $oldgroupes);
		}
	}

	// si plugin ciar, si le groupe a une rubrique EC,
	// que l'on n'a pas le droit de gerer,
	// ne pas permettre de le selectionner
	$result = sql_select("id_groupe","spip_ciag_grpauteurs","","","id_groupe");
	while ($row = sql_fetch($result)) {
		if (ciag_grp_contient_ec_pas_gerer($row['id_groupe']))
			$valeurs['groupes_avec_ec_non_gere'][] = $row['id_groupe'];
	}
	
	
	$valeurs['_hidden'] = "<input type='hidden' name='id_auteur' value='".$id_auteur."' />"
						. "<input type='hidden' name='ciag_grpauteurs_img_avant' value='".$valeurs['liste_groupes']."' />";

	
	return $valeurs;
}

function formulaires_ciag_gerer_grpauteurs_auteur_verifier_dist($id_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciag_gerer_grpauteurs_auteur_traiter_dist($id_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	$grpauteurs = _request('groupes');
	$ctrl_concurrent = true;
	$image_avant = _request('ciag_grpauteurs_img_avant');
	$ciauteurs_concurrent = !ciag_modifier_grpauteurs_de_auteur($grpauteurs,$id_auteur,$ctrl_concurrent,$image_avant);

	if (spip_version()>=3)
		$retour = "auteur";
	else
		$retour = "auteur_infos";
	
	if ($ciauteurs_concurrent) {
		$res['message_erreur'] = _T("alerte_modif_info_concourante");
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = generer_url_ecrire($retour,"id_auteur=$id_auteur");
	}
	
	return $res;	
}

?>