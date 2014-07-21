<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


function exec_cinotif_suivinotifications(){
	
	if (!autoriser('configurer')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		if (spip_version()<3) {
			echo "<br />\n";
			echo gros_titre(_T('cinotif:suivi_notifications'),'', false);
		}
		
		echo debut_gauche('', true);
		$cinotif_navigation = charger_fonction('cinotif_navigation', 'configuration');
	  	echo $cinotif_navigation();
		
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);

		if (spip_version()>=3)
			echo gros_titre(_T('cinotif:suivi_notifications'),'', false);
		
		echo debut_cadre_relief('',true,'',_T('cinotif:suivi_notifications'));
		echo recuperer_fond("prive/objets/liste/cinotif_suivinotifications", $contexte);
		echo fin_cadre_relief(true);
		
		echo  "<br />\n";		
		echo fin_gauche(), fin_page();
	}
}

function cinotif_urlparent_ecrire($objet,$id_objet,$parent='',$id_parent=''){
	
	include_spip('inc/filtres');
	if (spip_version()>=3){
		$exec_rubrique = "rubrique";
		$exec_article = "article";
	} else {
		$exec_rubrique = "naviguer";
		$exec_article = "articles";
	}	
	
	if ($objet=='article')
		$url = generer_url_ecrire($exec_article,"id_article=".$id_objet);
	elseif ($parent=='article')
		$url = generer_url_ecrire($exec_article,"id_article=".$id_parent);
	elseif ($parent=='rubrique')
		$url = generer_url_ecrire($exec_rubrique,"id_rubrique=".$id_parent);

	return $url;
}

?>