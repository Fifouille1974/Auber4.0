<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


function exec_ciparam_raccourci(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
//		echo "<br /><br /><br />\n";
		echo "<br />\n";
		$objet = _request('objet');
		if ($objet=='articles')
			echo gros_titre(_T('ciparam:titre_raccourci_article'),'', false);
		elseif ($objet=='rubriques')
			echo gros_titre(_T('ciparam:titre_raccourci_rubrique'),'', false);
		elseif ($objet=='syndic')
			echo gros_titre(_T('ciparam:titre_raccourci_syndic'),'', false);
		
		echo debut_gauche('', true);
		$ciparam_navigation = charger_fonction('ciparam_navigation', 'configuration');
	  	echo $ciparam_navigation();
	
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
	
		$ciparam_raccourci = charger_fonction('ciparam_raccourci', 'configuration');
		echo  $ciparam_raccourci(), "<br />\n";
	
		echo fin_gauche(), fin_page();
	}
}
?>