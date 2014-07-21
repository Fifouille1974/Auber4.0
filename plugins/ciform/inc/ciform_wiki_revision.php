<?php
/**
 * Plugin ciform
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function ciform_articles_versions($id_article,$version,$id_rubrique,$titre,$statut_article,$lang) {
	global $spip_lang_left, $spip_lang_right;

	$return = "";
	$id_version = intval(_request('id_version'));
	$id_diff = intval(_request('id_diff'));
		
	// recuperer les donnees actuelles de l'article
	$id_article = intval($id_article);
	$id_rubrique = intval($id_rubrique);
	
	include_spip('inc/presentation');
	include_spip('inc/suivi_versions');
	include_spip('inc/revisions');


	// recuperer les donnees versionnees
	$last_version = false;
	if (!$id_version) {
		$id_version = intval($version);
		$last_version = true;
	}
	$textes = revision_comparee($id_article, $id_version, 'complet', $id_diff);

	unset($id_rubrique); # on n'en n'aura besoin que si on affiche un diff


	// Titre

	$debut = $corps = '';

	if (is_array($textes))
	foreach ($textes as $var => $t) {
		switch ($var) {
			
			case 'id_rubrique':
			case 'surtitre':
			case 'soustitre':
			case 'descriptif':
			case 'ps':
			case 'url_site':
			case 'nom_site':
				$debut .= "";
				break;

			case 'titre':
				$debut .= "<h2>".propre_diff($t)."</h2>";
				break;

			default:
				$corps .= "<div dir='$lang_dir' class='champ contenu_$var'>"
					. "<div class='label'>$var</div>"
					. "<div class='$var'>"
					. propre_diff($t)
					. "</div></div>\n";
				break;
		}
	}

//	$return = '<div id="contenu">';
	$return = '';

	if ($debut) {
		$return .= ciform_revision_debut_cadre();
	
		$return .= "\n<table id='diff' cellpadding='0' cellspacing='0' border='0' width='100%'>";
		$return .= "<tr><td style='width: 100%' valign='top'>";
		$return .= $debut;
		$return .= "</td><td>";
	
		$return .= "</td>";
	
		$return .= "</tr></table>";
	
		$return .= ciform_revision_fin_cadre(true);


		//////////////////////////////////////////////////////
		// Affichage des versions
		//
		$result = sql_select("id_version, titre_version, date, id_auteur",
			"spip_versions",
			"id_article=".sql_quote($id_article)." AND  id_version>0",
			"", "id_version DESC");
	
		$return .= ciform_revision_debut_cadre();
	
		$zapn = 0;
		$lignes = array();
		$points = '...';
		$tranches = 10;
		while ($row = sql_fetch($result)) {
	
			$res = '';
			// s'il y en a trop on zappe a partir de la 10e
			// et on s'arrete juste apres celle cherchee
			if ($zapn++ > $tranches
			AND abs($id_version - $row['id_version']) > $tranches<<1) {
				if ($points) {
					$lignes[]= $points;
					$points = '';
				}
				if ($id_version > $row['id_version']) break;
				continue;
			}
	
			$date = affdate_heure($row['date']);
			$version_aff = $row['id_version'];
			$titre_version = typo($row['titre_version']);
			$titre_aff = $titre_version ? $titre_version : $date;
			if ($version_aff != $id_version) {
				$lien = parametre_url(self(), 'id_version', $version_aff);
				$lien = parametre_url($lien, 'id_diff', '');
				$res .= "<a href='".($lien.'#diff')."' title=\""._T('info_historique_affiche')."\">$titre_aff</a>";
			} else {
				$res .= "<b>$titre_aff</b>";
			}
	
			if (is_numeric($row['id_auteur'])
			AND $t = sql_getfetsel('nom', 'spip_auteurs', "id_auteur=" . intval($row['id_auteur']))) {
					$res .= " (".typo($t).")";
				} else {
					$res .= " (".$row['id_auteur'].")"; 
			}
	
			if ($version_aff != $id_version) {
			  $res .= " <span class='verdana2'>";
			  if ($version_aff == $id_diff) {
				$res .= "<b>("._T('info_historique_comparaison').")</b>";
			  } else {
				$lien = parametre_url(self(), 'id_version', $id_version);
				$lien = parametre_url($lien, 'id_diff', $version_aff);
				$res .= "(<a href='".($lien.'#diff').
				"'>"._T('info_historique_comparaison')."</a>)";
			  }
			$res .= "</span>";
			}
			$lignes[]= $res;
		}
		if ($lignes) {
			$return .= "<ul class='verdana3'><li>\n";
			$return .= join("\n</li><li>\n", $lignes);
			$return .= "</li></ul>\n";
		}
	
		//////////////////////////////////////////////////////
		// Corps de la version affichee
		//
		$return .= "\n\n<div id='wysiwyg' style='text-align: justify;'>".echappe_js($corps);
	
		// notes de bas de page
		if (strlen($GLOBALS['les_notes']))
			$return .= "<div class='champ contenu_notes'>
				<div class='label'>"._T('info_notes')."</div>
				<div class='notes' dir='$lang_dir'>"
				.$GLOBALS['les_notes']
				."</div></div>\n";
	
		$return .= "</div>\n";
	
		$return .= ciform_revision_fin_cadre();
	
	} else {
		$return = '<br /><div class="texte">'._T('avis_aucun_resultat').'</div>';
	}	
	//	$return .= '</div>'; // /#contenu
	
	return $return;
}


function ciform_revision_debut_cadre(){
	return '<div class="cadre-revision"><div class="cadre-padding">';
}


function ciform_revision_fin_cadre(){
	return '<div class="nettoyeur"></div></div></div>';
}

?>