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


function configuration_ciparam_tri_dist(){

	$objet = 'rubriques';
	$citri = _request('citri');
	$citrinum = _request('citrinum');
	$citriinverse = _request('citriinverse');
	$texte = "";
	$nom = "";
	$description = "";
	$where = "";

	$table = "spip_".$objet;
	$tris = ciparam_charger_param_xml("tris-".$objet);
	$tris = ciparam_order_array($tris,'description');

	if ($tris) {
	    foreach ($tris as $tri) {
   			$valeur = ciparam_tri_to_row($tri['nom'],$tri['ordre']);

 	    	if ($tri['ordre']=='asc' AND $valeur['tri']==$citri) {
		    	$description = $tri['description'];
		    	$where = "citri='$citri'";
	    	} elseif ($tri['ordre']=='num' AND $valeur['trinum']==$citrinum) {
		    	$description = $tri['description'];
		    	$where = "citrinum='$citrinum'";
	    	} elseif ($tri['ordre']=='desc' AND $valeur['triinverse']==$citriinverse) {
		    	$description = $tri['description'];
		    	$where = "citriinverse='$citriinverse'";
	    	}
	    }

		if (spip_version()>=3){
			$texte = recuperer_fond('prive/objets/liste/rubriques',array('where'=>$where,'titre'=>$description.' - '._T('ciparam:titre_rubriques_lies'),'par'=>'date'));
		} else {
			$texte .= debut_cadre_relief('',true,'','');
			$texte .= "<h1>".$description."</h1><br/>";
			$texte .= afficher_objets('rubrique',_T('ciparam:titre_rubriques_lies'), array('FROM' => "spip_rubriques AS rubriques", 'WHERE' => $where, 'ORDER BY' => "rubriques.id_rubrique"));
		    $texte .= fin_cadre_relief(true);
		}
	}
	
	return $texte;
}

?>