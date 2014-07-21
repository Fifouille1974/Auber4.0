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


function configuration_ciparam_formes_dist(){

	$res .= ciparam_lister_formes("articles");
	
	$res .= ciparam_lister_formes("rubriques");
	
	
	return $res;
}


function ciparam_lister_formes($objet) {
	$texte = "";
	
	if ($objet) {
		$table = "spip_".$objet;
		if (in_array($objet, array('articles', 'rubriques')))
			$objet_au_singulier = substr($objet,0,-1);
		else
			$objet_au_singulier = $objet;

		$total = 0;
		$req_where = sql_in('statut', array('poubelle','archive'),'NOT'); 
		$q = sql_select("COUNT(*) AS totalcnt", $table, $req_where);	  
		while($row = sql_fetch($q))
		  $total = $row['totalcnt'];

			
		$compteur_formes = array();
		$res = sql_allfetsel("COUNT(*) as cnt, ciforme", $table, "", "ciforme");
		foreach($res as $row) $compteur_formes[$row['ciforme']] = $row['cnt'];
	
		$formes = ciparam_charger_param_xml("formes-".$objet);
    	$formes = ciparam_order_array($formes,'description');

		if ($formes) {
			$texte .= debut_cadre_relief('',true,'',_T('ciparam:titre_formes_'.$objet));
		    $texte .= "<ul class='arial11'>";
		    $totalavecforme = 0;
		    foreach ($formes as $forme) {
		    	if (isset($compteur_formes[$forme['nom']])) {
		    		if ($compteur_formes[$forme['nom']]<2)
			    		$nombre = "(".$compteur_formes[$forme['nom']]." ".$objet_au_singulier.")";
			    	else
			    		$nombre = "(".$compteur_formes[$forme['nom']]." ".$objet.")";
		    	} else {
			    	$nombre = "";
		    	}

		    	$totalavecforme += $compteur_formes[$forme['nom']];
		    	$nom = $forme['nom'];
		    	$description = $forme['description'];
	    		$texte .= "<li><a href='".generer_url_ecrire("ciparam_forme","objet=".$objet."&ciforme=$nom")."'>".$description."</a> ".$nombre."</li>\n";
		    }
		    $texte .= "</ul>\n";
		    
		    $totalsansforme = $total-$totalavecforme;
		    $texte .= "<div>"._T('ciparam:total_avec_forme').$totalavecforme." ".$objet."</div>";
		    $texte .= "<div>"._T('ciparam:total_sans_forme').$totalsansforme." ".$objet." <a href='".generer_url_ecrire("ciparam_forme","objet=".$objet."&ciforme=")."'>"._T('ciparam:voir_la_liste')."</a></div>";
		    $texte .= "<div>"._T('ciparam:total_general').$total." ".$objet."</div>";
		    
		    
		    $texte .= fin_cadre_relief(true);
		}
	}
	
	return $texte;  
}	

?>