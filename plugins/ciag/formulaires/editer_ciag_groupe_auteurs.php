<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('inc/ciag_commun');


function formulaires_editer_ciag_groupe_auteurs_charger_dist($id_groupe='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){

	$valeurs = array();
	$valeurs['cieditable'] = true;
	
	if ($id_groupe=='oui') {
		if (!autoriser('creer','groupeauteur'))
			$valeurs['cieditable'] = false;
	} else {
		if (!autoriser('modifier','groupeauteur',$id_groupe))
			$valeurs['cieditable'] = false;
	}
	
	$id_groupe = intval($id_groupe);
	
	$id_table_objet = "id_groupe";
	$valeurs[$id_table_objet] = $id_groupe;

	if ($id_groupe) {
		$result = sql_select("*", "spip_ciag_grpauteurs", "id_groupe=".$id_groupe, "", "");
		while ($row = sql_fetch($result)) {
			$valeurs['titre']  = typo($row['titre']);
			$valeurs['descriptif'] = $row['descriptif'];
		}
	}
	
	$valeurs['_hidden'] = "<input type='hidden' name='$id_table_objet' value='".intval($id_groupe)."' />";

	return $valeurs;
}

// Choix par defaut des options de presentation
function ciag_groupe_auteurs_edit_config($row)
{
	global $spip_ecran, $spip_lang, $spip_display;

	$config = $GLOBALS['meta'];
	$config['lignes'] = ($spip_ecran == "large")? 8 : 5;
	$config['afficher_barre'] = false;
	$config['langue'] = $spip_lang;
	return $config;
}

function formulaires_editer_ciag_groupe_auteurs_verifier_dist($id_groupe='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){
	return $erreurs;
}

function formulaires_editer_ciag_groupe_auteurs_traiter_dist($id_groupe='new',$retour='', $config_fonc='groupes_mots_edit_config', $row=array(), $hidden=''){

	$c = array();
	// alignement sur l'approche de filtrage de SPIP
	foreach (array(
		'titre', 'descriptif'
	) as $champ)
		$c[$champ] = _request($champ);

	$exist = false;
	if (intval($id_groupe)>0) {
		$row = sql_fetsel("*", "spip_ciag_grpauteurs", "id_groupe=$id_groupe","","");
		if ($row)
			$exist = true;
	}
	
	if ($exist)
		sql_updateq("spip_ciag_grpauteurs", $c, "id_groupe=$id_groupe");
	else
		$id_groupe = sql_insertq("spip_ciag_grpauteurs", $c);		


	// si aucun auteur n'est gestionnaire du groupe
	// affecter le createur du groupe dans les gestionnaires du groupe
	$id_groupe = intval($id_groupe);
	if (!sql_countsel("spip_ciag_grpauteurs_gestionnaires", "id_groupe=".$id_groupe,"","")) {
		$id_auteur = intval($GLOBALS['visiteur_session']['id_auteur']);	
		if ($id_groupe>0 AND $id_auteur>0) {
			if (!sql_countsel("spip_ciag_grpauteurs_gestionnaires", "id_groupe=".$id_groupe." AND id_auteur=".$id_auteur,"",""))
				sql_insertq('spip_ciag_grpauteurs_gestionnaires', array('id_groupe' => $id_groupe, 'id_auteur' => $id_auteur));
		}
	}

	$res['message_ok'] = "";
	
	if ($exist)
		$res['redirect'] = generer_url_ecrire("ciag_groupes_auteurs");
	else
		$res['redirect'] = generer_url_ecrire("ciag_groupe_auteurs","id_groupe=".$id_groupe);
	
	return $res;	
}

?>