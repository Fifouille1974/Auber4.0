<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciag_commun');


function formulaires_ciag_gerer_gestionnaires_charger_dist($id_groupe, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{		
	$id_groupe = intval($id_groupe);
	$valeurs['id_groupe'] = $id_groupe;
	$valeurs['tableau_auteurs'] = array();
	$valeurs['liste_auteurs'] = "";
	$valeurs['ci_pagination'] = $pagination;

	if (!autoriser('modifier','groupeauteur',$id_groupe))
		return false;

	// gestionnaires actuels de ce groupe
	if ($id_groupe>0) {
		$oldauteurs = ciag_liste_gestionnaires_de_grpauteurs($id_groupe,$pagination,$debut_auteur);
		
		if ($oldauteurs) {
			$valeurs['tableau_auteurs'] = $oldauteurs;
			$valeurs['liste_auteurs'] = implode(",", $oldauteurs);
		}
	}

	$valeurs['_hidden'] = "<input type='hidden' name='id_groupe' value='".$id_groupe."' />"
						. "<input type='hidden' name='ciag_grpauteurs_img_avant' value='".$valeurs['liste_auteurs']."' />";

	
	return $valeurs;
}

function formulaires_ciag_gerer_gestionnaires_verifier_dist($id_groupe, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciag_gerer_gestionnaires_traiter_dist($id_groupe, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{
	$auteurs = _request('auteurs');
	$ctrl_concurrent = true;
	$image_avant = _request('ciag_grpauteurs_img_avant');
	$ciauteurs_concurrent = !ciag_modifier_gestionnaires_dans_grpauteurs($auteurs,$id_groupe,$ctrl_concurrent,$image_avant, $pagination, $debut_auteur);

	if ($ciauteurs_concurrent) {
		$res['message_erreur'] = _T("alerte_modif_info_concourante");
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = generer_url_ecrire("ciag_groupe_auteurs","id_groupe=$id_groupe");
	}
	
	return $res;	
}

?>