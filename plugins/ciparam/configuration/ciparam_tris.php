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


function configuration_ciparam_tris_dist(){

	$texte = "";
	$objet = "rubriques";
	$table = "spip_".$objet;	
	$compteur_tris = array();
	
	$res = sql_allfetsel("COUNT(*) as cnt, citri", $table, "", "citri");
	foreach($res as $row) $compteur_tri[$row['citri']] = $row['cnt'];
	
	$res2 = sql_allfetsel("COUNT(*) as cnt2, citrinum", $table, "", "citrinum");
	foreach($res2 as $row2) $compteur_trinum[$row2['citrinum']] = $row2['cnt2'];

	$res3 = sql_allfetsel("COUNT(*) as cnt3, citriinverse", $table, "", "citriinverse");
	foreach($res3 as $row3) $compteur_triinverse[$row3['citriinverse']] = $row3['cnt3'];

	$tris = ciparam_charger_param_xml("tris-".$objet);
	$tris = ciparam_order_array($tris,'description');

	if ($tris) {
		$texte .= debut_cadre_relief('',true,'',_T('ciparam:titre_tri'));
	    $texte .= "<ul class='arial11'>";
	    foreach ($tris as $tri) {
   			$valeur = ciparam_tri_to_row($tri['nom'],$tri['ordre']);

	    	if ($valeur['tri'] AND isset($compteur_tri[$tri['nom']])) 
	    		$nombre = "(".$compteur_tri[$tri['nom']]." ".$objet.")";
	    	elseif ($valeur['trinum'] AND isset($compteur_trinum[$tri['nom']])) 
	    		$nombre = "(".$compteur_trinum[$tri['nom']]." ".$objet.")";
	    	elseif ($valeur['triinverse'] AND isset($compteur_triinverse[$tri['nom']])) 
	    		$nombre = "(".$compteur_triinverse[$tri['nom']]." ".$objet.")";
	    	else
		    	$nombre = "";

	    	$nom = $tri['nom'];
	    	$description = $tri['description'];
	    	$param = "citri=".$valeur['tri']."&citrinum=".$valeur['trinum']."&citriinverse=".$valeur['triinverse'];
    		$texte .= "<li><a href='".generer_url_ecrire("ciparam_tri",$param)."'>".$description."</a> ".$nombre."</li>\n";
	    }
	    $texte .= "</ul>\n";
	    $texte .= fin_cadre_relief(true);
	}
	
	return $texte;  
}	


?>