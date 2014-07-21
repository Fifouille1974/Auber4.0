<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/ciag_commun');


function exec_ciag_liste_auteur_rubriques(){
	
	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
	
	
	echo debut_gauche('', true);
	$ciag_navigation = charger_fonction('ciag_navigation', 'configuration');
		echo $ciag_navigation();
	
	echo creer_colonne_droite('', true);
	echo debut_droite('', true);
	
	echo '<style type="text/css"><!-- #contenu a {color: #000000;} --></style>';
	echo gros_titre(_T('ciag:liste_auteur_rubriques'),'', false);
	echo "<br /><br />";
	
	echo debut_cadre_relief('',true,'',_T('ciag:liste_auteur_rubriques'));
	
	
	echo recuperer_fond("prive/objets/liste/ciag_auteur_rubriques", $contexte);
	
	echo fin_cadre_relief(true);
	
	
	echo  "<br />\n";
	
	echo fin_gauche(), fin_page();
}


function ciag_titres_rubriques_de_auteur($id_auteur) {
	$return = '';

	if (spip_version()>=3)
		$exec = "rubrique";
	else
		$exec = "naviguer";
	
	if ($id_auteur = intval($id_auteur)) {
		$rubriques = ciag_liste_rubriques_de_auteur($id_auteur);
		if ($rubriques) {
			// si plugin ciar, tenir compte des EC
			$rubriques_exclues = array();
			if (defined('_DIR_PLUGIN_CIAR')){
				include_spip('ciar_fonctions');
				$rubriques_exclues = ciar_accessrubec();
				$rubriques = array_diff($rubriques,$rubriques_exclues);
			}
			
			$in = sql_in('id_rubrique',$rubriques);
			$result = sql_select('id_rubrique,titre', "spip_rubriques", $in);
			while ($row = sql_fetch($result))
				$return .= '<div><a href="'.generer_url_ecrire($exec,"id_rubrique=".$row['id_rubrique']).'">'.interdire_scripts(typo(extraire_multi($row['titre']))).'</a></div>';

		}
	}
	return $return;
}

?>