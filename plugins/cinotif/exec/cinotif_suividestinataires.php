<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cinotif_commun');


function exec_cinotif_suividestinataires(){

	$id_courrier = intval(_request('id_courrier'));
	
	if (!$id_courrier OR !autoriser('configurer')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre(_T('cinotif:suivi_destinataires'),'', false);
		
		echo debut_gauche('', true);
		$cinotif_navigation = charger_fonction('cinotif_navigation', 'configuration');
	  	echo $cinotif_navigation();
		
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		
		$contexte = array();
		$row = sql_fetsel("id_courrier,destinataires", "spip_cinotif_courriers", "id_courrier=".intval($id_courrier));
		if ($row) {
			$contexte['id_courrier'] = $row['id_courrier'];
			$contexte['destinataires'] = explode(',',$row['destinataires']);
		}		

		
		$sympa = false;
		if (cinotif_type_notification_actif()=='sympa'){
			$sympa = true;
		}
			
		echo debut_cadre_relief('',true,'',_T('cinotif:suivi_destinataires'));
		if ($sympa)
			echo recuperer_fond("prive/objets/liste/cinotif_suividestinataires_sympa", $contexte);
		else
			echo recuperer_fond("prive/objets/liste/cinotif_suividestinataires", $contexte);
		
		echo fin_cadre_relief(true);
		
		echo  "<br />\n";		
		echo fin_gauche(), fin_page();
	}
}

?>