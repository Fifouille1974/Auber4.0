<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */


if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('ecrire/inc/instituer_auteur');

if (defined('_DIR_PLUGIN_CIAR'))
	include_spip('ciar_fonctions');

if (defined('_DIR_PLUGIN_CIAG'))
	include_spip('inc/ciag_commun');
	
	

//  affiche le statut de l'auteur dans l'espace prive
// les admins voient et peuvent modifier les droits d'un auteur
// les admins restreints les voient mais 
// ne peuvent les utiliser que pour mettre un auteur a la poubelle

// http://doc.spip.org/@inc_instituer_auteur_dist
if(!function_exists('inc_instituer_auteur')) {
function inc_instituer_auteur($auteur, $modif = true) {

	if (!$id_auteur = intval($auteur['id_auteur'])) {
		$statut = _STATUT_AUTEUR_CREATION;
	} else
		$statut = $auteur['statut'];

	$ancre =  "instituer_auteur-" . intval($id_auteur);

	$menu = $modif ? choix_statut_auteur($statut, $id_auteur, "$ancre-aff"):traduire_statut_auteur($auteur['statut']);
	if (!$menu) return '';

	$label = $modif?'label':'b';
	$res = "<$label>" . _T('info_statut_auteur')."</$label> " . $menu;

//-------- Debut ajout CI ------
if ($GLOBALS['spip_version_affichee']>="2.1") {
//-------- Fin ajout CI ------
	if ($modif)
		$res .= editer_choix_webmestre($auteur);
	else
		$res .= afficher_webmestre($auteur);
//-------- Debut ajout CI ------
}
//-------- Fin ajout CI ------
		
	// Prepare le bloc des rubriques pour les admins eventuellement restreints ;
	// si l'auteur n'est pas '0minirezo', on le cache, pour pouvoir le reveler
	// en jquery lorsque le menu de statut change
	$vis = in_array($statut, explode(',', _STATUT_AUTEUR_RUBRIQUE))
		? ''
		: " style='display: none'";

//-------- Debut ajout CI ------
//	if ($menu_restreints = choix_rubriques_admin_restreint($auteur, $modif))
	if ($menu_restreints = ciag_choix_rubriques_admin_restreint($auteur, $modif))
//-------- Fin ajout CI ------
		$res .= "<div class='instituer_auteur' "
		  . ($modif?"id='$ancre-aff'":'') // seul le bloc en modification necessite un id
		  . "$vis>"
			. $menu_restreints
			. "</div>";

	return $res;
}
}

function ciag_afficher_rubriques_admin_restreintes($auteur, $modif = true){
	global $spip_lang;

	$id_auteur = intval($auteur['id_auteur']);

//-------- Debut ajout CI ------
	if (spip_version()>=3)
		$result = sql_select("rubriques.id_rubrique, " . sql_multi ("titre", $spip_lang) . "", "spip_auteurs_liens AS lien LEFT JOIN spip_rubriques AS rubriques ON lien.id_objet=rubriques.id_rubrique", "lien.objet='rubrique' AND lien.id_auteur=$id_auteur", "", "multi");
	else
//-------- Fin ajout CI ------
	$result = sql_select("rubriques.id_rubrique, " . sql_multi ("titre", $spip_lang) . "", "spip_auteurs_rubriques AS lien LEFT JOIN spip_rubriques AS rubriques ON lien.id_rubrique=rubriques.id_rubrique", "lien.id_auteur=$id_auteur", "", "multi");

//-------- Debut ajout CI ------
	$restreint_ec = '';
	$rubriques_ec = array();
	$rubriques_via_groupes = array();

	if (defined('_DIR_PLUGIN_CIAR'))
		$rubriques_ec = ciar_tableau_rub_ec();
		
	if (defined('_DIR_PLUGIN_CIAG'))
		$rubriques_via_groupes = ciag_liste_rubriques_de_auteur_via_grpauteurs($id_auteur);

//-------- Fin ajout CI ------
	
	$menu = $restreint = '';
	// L'autorisation de modifier les rubriques restreintes
	// est egale a l'autorisation de passer en admin
	$modif &= autoriser('modifier', 'auteur', $id_auteur, null, array('statut' => '0minirezo'));
	while ($row_admin = sql_fetch($result)) {
//-------- Debut ajout CI ------
/*
		$id_rubrique = $row_admin["id_rubrique"];
		$h = generer_url_ecrire('naviguer', "id_rubrique=$id_rubrique");
		$restreint .= "\n<li id='rubrest_$id_rubrique'>"
			. ($modif
				? "<input type='checkbox' checked='checked' name='restreintes[]' value='$id_rubrique' />\n"
				: ''
			)
			. "<a href='$h'>"
			. typo($row_admin["multi"])
			. "</a>"
			. '</li>';
*/
		$id_rubrique = $row_admin["id_rubrique"];
		$h = generer_url_ecrire('naviguer', "id_rubrique=$id_rubrique");		
		$ec = in_array($id_rubrique,$rubriques_ec);
		$grp = in_array($id_rubrique,$rubriques_via_groupes);

		if ($ec) {
			$restreint_ec .= "\n<li id='rubrest_$id_rubrique'><a href='$h'>".typo($row_admin["multi"])."</a></li>";	
			if ($modif)
				$restreint .= "<input type='hidden' checked='checked' name='restreintes[]' value='$id_rubrique' />\n";
		}

		if ($grp) {
			$restreint_grp .= "\n<li id='rubrest_$id_rubrique'><a href='$h'>".typo($row_admin["multi"])."</a></li>";
			if ($modif)
				$restreint .= "<input type='hidden' checked='checked' name='restreintes[]' value='$id_rubrique' />\n";
		}

		if (!$ec AND !$grp) {
			$restreint .= "\n<li id='rubrest_$id_rubrique'>"
				. ($modif
					? "<input type='checkbox' checked='checked' name='restreintes[]' value='$id_rubrique' />\n"
					: ''
				)
				. "<a href='$h'>"
				. typo($row_admin["multi"])
				. "</a>"
				. '</li>';
		}
//-------- Fin ajout CI ------
	}

//-------- Debut ajout CI ------
//	if (!$restreint) {
	if (!$restreint AND !$restreint_grp) {
//-------- Fin ajout CI ------
		$phrase = _T('info_admin_gere_toutes_rubriques')."\n";
	} else {

		$menu =  "<ul id='liste_rubriques_restreintes' style='list-style-image: url("
			. chemin_image("rubrique-12.gif")
			. ")'>"
			. $restreint
			. "</ul>\n";

//-------- Debut ajout CI ------
		if ($restreint_grp) {
			$menu .= "<p>"._T('ciag:grpauteurs_rubriques_auteur_info')."</p>";
			$menu .=  "<ul id='liste_rubriques_grp' style='list-style-image: url("
			. chemin_image("rubrique-12.gif")
			. ")'>"
			. $restreint_grp
			. "</ul><br/>\n";
		}

		if ($restreint_ec) {
			$menu .= "<p>"._T('ciar:ec_auteur_info')."</p>";
			$menu .=  "<ul id='liste_rubriques_ec' style='list-style-image: url("
			. chemin_image("rubrique-12.gif")
			. ")'>"
			. $restreint_ec
			. "</ul><br/>\n";
		}

//-------- Fin ajout CI ------
			
		// Il faut un element zero pour montrer qu'on a l'interface
		// sinon il est impossible de deslectionner toutes les rubriques
		if ($modif)
			$menu .= "<input type='hidden' name='restreintes[]' value='0' />\n";
		$phrase = _T('info_admin_gere_rubriques');
	}

//-------- Debut ajout CI ------
	$groupes = '';
	if (defined('_DIR_PLUGIN_CIAG')) {
		$grpauteurs = ciag_liste_grpauteurs_de_auteur($id_auteur);
		if ($grpauteurs) {
			$in = sql_in('id_groupe',$grpauteurs);			
			$result = sql_select("*","spip_ciag_grpauteurs",$in,"","titre");
			while ($row = sql_fetch($result))
				$groupes .= "\n<li id='grpauteurs_".$row['id_groupe']."'><a href='".generer_url_ecrire('ciag_groupe_auteurs', "id_groupe=".$row['id_groupe'])."'>".typo($row['titre'])."</a></li>";
		}
	}
		
	if ($groupes) {
		$menu .= "<p>"._T('ciag:grpauteurs_auteur_info')."</p>";
		$menu .=  "<ul id='liste_grpauteurs'>"
		. $groupes
		. "</ul><br/>\n";
	}
//-------- Fin ajout CI ------
	
	if ($auteur['statut'] != '0minirezo')
		$phrase = '';

	return "<p>$phrase</p>\n$menu";
}


function ciag_choix_rubriques_admin_restreint($auteur, $modif=true) {
	global $spip_lang;

	$id_auteur = intval($auteur['id_auteur']);
	$res = ciag_afficher_rubriques_admin_restreintes($auteur, $modif);

	// Ajouter une rubrique a un administrateur restreint
	if ($modif
	AND autoriser('modifier', 'auteur', $id_auteur, NULL, array('restreintes' => true))
	AND $chercher_rubrique = charger_fonction('chercher_rubrique', 'inc')
	AND $a = $chercher_rubrique(0, 'auteur', false)) {

		$label = $restreint
			? _T('info_ajouter_rubrique')
			: _T('info_restreindre_rubrique');

		$res .= debut_block_depliable(true,"statut$id_auteur")
		. "\n<div id='ajax_rubrique' class='arial1'>\n"
		. "<b>"
		. $label 
		. "</b>"
		. "\n<input name='id_auteur' value='"
		. $id_auteur
		. "' type='hidden' />"
		. $a
		. "</div>\n"

		// onchange = pour le menu
		// l'evenement doit etre provoque a la main par le selecteur ajax
		. "<script type='text/javascript'><!--
		jQuery('#id_parent')
		.bind('change', function(){
			var id_parent = this.value;
			var titre = jQuery('#titreparent').attr('value') || this.options[this.selectedIndex].text;
			// Ajouter la rubrique selectionnee au formulaire,
			// sous la forme d'un input name='rubriques[]'
			var el = '<input type=\'checkbox\' checked=\'checked\' name=\'restreintes[]\' value=\''+id_parent+'\' /> ' + '<a href=\'?exec=naviguer&amp;id_rubrique='+id_parent+'\'>'+titre+'</a>';
			if (jQuery('#rubrest_'+id_parent).size() == 0) {
				jQuery('#liste_rubriques_restreintes')
				.append('<li id=\'rubrest_'+id_parent+'\'>'+el+'</li>');
			}
		}); //--></script>\n"

		. fin_block();
	}

	return $res;
}

?>