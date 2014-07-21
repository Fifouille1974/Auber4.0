<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


// declarer la fonction du pipeline
function ciag_autoriser(){}

/**
 * Droit de creer un groupe d'auteurs
 * @return boolean
 */
function autoriser_groupeauteur_creer($faire,$type,$id,$qui,$opt) {
	$return = false;

	if ($qui['statut'] == '0minirezo' AND !$qui['restreint'])
		$return = true;

	if (defined('_DIR_PLUGIN_CIAR')){
		if (!defined('_CIAG_ECCMA_PAS_CREER')) {
			if ($GLOBALS['visiteur_session']['cioption']=='eccma')
				$return = true;
		}
	}
	
	return $return;
}

/**
 * Droit de gerer un groupe d'auteurs
 * @return boolean
 */
function autoriser_groupeauteur_modifier($faire,$type,$id,$qui,$opt) {

	$autoriser = false;
	$id_auteur = isset($qui['id_auteur']) ? $qui['id_auteur'] : $GLOBALS['visiteur_session']['id_auteur'];
	
	if (sql_countsel("spip_ciag_grpauteurs_gestionnaires", "id_groupe=".$id)>=1) {	
		// il faut l'autorisation explicite de gerer ce groupe d'auteurs
		if(sql_fetsel("*", "spip_ciag_grpauteurs_gestionnaires", "id_groupe=".$id." AND id_auteur=".$id_auteur,"",""))
			$autoriser = true;
	} elseif ($qui['statut']=='0minirezo' AND !$qui['restreint']) {
			$autoriser = true;
	}

	return $autoriser;
}

/**
 * Droit de supprimer un groupe d'auteurs
 * @return boolean
 */
function autoriser_groupeauteur_supprimer($faire, $type, $id, $qui, $opt) {
	
	if (!autoriser('modifier','groupeauteur',$id))
		return false;

	if (sql_countsel('spip_ciag_grpauteurs_auteurs','id_groupe='.intval($id))>=1)
		return false;
		
	if (sql_countsel('spip_ciag_grpauteurs_rubriques','id_groupe='.intval($id))>=1)
		return false;
	
	return true;	
}

?>