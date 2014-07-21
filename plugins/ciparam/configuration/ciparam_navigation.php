<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

function configuration_ciparam_navigation_dist(){
	
	if (spip_version()>=3) {
		$rac = icone_horizontale (_T('ciparam:titre'), generer_url_ecrire("ciparam_config"), "configuration-24.png", "", false);
		$rac .= icone_horizontale (_T('ciparam:titre_formes'), generer_url_ecrire("ciparam_formes"), "article-24.png", "", false);
		$rac .= icone_horizontale (_T('ciparam:titre_raccourcis'), generer_url_ecrire("ciparam_raccourcis"), "article-24.png", "", false);
		$rac .= icone_horizontale (_T('ciparam:titre_tris'), generer_url_ecrire("ciparam_tris"), "article-24.png", "", false);
	} else {
		$rac = '<ul class="raccourcis_rapides"><li><a href="'.generer_url_ecrire("ciparam_config").'">'._T('ciparam:titre').'</a></li></ul>';
		$rac .= '<ul class="raccourcis_rapides"><li><a href="'.generer_url_ecrire("ciparam_formes").'">'._T('ciparam:titre_formes').'</a></li></ul>';
		$rac .= '<ul class="raccourcis_rapides"><li><a href="'.generer_url_ecrire("ciparam_raccourcis").'">'._T('ciparam:titre_raccourcis').'</a></li></ul>';
		$rac .= '<ul class="raccourcis_rapides"><li><a href="'.generer_url_ecrire("ciparam_tris").'">'._T('ciparam:titre_tris').'</a></li></ul>';
	}

	return bloc_des_raccourcis($rac);
}

?>