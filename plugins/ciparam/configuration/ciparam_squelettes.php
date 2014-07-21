<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/config');
include_spip('inc/ciparam_inc_logos');
include_spip('inc/plugin');
include_spip('inc/ciparam_inc_commun');
include_spip('inc/ciparam_inc_meta');


function configuration_ciparam_squelettes_dist(){
	global $spip_lang_left;
	
	$ci_motcle_accueil = entites_html(ciparam_lire_meta("ci_motcle_accueil"));	
	$ci_nom_bandeau = entites_html(ciparam_lire_meta("ci_nom_bandeau"));	
	$ci_abonnement_xiti = entites_html(trim(ciparam_lire_meta("ci_abonnement_xiti")));	
	$ci_xiti_rss = entites_html(trim(ciparam_lire_meta("ci_xiti_rss")));	
	$ci_xiti_email = entites_html(trim(ciparam_lire_meta("ci_xiti_email")));	
	$ci_nbactu = entites_html(ciparam_lire_meta("ci_nbactu"));
	$ci_menu_niveau = entites_html(ciparam_lire_meta("ci_menu_niveau"));	
	$ci_haut = entites_html(ciparam_lire_meta("ci_haut"));
	
	$action = generer_url_ecrire('ciparam_config');
	
	$class3 = '';
	if (spip_version()>=3) {
		$class3 = ' text';
		$res .= '<div class="formulaire_spip">';
	}
	
	$res .= "<form action='$action' method='post'><div>".form_hidden($action)
	. "<input type='hidden' name='changer_config' value='oui' /></div>";

	$res .= "\n<table>";
	
    // Mots dans les META de la page d'accueil
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='ci_motcle_accueil'>"._T('ciparam:texte_motcle_accueil')."</label><br />"
	. " <input type='text' name='ci_motcle_accueil' id='ci_motcle_accueil' value=\"$ci_motcle_accueil\" size='40' class='formo$class3' /><br />"
	. "</td></tr>";


	// Nom du site dans le bandeau
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='ci_nom_bandeau'>"._T('ciparam:texte_nom_bandeau')."</label><br />"
	. " <input type='text' name='ci_nom_bandeau' id='ci_nom_bandeau' value=\"$ci_nom_bandeau\" size='40' class='formo$class3' /><br />"
	. "</td></tr>";

	
    // Xiti
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='ci_abonnement_xiti'>"._T('ciparam:texte_abonnement_xiti')."</label><br />";
		// s'il existe un fichier de parametrage xiti avec un contenu différent de la valeur par defaut, alors lecture seule	
		if (ciparam_lire_config_fichier('cixiti')!="" AND !preg_match(",_images,",ciparam_lire_config_fichier('cixiti'))) {
			$res .= ciparam_lire_config_fichier("cixiti")."<br />";
		} else {
			$res .= "<input type='text' name='ci_abonnement_xiti' id='ci_abonnement_xiti' value=\"$ci_abonnement_xiti\" size='40' class='formo$class3' /><br />";
		}	
		$res .= "</td></tr>";
	
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='ci_xiti_rss'>"._T('ciparam:texte_xiti_rss')."</label><br />";
		// s'il existe un fichier de parametrage xiti_rss avec un contenu différent de la valeur par defaut, alors lecture seule	
		if (defined('_CI_XITI_RSS')) {
			$res .= _CI_XITI_RSS."<br />";
		} else {
			$res .= "<input type='text' name='ci_xiti_rss' id='ci_xiti_rss' value=\"$ci_xiti_rss\" size='40' class='formo$class3' /><br />";
		}	
		$res .= "</td></tr>";

	if (defined('_DIR_PLUGIN_CINOTIF')){
		$res .= "\n<tr><td class='verdana2'>"
		. "<label for='ci_xiti_email'>"._T('ciparam:texte_xiti_email')."</label><br />";
			// s'il existe un fichier de parametrage xiti_email avec un contenu différent de la valeur par defaut, alors lecture seule	
		if (defined('_CI_XITI_EMAIL')) {
			$res .= _CI_XITI_EMAIL."<br />";
		} else {
			$res .= "<input type='text' name='ci_xiti_email' id='ci_xiti_email' value=\"$ci_xiti_email\" size='40' class='formo$class3' /><br />";
		}	
		$res .= "</td></tr>";
	}

    // Nombre d'actu
	$res .= "\n<tr><td class='verdana2'>"
	. "<label for='ci_nbactu'>"._T('ciparam:texte_nbactu')."</label><br />"
	. " <input type='text' name='ci_nbactu' id='ci_nbactu' value=\"$ci_nbactu\" size='40' class='formo$class3' /><br />"
	. "</td></tr>";

	$res .= "\n</table>\n";
	
	
    // Nombre de niveaux de menu
	$res .= ciparam_choix_menu_niveau();


    // Haut de page (largeur de menu)
	$res .= ciparam_choix_haut();

	
    // Masquer certains elements
	$res .= ciparam_choix_masquer();


    // Types de colonnes
    if(ciparam_chemin_param_xml("navigations-rubriques")
    	OR ciparam_chemin_param_xml("navigations-articles")
    	OR ciparam_chemin_param_xml("extras-rubriques")
    	OR ciparam_chemin_param_xml("extras-articles")) {

		$res .= debut_cadre_relief("", true, "", _T('ciparam:texte_colonne'));
	    $res .= ciparam_choix_extra_navigation('rubrique','navigation');
	    $res .= "<br />\n".ciparam_choix_extra_navigation('rubrique','extra');
	    $res .= "<br />\n".ciparam_choix_extra_navigation('article','navigation');
	    $res .= "<br />\n".ciparam_choix_extra_navigation('article','extra');
		$res .= fin_cadre_relief(true);
   	}


	// Sauts de lignes consecutifs
	$res .= ciparam_choix_sauts_lignes();

	
	$res .= '<div><input type="submit" class="fondo" style="float: right;" value="Valider"/></div>'
	. "</form>";
	
	if (spip_version()>=3)
		$res .= '</div>';
		
	$res = debut_cadre_trait_couleur("", true, "", _T('ciparam:titre'))
	. $res
	. fin_cadre_trait_couleur(true);
	
	
	return $res;
}


function ciparam_choix_menu_niveau() {
	$texte = "";
	$valeur = "";
	
	$valeur = ciparam_lire_meta("ci_menu_niveau");	
	$niveaux = ciparam_charger_param_xml("menu-niveaux");
	
	if ($niveaux) {
		$texte .= debut_cadre_relief("", true, "", _T('ciparam:info_menu_niveau'));
		
		$texte .= "<label for='ci_menu_niveau'>"._T('ciparam:texte_menu_niveau')."</label><br />"
	    . "\n<select name='ci_menu_niveau' id='ci_menu_niveau' class='formo'>\n";

    	if (!$valeur) 
    		$selected = "selected='selected'";

//		$texte .= "<option value='' ".$selected."></option>\n";
	    
	    foreach ($niveaux as $niveau) {
	    	if ($valeur AND $valeur==$niveau['nom']) 
	    		$selected = "selected='selected'";
	    	else
		    	$selected = "";

	    	$nom = $niveau['nom'];
	    	$description = $niveau['description'];
    		$texte .= "<option value='".$nom."' $selected>".$description."</option>\n";
	    }
	    $texte .="</select><br />\n";
		$texte .= fin_cadre_relief(true);
	}
	
	return $texte;  
}	


function ciparam_choix_haut() {
	$texte = "";
	$valeur = "";
	
	$valeur = ciparam_lire_meta("ci_haut");	
	$hauts = ciparam_charger_param_xml("hauts");

	if ($hauts) {
		$texte .= debut_cadre_relief("", true, "", _T('ciparam:info_choix_haut'));
		
		$texte .= "<label for='ci_haut'>"._T('ciparam:texte_haut')."</label><br />"
	    . "\n<select name='ci_haut' id='ci_haut' class='formo'>\n";

    	if (!$valeur) 
    		$selected = "selected='selected'";

		$texte .= "<option value='' ".$selected."></option>\n";
	    
	    foreach ($hauts as $haut) {
	    	if ($valeur AND $valeur==$haut['nom']) 
	    		$selected = "selected='selected'";
	    	else
		    	$selected = "";

	    	$nom = $haut['nom'];
	    	$description = $haut['description'];
    		$texte .= "<option value='".$nom."' $selected>".$description."</option>\n";
	    }
	    $texte .="</select><br />\n";
		$texte .= fin_cadre_relief(true);
	}
	
	return $texte;  
}	

function ciparam_choix_masquer() {
	$texte = "";
	$masquers = ciparam_charger_param_xml("masquer");

	if ($masquers) {
		$valeurs = ciparam_lire_meta("ci_masquer");
		if (!$valeurs)
			$valeurs = array();

		$texte .= debut_cadre_relief("", true, "", _T('ciparam:info_choix_masquer'));
		
		foreach ($masquers as $masquer) {
			$id = 'ci_masquer_' . $masquer['nom'];
			$vu = in_array($masquer['nom'],$valeurs);
			$texte .= "<input type='checkbox' name='ci_masquer[]' value='".$masquer['nom']."' id='$id'"
				. ($vu ? " checked='checked'" : '')
				. " /> <label for='$id'>"
				. ($vu ? "<b>".$masquer['description']."</b>" : $masquer['description'])
				.  "</label><br />";
		}
	
		$texte .= fin_cadre_relief(true);
	}	

	return $texte;
}	


// exemple : $type_colonne = extra
//			 $objet = article
function ciparam_choix_extra_navigation($objet,$type_colonne) {
	$texte = "";
	$valeur = "";
	
	if ($objet AND $type_colonne) {
		$valeur = ciparam_lire_meta("ci_".$type_colonne."_".$objet);			
		$colonnes = ciparam_charger_param_xml($type_colonne."s-".$objet."s");
    	$colonnes = ciparam_order_array($colonnes,'description');

		if ($colonnes) {
			$champ = "ci_".$type_colonne."_".$objet;
			
			$texte .= "<label for='$champ'>"._T('ciparam:info_'.$type_colonne."_".$objet)."</label><br />";
		    if (ciparam_lire_config_fichier("ci".$type_colonne.$objet)!="") {
		    	$texte .=ciparam_lire_config_fichier("ci".$type_colonne.$objet);
		    } else {		
				$texte .= "\n<select name='".$champ."' id='".$champ."' class='formo'>\n";
				
		    	if (!$valeur) 
		    		$selected = "selected='selected'";

	    		$texte .= "<option value='' ".$selected."></option>\n";
			
			    foreach ($colonnes as $colonne) {
			    	if ($valeur AND $valeur==$colonne['nom']) 
			    		$selected = "selected='selected'";
			    	else
				    	$selected = "";
	
			    	$nom = $colonne['nom'];
			    	$description = $colonne['description'];
		    		$texte .= "<option value='".$nom."' $selected>".$description."</option>\n";
			    }
			    $texte .="</select>\n";
		    }
		}
	}
	
	return $texte;  
}	

// parametrage par fichier
function ciparam_lire_config_fichier($nom) {

	$return = '';
	$fichier = '_config_'.$nom.'.php';
	if (in_array($nom,array('cigaucherubrique','cigauchearticle')))	
		$fichier = '_config_css.php';
	
	$f = _DIR_RACINE . _NOM_PERMANENTS_INACCESSIBLES . $fichier;

	if (@file_exists($f)) {
		include_once($f);
		if (isset($GLOBALS['ciconfig'][$nom]))
			$return = $GLOBALS['ciconfig'][$nom];
	}

	return $return;
}

function ciparam_choix_sauts_lignes() {
	$nom = 'ci_sauts_lignes';
	$valeurs = array('oui' => _T('item_oui'),'non' => _T('item_non'));
	if (!$valeur_actuelle = ciparam_lire_meta("ci_sauts_lignes"))
		$valeur_actuelle = 'non';
	
	$texte .= debut_cadre_relief("", true, "", _T('ciparam:sauts_lignes'));
	
	$choix = array();
	while (list($valeur, $titre) = each($valeurs)) {
		$choix[] = ciparam_bouton_radio($nom, $valeur, $titre, $valeur == $valeur_actuelle);
	}
	$texte .= "\n".join("<br />", $choix);
	
	$texte .= fin_cadre_relief(true);

	return $texte;	
}

function ciparam_bouton_radio($nom, $valeur, $titre, $actif = false, $disabled = false) {
	static $id_label = 0;
	
	if ($disabled) $option = " disabled='disabled'";
	else $option = "";
    
	$texte = "<input type='radio' name='$nom' value='$valeur' id='label_${nom}_${id_label}'$option";
	if ($actif) {
		$texte .= ' checked="checked"';
		$titre = '<b>'.$titre.'</b>';
	}
	$texte .= " /> <label for='label_${nom}_${id_label}'>$titre</label>\n";
	$id_label++;
	return $texte;
}

?>