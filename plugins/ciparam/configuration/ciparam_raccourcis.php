<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/config');
include_spip('inc/plugin');
include_spip('inc/ciparam_inc_commun');
include_spip('inc/ciparam_inc_meta');


function configuration_ciparam_raccourcis_dist(){

	$res .= ciparam_lister_raccourcis("articles");
	
	$res .= ciparam_lister_raccourcis("rubriques");
	
	$res .= ciparam_lister_raccourcis("syndic");
	
	return $res;
}


function ciparam_lister_raccourcis($objet) {
	$texte = "";
	
	if ($objet) {	
		$table = "spip_ci_raccourcis_".$objet;	
		$compteur_raccourcis = array();
		if ($objet=='syndic')
			$objetnombre = 'sites';
		else
			$objetnombre = $objet;

		$res = sql_allfetsel("COUNT(*) as cnt, raccourci", $table, "", "raccourci");
		foreach($res as $row) $compteur_raccourcis[$row['raccourci']] = $row['cnt'];
	
		$raccourcis = ciparam_charger_param_xml("raccourcis-".$objet);
    	$raccourcis = ciparam_order_array($raccourcis,'description');

		if ($raccourcis) {
			$texte .= debut_cadre_relief('',true,'',_T('ciparam:titre_raccourcis_'.$objet));
		    $texte .= "<ul class='arial11'>";
		    foreach ($raccourcis as $raccourci) {
		    	if (isset($compteur_raccourcis[$raccourci['nom']])) {
		    		if ($compteur_raccourcis[$raccourci['nom']]<2)
			    		$libelle = substr($objetnombre,0,-1);
			    	else		    		
			    		$libelle = $objetnombre;

		    		$nombre = "(".$compteur_raccourcis[$raccourci['nom']]." ".$libelle.")";
		    	} else {
			    	$nombre = "";
		    	}
		    	$nom = $raccourci['nom'];
		    	$description = $raccourci['description'];
	    		$texte .= "<li><a href='".generer_url_ecrire("ciparam_raccourci","objet=$objet&raccourci=$nom")."'>".$description."</a> ".$nombre."</li>\n";
		    }
		    $texte .= "</ul>\n";
		    $texte .= fin_cadre_relief(true);
		}
	}
	
	return $texte;  
}	

?>