<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/ciag_commun');


function exec_ciag_liste_grpauteurs_rubriques(){
	
	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
	
	
	echo debut_gauche('', true);
	$ciag_navigation = charger_fonction('ciag_navigation', 'configuration');
		echo $ciag_navigation();
	
	echo creer_colonne_droite('', true);
	echo debut_droite('', true);
	
	echo '<style type="text/css"><!-- #contenu a {color: #000000;} --></style>';
	echo gros_titre(_T('ciag:liste_grpauteurs_rubriques'),'', false);
	echo "<br /><br />";
	
	echo debut_cadre_relief('',true,'',_T('ciag:liste_grpauteurs_rubriques'));
	
	
	echo recuperer_fond("prive/objets/liste/ciag_grpauteurs_rubriques", $contexte);
	
	echo fin_cadre_relief(true);
	
	
	echo  "<br />\n";
	
	echo fin_gauche(), fin_page();
}

?>