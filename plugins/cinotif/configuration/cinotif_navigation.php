<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

function configuration_cinotif_navigation(){
	$aide = false;
	if ($GLOBALS['meta']['langue_site']=='fr' AND !isset($GLOBALS['meta']['cinotif_formulaire']) OR $GLOBALS['meta']['cinotif_formulaire']=='defaut')
		$aide = true;
	
	if (spip_version()>=3) {
		$rac = icone_horizontale (_T('cinotif:titre'), generer_url_ecrire("cinotif_config"), "configuration-24.png", "", false);
		if ($aide)
			$rac .= icone_horizontale (_T('cinotif:config_titre_aide'), generer_url_ecrire("cinotif_config_aide"), "configuration-24.png", "", false);
		$rac .= icone_horizontale (_T('cinotif:suivi_abonnes'), generer_url_ecrire("cinotif_suiviabonnes"), "article-24.png", "", false);
		$rac .= icone_horizontale (_T('cinotif:suivi_abonnements'), generer_url_ecrire("cinotif_suiviabonnements"), "article-24.png", "", false);
		$rac .= icone_horizontale (_T('cinotif:suivi_notifications'), generer_url_ecrire("cinotif_suivinotifications"), "article-24.png", "", false);
	} else {
		$rac = icone_horizontale (_T('cinotif:titre'), generer_url_ecrire("cinotif_config"), "administration-24.gif", "", false);
		if ($aide)
			$rac .= icone_horizontale (_T('cinotif:config_titre_aide'), generer_url_ecrire("cinotif_config_aide"), "administration-24.gif", "", false);
		$rac .= icone_horizontale (_T('cinotif:suivi_abonnes'), generer_url_ecrire("cinotif_suiviabonnes"), "breve-24.gif", "", false);
		$rac .= icone_horizontale (_T('cinotif:suivi_abonnements'), generer_url_ecrire("cinotif_suiviabonnements"), "breve-24.gif", "", false);
		$rac .= icone_horizontale (_T('cinotif:suivi_notifications'), generer_url_ecrire("cinotif_suivinotifications"), "breve-24.gif", "", false);
	}
	
	return bloc_des_raccourcis($rac);
}

?>