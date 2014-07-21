<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cinotif_commun');


function exec_cinotif_suiviabonnements_rubrique(){
	
	$id_rubrique= intval(_request('id_rubrique'));
	
	if ($id_rubrique) {
		$row = sql_fetsel("id_rubrique,titre", "spip_rubriques", "id_rubrique=$id_rubrique");
		if ($row) {
			$id_rubrique = $row['id_rubrique'];
			$type = $row['titre'];
			$titre = typo($type);
		}
	}

	if (!$id_rubrique OR !autoriser('modifier','rubrique',$id_rubrique)) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre($titre,'', false);
		
		echo debut_gauche('', true);
//		$cinotif_navigation = charger_fonction('cinotif_navigation', 'configuration');
//	  	echo $cinotif_navigation();
		
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
				
//		echo debut_cadre_relief('',true,'',_T('cinotif:suivi_abonnements_rubrique'));

		if (spip_version()>=3)
			$exec = "rubrique";
		else
			$exec = "naviguer";
	
		$url = generer_url_ecrire($exec,"id_rubrique=$id_rubrique");

		if (spip_version()>=3)
			$icone_retour = icone_verticale(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_right']);
		else
			$icone_retour = icone_inline(_T('icone_retour'), $url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_right']);


		echo '<div class="cadre cadre-r">';	
		echo '<div class="titrem">'._T('cinotif:suivi_abonnements_rubrique').'</div>';
		echo "<div class='bandeau_actions' style=''>$icone_retour</div><div class='nettoyeur'></div>";
		echo '<div class="cadre_padding">';	
			
		$rub_ec = '';
		if (defined('_DIR_PLUGIN_CIAR')){
			if (ciar_rub_ec($id_rubrique))
				$rub_ec = 'oui';	
		}
			
		$contexte = array(
			'icone_retour'=>$icone_retour,
			'titre'=>$type,
			'redirect'=>$url,
			'new'=> $id_rubrique,
			'rub_ec' => $rub_ec,
			'config_fonc'=>''
		);
		
		echo recuperer_fond("prive/objets/liste/cinotif_suiviabonnements_rubrique", $contexte);
		
		echo "<div class='nettoyeur'></div></div></div>";

		echo  "<br />\n";
		
		echo fin_gauche(), fin_page();
	}
}

?>