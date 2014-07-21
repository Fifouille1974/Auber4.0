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


function configuration_ciparam_raccourci_dist(){

	$objet = _request('objet');
	$ciraccourci = _request('raccourci');
	$texte = "";
	$nom = "";
	$description = "";

	if ($objet=='articles' OR $objet=='rubriques'  OR $objet=='syndic') {
	
		$table = "spip_ci_raccourcis_".$objet;
		$raccourcis = ciparam_charger_param_xml("raccourcis-".$objet);
		$raccourcis = ciparam_order_array($raccourcis,'description');
	
		if ($raccourcis) {
		    foreach ($raccourcis as $raccourci) {
		    	if ($raccourci['nom']==$ciraccourci) { 
			    	$nom = $raccourci['nom'];
			    	$description = $raccourci['description'];
			    	break;
		    	}
		    }
		    
		    if ($nom) {
				if (spip_version()>=3){
					$texte = recuperer_fond('prive/objets/liste/ciparam_3_'.$objet.'_raccourci',array('raccourci'=>$nom,'titre'=>$description.' - '._T('ciparam:titre_'.$objet.'_lies'),'par'=>'date'));
				} else {

					$texte .= debut_cadre_relief('',true,'','');
					$texte .= "<h1>".$description."</h1><br/>";
	
					if ($objet=='articles')
						$texte .= afficher_objets('article',_T('ciparam:titre_articles_lies'), array('FROM' => "spip_articles AS articles, $table AS lien", 'WHERE' => "lien.id_article=articles.id_article AND raccourci='$nom'", 'ORDER BY' => "articles.date DESC"));
					elseif ($objet=='rubriques')
						$texte .= afficher_objets('rubrique',_T('ciparam:titre_rubriques_lies'), array('FROM' => "spip_rubriques AS rubriques, $table AS lien", 'WHERE' => "lien.id_rubrique=rubriques.id_rubrique AND raccourci='$nom'", 'ORDER BY' => "rubriques.id_rubrique"));
					elseif ($objet=='syndic')
						$texte .= afficher_objets('site',_T('ciparam:titre_syndic_lies'), array('FROM' => "spip_syndic AS syndic, $table AS lien", 'WHERE' => "lien.id_syndic=syndic.id_syndic AND raccourci='$nom'", 'ORDER BY' => "syndic.id_syndic"));
			    	
				    $texte .= fin_cadre_relief(true);
				}			    
		    }
		}
	
	}
	
	return $texte;
}


?>