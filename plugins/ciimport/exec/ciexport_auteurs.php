<?php
/**
 * Plugin ciimport
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


function exec_ciexport_auteurs(){
	
	if (!autoriser('configurer')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

		$choixstatut = '';
		$contexte = array('statut'=>'');
		// possibilite de demander un statut particulier
		if ($choixstatut=_request('statut')) {
			if (in_array($choixstatut, array('0minirezo','1comite','5poubelle','6forum')))
				$contexte = array('statut'=>$choixstatut);
		}

		if ($choixstatut=='6forum')
			$grostitre = _T('ciimport:icone_export_visiteurs');
		else
			$grostitre = _T('ciimport:icone_export_auteurs');
		
			
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre($grostitre,'', false);
		
		echo debut_gauche('', true);
		
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		
		echo debut_cadre_relief('',true,'',$titre);
		
		echo recuperer_fond("prive/objets/liste/ciexport_auteurs", $contexte);

		echo fin_cadre_relief(true);
		
		echo  "<br />\n";
		echo fin_gauche(), fin_page();
	}
}

?>