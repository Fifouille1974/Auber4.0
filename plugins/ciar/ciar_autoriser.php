<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


// declarer la fonction du pipeline
function ciar_autoriser(){}


/**
 * Droit de transformer une rubrique en EC
 * @return boolean
 */
function autoriser_rubrique_eccreer($faire,$type,$id,$qui,$opt) {

	// il faut au moins l’autorisation de modifier la rubrique
	$autoriser = autoriser('modifier','rubrique',$id);

	if ($autoriser) {
		$autoriser = false;

		// il faut en plus l’autorisation explicite de creer des EC ...
		if (in_array($GLOBALS['visiteur_session']['cioption'],array('eccma','ecadminsite'))) {
			$autoriser = true;
			
		// ... ou bien etre administrateur du site sans l'option 'ecadminrestreint'
		} elseif ($GLOBALS['visiteur_session']['statut']=='0minirezo' AND $GLOBALS['visiteur_session']['cioption']!='ecadminrestreint') {
			include_spip('inc/filtres');			
			if (spip_version()>=3)
				$n = sql_countsel("spip_auteurs_liens", "objet='rubrique' AND id_auteur=".$GLOBALS['visiteur_session']['id_auteur']);
			else	
				$n = sql_countsel("spip_auteurs_rubriques", "id_auteur=".$GLOBALS['visiteur_session']['id_auteur']);
			if (!$n)
				$autoriser = true;			
		}

	}

	return $autoriser;
}


/**
 * Droit de gerer un EC
 * @return boolean
 */
function autoriser_rubrique_ecmodifier($faire,$type,$id,$qui,$opt) {

	$autoriser = false;
	$id_auteur = isset($qui['id_auteur']) ? $qui['id_auteur'] : $GLOBALS['visiteur_session']['id_auteur'];
	
	// il faut l'autorisation explicite de gerer cet EC
	$row = sql_fetsel("id_auteur,cistatut_auteur_rub", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=".$id." AND id_auteur=".$id_auteur,"","");
	if ($row)
		if ($row['cistatut_auteur_rub']=='eccma')
			$autoriser = true;
			

	return $autoriser;
}

?>