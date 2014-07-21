<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/ciparam_inc_commun');

// Utilisation du pipeline
function ciparam_cisf_article_options($param){

	// Options supplementaires affectees a l'article
	if ($param['args']['type']=='options_affectees') {
		$id_article = intval($param['args']['id_article']);
		if($id_article){
			$param['data']['ciparam:eq_menu_forme'] = ciparam_forme_id_article($id_article);
			$param['data']['ciparam:eq_menu_raccourci'] = ciparam_raccourcis_id_article($id_article);
		}
		
	// Ajouts dans le menus des options avancees
	} elseif ($param['args']['type']=='options_avancees') {	
		$tableau = $param['data'];
		$tableau_return = array();
		$tableau_ciparam = array(
			"bt_forme"=>"ciparam:eq_menu_forme",
			"bt_raccourci"=>"ciparam:eq_menu_raccourci"
		);	
	
		if (isset($tableau)) {
			if (is_array($tableau)){
				foreach($tableau as $nom=>$libelle){
					if ($nom=='bt_logo') {
						// inserer avant bt_logo
						foreach($tableau_ciparam as $nom2=>$libelle2){
							$tableau_return[$nom2]=$libelle2;
						}
					}
					$tableau_return[$nom]=$libelle;
				}
			}	
		}
			
		if (!$tableau_return)
			$tableau_return = $tableau_ciparam;
		
		$param['data'] = $tableau_return;
	}
	
	return $param;
}

function ciparam_forme_id_article($id_article) {
	$return = "";
	
	if (intval($id_article)>0) {
		$valeur = ciparam_lire_valeur("spip_articles","id_article",$id_article,"ciforme");
		$formes = ciparam_charger_param_xml("formes-articles");
	    foreach ($formes as $forme) {
		   	if ($valeur AND $valeur==$forme['nom']){
		    	$return = $forme['description'];
		    	break;
		   	}
	    }
	}
	
	return $return;
}

function ciparam_raccourcis_id_article($id_article) {
	$return = "";
	
	if (intval($id_article)>0) {
		$valeurs = ciparam_lire_valeurs("spip_ci_raccourcis_articles","id_article",$id_article,"raccourci");
		$raccourcis = ciparam_charger_param_xml("raccourcis-articles");
		
		if ($valeurs AND $raccourcis) {
		    foreach ($raccourcis as $raccourci) {
		    	if (in_array($raccourci['nom'],$valeurs)) {
			    	$return .= $raccourci['description']." | ";
		    	}
		   	}
	    }
		if ($return)
			$return = substr($return,0,-3);
	}
		
	return $return;
}

?>