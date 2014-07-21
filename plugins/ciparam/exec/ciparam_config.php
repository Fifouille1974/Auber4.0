<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/filtres');

function ciparam_appliquer_modifs_config() {
	include_spip('inc/meta');
	include_spip('base/ciparam_meta');
	include_spip('inc/ciparam_inc_meta');
	include_spip('ciparam_fonctions');

	$liste_meta = ciparam_tab_meta_ciparam();
	$tableau = array();
	
	foreach($liste_meta as $cle=>$val)
		$tableau[$cle] = _request($cle);
	
	ecrire_meta('ciparam', @serialize($tableau));
	
	return true;
}

function exec_ciparam_config(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		
		if (_request('changer_config') == 'oui')
			ciparam_appliquer_modifs_config();
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre(_T('ciparam:titre'),'', false);
		
		echo debut_gauche('', true);
		$ciparam_navigation = charger_fonction('ciparam_navigation', 'configuration');
	  	echo $ciparam_navigation();
	
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
	
		$ciparam_squelettes = charger_fonction('ciparam_squelettes', 'configuration');
		echo  $ciparam_squelettes(), "<br />\n";

		// offrir un pipeline
		$res = pipeline('affiche_milieu',array('args'=>array('exec'=>'ciparam_config'),'data'=>''));
		if ($res)
			echo $res;
		
		

		// Reservation pour le multisite
		if (function_exists('cims_num_site'))
			// Le logo du site, c'est site{on,off}-numerodesite.{gif,png,jpg}
			$id_logo_site = -intval(cims_num_site());
		else
			// Le logo de notre site, c'est site{on,off}0.{gif,png,jpg}
			$id_logo_site = 0;

		if (spip_version()>=3) {	
			echo recuperer_fond('prive/editer/ciparam_logo_site',array('id'=>$id_logo_site));
		} else {
			$iconifier = charger_fonction('iconifier', 'inc');
			echo $iconifier('id_syndic', $id_logo_site, 'ciparam_config', true);
		}
	
		echo fin_gauche(), fin_page();
	}
}

?>