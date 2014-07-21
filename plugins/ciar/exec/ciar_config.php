<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/filtres');

function exec_ciar_config(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		echo "<br />\n";
		echo gros_titre(_T('ciar:titre_liste_rubriques'),'', false);
		
		echo debut_gauche('', true);
		$ciar_navigation = charger_fonction('ciar_navigation', 'configuration');
	  	echo $ciar_navigation();
	
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
	
		echo ciar_avertissement();
		
		echo ciar_liste_acces_restreint();
		echo ciar_liste_acces_indiv();
	
	
		echo fin_gauche(), fin_page();
	}
}


function ciar_liste_acces_restreint() {
	
    $texte = "";

	if (spip_version()>=3)
		$exec = "rubrique";
	else
		$exec = "naviguer";
    
	$result = sql_select('rub.*', "spip_rubriques AS rub LEFT JOIN spip_ciar_rubriques_protection AS l ON l.id_rubrique=rub.id_rubrique", "l.acces_restreint='_acces_restreint'");
	while ($row = sql_fetch($result)) {
		$texte .= "<li><a href='".generer_url_ecrire($exec,"id_rubrique=".$row['id_rubrique'])."'>".$row['titre']."</a></li>\n";
    }
    
    if ($texte) {
		$texte = debut_cadre_relief('',true,'',_T('ciar:ar'))
	    . "<ul class='arial11'>"
	    . $texte
	    . "</ul>\n"
	    . fin_cadre_relief(true);
    }

	return $texte;  
}	


function ciar_liste_acces_indiv() {

    $texte = "";

	if (spip_version()>=3)
		$exec = "rubrique";
	else
		$exec = "naviguer";
    
    $gestionnaires = array();
	$result = sql_select('auteurs.id_auteur,auteurs.nom,l.id_rubrique', "spip_auteurs AS auteurs LEFT JOIN spip_ciar_auteurs_acces_rubriques AS l ON l.id_auteur=auteurs.id_auteur", "l.cistatut_auteur_rub='eccma'");
	while ($row = sql_fetch($result)) {
		$gestionnaires[$row['id_rubrique']][]=$row['nom'];
	}
 
	$result = sql_select('rub.*', "spip_rubriques AS rub LEFT JOIN spip_ciar_rubriques_protection AS l ON l.id_rubrique=rub.id_rubrique", "l.acces_restreint='_acces_indiv'");
	while ($row = sql_fetch($result)) {
		if (isset($gestionnaires[$row['id_rubrique']]))
			$gestionnaire = " (".implode(", ",$gestionnaires[$row['id_rubrique']]).")";
		else
			$gestionnaire = "";
		$texte .= "<li><a href='".generer_url_ecrire($exec,"id_rubrique=".$row['id_rubrique'])."'>".$row['titre']."</a>".$gestionnaire."</li>\n";
    }

    if ($texte) {
		$texte = debut_cadre_relief('',true,'',_T('ciar:ari'))
		. _T('ciar:info_gestionnaire')
	    . "<ul class='arial11'>"
	    . $texte
	    . "</ul>\n"
	    . fin_cadre_relief(true);
    }
    
	return $texte;  
}	

function ciar_avertissement() {
	global $spip_lang_right, $spip_lang_left;

	return debut_boite_info(true)
	. "<div class='verdana2' style='text-align: justify'>"
	. _T('ciar:info_fonctionnement1')
	. "</div>"
	. "<div>&nbsp;</div>"
	. "<div class='verdana2' style='text-align: justify'>"
	. _T('ciar:info_fonctionnement2')
	. "</div>"
	. "<div>&nbsp;</div>"
	. "<div class='verdana2' style='text-align: justify'>"
	. _T('ciar:info_fonctionnement3')
	. "</div>"
	. fin_boite_info(true)
	. "<div>&nbsp;</div>";
}


?>