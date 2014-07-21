<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cinotif_commun');


function exec_cinotif_suiviabonnements(){
	
	if (!autoriser('configurer')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

		$contexte = array();
		$grostitre = _T('cinotif:suivi_abonnements');
		$titre = $grostitre;
		if ($id_abonne = intval(_request('id_abonne'))) {
			$contexte['id_abonne'] = $id_abonne;
			$grostitre = _T('cinotif:abonnements');
			$titre = _T('cinotif:abonnements_de');
		}
			
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		if (spip_version()<3) {
			echo "<br />\n";
			echo gros_titre($grostitre,'', false);
		}
		
		echo debut_gauche('', true);
		$cinotif_navigation = charger_fonction('cinotif_navigation', 'configuration');
	  	echo $cinotif_navigation();
		
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		
		if (spip_version()>=3)
			echo gros_titre($grostitre,'', false);

		echo debut_cadre_relief('',true,'',$titre);
		if ($id_abonne){
			echo recuperer_fond("prive/objets/liste/cinotif_suiviabonnements_abonne", $contexte);
		} else {
			if (defined('_DIR_PLUGIN_CIMS') AND defined('_CIMS_ACTIF'))
				echo recuperer_fond("prive/objets/liste/cinotif_suiviabonnements_cims", $contexte);
			else
				echo recuperer_fond("prive/objets/liste/cinotif_suiviabonnements", $contexte);
		}

		echo fin_cadre_relief(true);
		
		echo  "<br />\n";
		echo fin_gauche(), fin_page();
	}
}

?>