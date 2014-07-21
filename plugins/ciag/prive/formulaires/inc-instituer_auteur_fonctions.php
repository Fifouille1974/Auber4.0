<?php

if (defined('_DIR_PLUGIN_CIAR'))
	include_spip('ciar_fonctions');

if (defined('_DIR_PLUGIN_CIAG'))
	include_spip('inc/ciag_commun');



function ciag_afficher_rubriques_auteur($id_auteur){
	global $spip_lang;

	$id_auteur = intval($id_auteur);

	$result = sql_select("rubriques.id_rubrique, " . sql_multi ("titre", $spip_lang) . "", "spip_auteurs_liens AS lien LEFT JOIN spip_rubriques AS rubriques ON lien.id_objet=rubriques.id_rubrique", "lien.objet='rubrique' AND lien.id_auteur=$id_auteur", "", "multi");

	$restreint_ec = '';
	$rubriques_ec = array();
	$rubriques_via_groupes = array();

	if (defined('_DIR_PLUGIN_CIAR'))
		$rubriques_ec = ciar_tableau_rub_ec();
		
	if (defined('_DIR_PLUGIN_CIAG'))
		$rubriques_via_groupes = ciag_liste_rubriques_de_auteur_via_grpauteurs($id_auteur);

	
	$menu = $restreint = '';
	$image_suppr = balise_img(chemin_image('supprimer-12.png'));
	// L'autorisation de modifier les rubriques restreintes
	// est egale a l'autorisation de passer en admin
	$modif = true;
	$modif &= autoriser('modifier', 'auteur', $id_auteur, null, array('statut' => '0minirezo'));
	
	while ($row_admin = sql_fetch($result)) {
		$id_rubrique = $row_admin["id_rubrique"];
		$url_rubrique = generer_url_ecrire('rubrique', "id_rubrique=$id_rubrique");		
		$titre_rubrique = typo($row_admin["multi"]);
		if (!$titre_rubrique)
			$titre_rubrique = _T('info_sans_titre');

		$ec = in_array($id_rubrique,$rubriques_ec);
		$grp = in_array($id_rubrique,$rubriques_via_groupes);

		
		if ($ec) {
			$restreint_ec .= "\n<li id='rubrest_$id_rubrique'><a href='$url_rubrique'>".typo($row_admin["multi"])."</a></li>";	
			if ($modif)
				$restreint .= "<input type='hidden' checked='checked' name='restreintes[]' value='$id_rubrique' />\n";
		}

		if ($grp) {
			$restreint_grp .= "\n<li id='rubrest_$id_rubrique'><a href='$url_rubrique'>".typo($row_admin["multi"])."</a></li>";
			if ($modif)
				$restreint .= "<input type='hidden' checked='checked' name='restreintes[]' value='$id_rubrique' />\n";
		}

		if (!$ec AND !$grp) {
			$restreint .= "\n<li class='rubrique'><input type='checkbox' class='checkbox' name='restreintes[]' checked='checked' value='".$id_rubrique."' />
				<label><a href='".$url_rubrique."' target='_blank'>".$titre_rubrique."</a></label>
				<a href='#' onclick='jQuery(this).parent().remove();return false;' class='removelink'>".$image_suppr."</a></li>";
		}
	}

	if (!$restreint AND !$restreint_grp) {
		$phrase = _T('info_admin_gere_toutes_rubriques')."\n";
	} else {
		$phrase = _T('info_admin_gere_rubriques')."\n";

		if ($restreint)
			$menu = "<ul id='liste_rubriques_restreintes' class='item_picked'>"
				. $restreint
				. "</ul>\n";
		else
			$menu = "<ul id='liste_rubriques_restreintes'></ul>";


		if ($restreint_grp) {
			$menu .= "<p>"._T('ciag:grpauteurs_rubriques_auteur_info')."</p>"
			. "<ul id='liste_rubriques_grp' style='list-style-image: url("
			. chemin_image("rubrique-12.gif")
			. ")'>"
			. $restreint_grp
			. "</ul><br/>\n";
		}

		if ($restreint_ec) {
			$menu .= "<p>"._T('ciar:ec_auteur_info')."</p>"
			. "<ul id='liste_rubriques_ec' style='list-style-image: url("
			. chemin_image("rubrique-12.gif")
			. ")'>"
			. $restreint_ec
			. "</ul><br/>\n";
		}

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
			
		// Il faut un element zero pour montrer qu'on a l'interface
		// sinon il est impossible de deslectionner toutes les rubriques
		if ($modif)
			$menu .= "<input type='hidden' name='restreintes[]' value='0' />\n";
	}

	if ($auteur['statut'] != '0minirezo')
		$phrase = '';

	return "<p>$phrase</p>\n$menu";
}



/**
 * Afficher le formulaire de choix de rubrique restreinte
 * pour insertion dans le formulaire
 *
 * @param int $id_auteur
 * @param string $label
 * @return string
 */
function choisir_rubriques_admin_restreint($id_auteur,$label='', $sel_css="#liste_rubriques_restreintes", $img_remove="") {
	global $spip_lang;
	$res = "";
	// Ajouter une rubrique a un administrateur restreint
	if ($chercher_rubrique = charger_fonction('chercher_rubrique', 'inc')
	  AND $a = $chercher_rubrique(0, 'auteur', false)) {

		if ($img_remove)
			$img_remove = addslashes("<a href=\"#\" onclick=\"jQuery(this).parent().remove();return false;\" class=\"removelink\">$img_remove</a>");

		$res =
		  "\n<div id='ajax_rubrique'>\n"
		. "<label>$label</label>\n"
		. "<input name='id_auteur' value='$id_auteur' type='hidden' />\n"
		. $a
		. "</div>\n"

		// onchange = pour le menu
		// l'evenement doit etre provoque a la main par le selecteur ajax
		. "<script type='text/javascript'>/*<![CDATA[*/
jQuery(function(){
	jQuery('#id_parent')
	.bind('change', function(){
		var id_parent = this.value;
		var titre = jQuery('#titreparent').attr('value') || this.options[this.selectedIndex].text;
		titre=titre.replace(/^\\s+/,'');
		// Ajouter la rubrique selectionnee au formulaire,
		// sous la forme d'un input name='rubriques[]'
		var el = '<input type=\'checkbox\' class=\'checkbox\' checked=\'checked\' name=\'restreintes[]\' value=\''+id_parent+'\' /> ' + '<label><a href=\'?exec=rubrique&amp;id_rubrique='+id_parent+'\' target=\'_blank\'>'+titre+'</a></label>';
		el = el + '$img_remove';
		if (!jQuery('$sel_css input[value='+id_parent+']').length) {
			jQuery('$sel_css').append('<li class=\"rubrique\">'+el+'</li>');
		}
	});
});
/*]]>*/</script>";

	}

	return $res;
}

?>