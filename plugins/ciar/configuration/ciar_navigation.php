<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

function configuration_ciar_navigation(){
	
	if (spip_version()>=3) {
		$rac = icone_horizontale (_T('ciar:titre_liste_rubriques'), generer_url_ecrire("ciar_config"), "rubrique-del-24.png", "", false);
		$rac .= icone_horizontale (_T('ciar:titre_protection_pj'), generer_url_ecrire("ciar_config_pj"), "configuration-24.png", "", false);
	} else {
		$rac = icone_horizontale (_T('ciar:titre_liste_rubriques'), generer_url_ecrire("ciar_config"), "cadenas-24.gif", "", false);
		$rac .= icone_horizontale (_T('ciar:titre_protection_pj'), generer_url_ecrire("ciar_config_pj"), "administration-24.gif", "", false);
	}
	
	return bloc_des_raccourcis($rac);
}

?>