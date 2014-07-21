<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/ciag_commun');


function exec_ciag_miniliste_grpauteurs_auteurs(){
	
	include_spip('inc/commencer_page');
	include_spip('inc/headers');
	http_no_cache();
	echo init_entete(_T('ciag:liste_grpauteurs_auteurs'), 0, false)
	. "<div id='page'>";
	
	echo debut_cadre_relief('',true,'',_T('ciag:liste_grpauteurs_auteurs'));
	
	echo recuperer_fond("prive/objets/liste/ciag_mini_grpauteurs_auteurs", $contexte);
	
	echo fin_cadre_relief(true);

	echo '<div><a href="javascript:window.close()">'._T('ciag:fermer').'</a></div>';
	echo "</body></html>";
}

?>