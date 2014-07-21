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


function configuration_ciparam_forme_dist(){

	$objet = _request('objet');
	$ciforme = _request('ciforme');
	$texte = "";
	$nom = "";
	$description = "";

	if ($objet=='articles' OR $objet=='rubriques') {

		$table = "spip_".$objet;
		$formes = ciparam_charger_param_xml("formes-".$objet);
		$formes = ciparam_order_array($formes,'description');
		
		if (!$ciforme){
			$tableau_des_formes = array();
			if ($formes) {
			    foreach ($formes as $forme)
			    	$tableau_des_formes[] = $forme['nom'];
			}
			$req_where = sql_in('statut', array('poubelle','archive'),'NOT'); 
			if ($tableau_des_formes)
				$req_where .= " AND ".sql_in('ciforme', $tableau_des_formes,'NOT'); 

				
			if (spip_version()>=3){
				$texte = recuperer_fond('prive/objets/liste/'.$objet,array('where'=>$req_where,'titre'=>$objet." "._T('ciparam:sans_forme'),'par'=>'date'));
			} else {				
				$texte .= debut_cadre_relief('',true,'','');
				$texte .= "<h1>".$objet." "._T('ciparam:sans_forme')."</h1><br/>";
				
				if ($objet=='articles')
					$texte .= afficher_objets('article',$objet." "._T('ciparam:sans_forme'), array('FROM' => "spip_articles AS articles", 'WHERE' => $req_where, 'ORDER BY' => "articles.date DESC"));
				elseif ($objet=='rubriques')
					$texte .= afficher_objets('rubrique',$objet." "._T('ciparam:sans_forme'), array('FROM' => "spip_rubriques AS rubriques", 'WHERE' => $req_where, 'ORDER BY' => "rubriques.id_rubrique"));
			    
			    $texte .= fin_cadre_relief(true);
			}
			
		} else {
		
			if ($formes) {
			    foreach ($formes as $forme) {
			    	if ($forme['nom']==$ciforme) { 
				    	$nom = $forme['nom'];
				    	$description = $forme['description'];
				    	break;
			    	}
			    }
			    
			    if ($nom) {	
			    	$where = "ciforme='".$nom."'";
					if (spip_version()>=3){
						$texte = recuperer_fond('prive/objets/liste/'.$objet,array('where'=>$where,'titre'=>$description.' - '._T('ciparam:titre_'.$objet.'_lies'),'par'=>'date'));
					} else {
						$texte .= debut_cadre_relief('',true,'','');
						$texte .= "<h1>".$description."</h1><br/>";
						
						if ($objet=='articles')
							$texte .= afficher_objets('article',_T('ciparam:titre_articles_lies'), array('FROM' => "spip_articles AS articles", 'WHERE' => $where, 'ORDER BY' => "articles.date DESC"));
						elseif ($objet=='rubriques')
							$texte .= afficher_objets('rubrique',_T('ciparam:titre_rubriques_lies'), array('FROM' => "spip_rubriques AS rubriques", 'WHERE' => $where, 'ORDER BY' => "rubriques.id_rubrique"));
					    
					    $texte .= fin_cadre_relief(true);
					}
			    }
			}
		}
	}
	
	return $texte;
}

?>