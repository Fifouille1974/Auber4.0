<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciag_commun');


function formulaires_ciag_gerer_auteurs_charger_dist($id_groupe, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{		
	$id_groupe = intval($id_groupe);
	$valeurs['id_groupe'] = $id_groupe;
	$valeurs['tableau_auteurs'] = array();
	$valeurs['liste_auteurs'] = "";
	$valeurs['ci_pagination'] = $pagination;

	if (!autoriser('modifier','groupeauteur',$id_groupe))
		return false;

	// si plugin ciar, si le groupe a une rubrique EC,
	// que l'on n'a pas le droit de gerer,
	// ne pas lancer ce formulaire
	$rubriques_exclues = array();
	if (ciag_grp_contient_ec_pas_gerer($id_groupe))
		return false;		

	// admin restreints
	$valeurs['ciag_admin_restreints'] = ciag_liste_auteurs_restreints_de_grpauteurs($id_groupe);


	// auteurs actuels de ce groupe (pour cette tranche de pagination)
	if ($id_groupe>0) {
		$oldauteurs = ciag_liste_auteurs_de_grpauteurs($id_groupe,$pagination,$debut_auteur);
		
		if ($oldauteurs) {
			$valeurs['tableau_auteurs'] = $oldauteurs;
			$valeurs['liste_auteurs'] = implode(",", $oldauteurs);
		}
	}

	// administrateurs du site
	$adminsite = array();
	if (spip_version()>=3)
		$result = sql_select("A.id_auteur AS id_auteur","spip_auteurs AS A LEFT JOIN spip_auteurs_liens AS R ON A.id_auteur=R.id_auteur", "A.statut='0minirezo' AND R.objet='rubrique' AND R.id_objet is NULL");
	else
		$result = sql_select("A.id_auteur AS id_auteur","spip_auteurs AS A LEFT JOIN spip_auteurs_rubriques AS R ON A.id_auteur=R.id_auteur", "A.statut='0minirezo' AND R.id_rubrique is NULL");
	
	while ($row = sql_fetch($result))
		$adminsite[] = $row['id_auteur'];
		
	// on enleve ceux qui ne seront pas denormalises	
	$adminsite = array_diff($adminsite,ciag_auteurs_adminsite($adminsite,true));	
		
	$valeurs['ciag_admin_site'] = $adminsite;
	
	$valeurs['_hidden'] = "<input type='hidden' name='id_groupe' value='".$id_groupe."' />"
						. "<input type='hidden' name='ciag_grpauteurs_img_avant' value='".$valeurs['liste_auteurs']."' />";

	
	return $valeurs;
}

function formulaires_ciag_gerer_auteurs_verifier_dist($id_groupe, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciag_gerer_auteurs_traiter_dist($id_groupe, $pagination, $debut_auteur, $retour='', $config_fonc='', $row=array(), $hidden='')
{
	$auteurs = _request('auteurs');
	$ctrl_concurrent = true;
	$image_avant = _request('ciag_grpauteurs_img_avant');
	
	$ciauteurs_concurrent = !ciag_modifier_auteurs_dans_grpauteurs($auteurs,$id_groupe,$ctrl_concurrent,$image_avant,$pagination,$debut_auteur);

	if ($ciauteurs_concurrent) {
		$res['message_erreur'] = _T("alerte_modif_info_concourante");
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = generer_url_ecrire("ciag_groupe_auteurs","id_groupe=$id_groupe");
	}
	
	return $res;	
}


?>