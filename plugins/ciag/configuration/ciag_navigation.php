<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


function configuration_ciag_navigation(){

if (spip_version()>=3)
	$rac = icone_horizontale (_T('ciag:titre_groupes_auteurs'), generer_url_ecrire("ciag_groupes_auteurs"), "ciag-annonce.png", "", false);
else
	$rac = icone_horizontale (_T('ciag:titre_groupes_auteurs'), generer_url_ecrire("ciag_groupes_auteurs"), "annonce.gif", "", false);

	
$rac .= icone_horizontale (_T('ciag:liste_auteur_grpauteurs'), generer_url_ecrire("ciag_liste_auteur_grpauteurs"), "breve-24.gif", "", false);
$rac .= icone_horizontale (_T('ciag:liste_auteur_rubriques'), generer_url_ecrire("ciag_liste_auteur_rubriques"), "breve-24.gif", "", false);

$rac .= icone_horizontale (_T('ciag:liste_rubrique_grpauteurs'), generer_url_ecrire("ciag_liste_rubrique_grpauteurs"), "breve-24.gif", "", false);
$rac .= icone_horizontale (_T('ciag:liste_rubrique_auteurs'), generer_url_ecrire("ciag_liste_rubrique_auteurs"), "breve-24.gif", "", false);

$rac .= icone_horizontale (_T('ciag:liste_grpauteurs_auteurs'), generer_url_ecrire("ciag_liste_grpauteurs_auteurs"), "breve-24.gif", "", false);
$rac .= icone_horizontale (_T('ciag:liste_grpauteurs_rubriques'), generer_url_ecrire("ciag_liste_grpauteurs_rubriques"), "breve-24.gif", "", false);

return bloc_des_raccourcis($rac);
}

?>