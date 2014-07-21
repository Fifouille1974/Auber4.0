<?php
/**
 * Plugin CINOTIF
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/filtres');


function exec_cinotif_config(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		if (spip_version()<3) {
			echo "<br />\n";
			echo gros_titre(_T('cinotif:titre'),'', false);
		}
				
		echo debut_gauche('', true);
		$cinotif_navigation = charger_fonction('cinotif_navigation', 'configuration');
	  	echo $cinotif_navigation();
	
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		
		if (spip_version()>=3) {
			echo "<br />\n";
			echo gros_titre(_T('cinotif:titre'),'', false);
		}
		$contexte = array();
		echo recuperer_fond("prive/editer/cinotif_config", $contexte);
			
		echo fin_gauche();
		echo fin_page();
	}
}

?>