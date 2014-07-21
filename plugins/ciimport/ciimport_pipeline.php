<?php
/**
 * Plugin ciimport : Importation d'auteurs et de mots-cles
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * ajoute un bloc dans la colonne de gauche de la page auteurs
 *
 * @param array
 * @return array
 */
function ciimport_affiche_gauche($flux) {
	global $connect_statut, $connect_toutes_rubriques;

	$exec = $flux["args"]["exec"];
	
	if (spip_version()>=3) {
		include_spip('inc/presentation');
		include_spip('inc/filtres_boites');
		if ($exec == "auteurs") {
			if ($connect_statut == '0minirezo' AND $connect_toutes_rubriques) {
				$ret = boite_ouvrir('', 'raccourcis');
				$ret .= icone_horizontale(_T('ciimport:icone_import_auteurs'), generer_url_ecrire("ciimport_auteurs", ''), "breve-24.gif", "creer.gif", false);
				$ret .= icone_horizontale(_T('ciimport:icone_export_auteurs'), generer_url_ecrire("ciexport_auteurs", ''), "breve-24.gif", "", false);
				$ret .= icone_horizontale(_T('ciimport:icone_export_visiteurs'), generer_url_ecrire("ciexport_auteurs", 'statut=6forum'), "breve-24.gif", "", false);
				$ret .= boite_fermer();
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
			}
		}

		if ($exec == "mots") {
			if ($connect_statut == '0minirezo' AND $connect_toutes_rubriques) {
				$ret = boite_ouvrir('', 'raccourcis');
				$ret .= icone_horizontale(_T('ciimport:icone_import_motscles'), generer_url_ecrire("ciimport_motscles", ''), "breve-24.gif", "creer.gif", false);
				$ret .= icone_horizontale(_T('ciimport:icone_export_motscles'), generer_url_ecrire("ciexport_motscles", ''), "breve-24.gif", "creer.gif", false);
				$ret .= boite_fermer();
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
			}
		}
		
		if ($exec == "controler_forum") {
	      	$objet = $flux["args"]["objet"];
	      	$id_objet = $flux["args"]["id_objet"];
	      	if (autoriser('modererforum', $objet, $id_objet)){
				$flux["data"] .= bloc_des_raccourcis(
				icone_horizontale(_T('ciimport:icone_export_forum_publie'), generer_url_ecrire("ciexport_forum", "objet=$objet&id_objet=$id_objet"), "breve-24.gif", "creer.gif", false)
				.icone_horizontale(_T('ciimport:icone_export_forum_tous'), generer_url_ecrire("ciexport_forum", "objet=$objet&id_objet=$id_objet&statut=tous"), "breve-24.gif", "creer.gif", false)
				.icone_horizontale(_T('ciimport:icone_export_forum_nonpublie'), generer_url_ecrire("ciexport_forum", "objet=$objet&id_objet=$id_objet&statut=nonpublie"), "breve-24.gif", "creer.gif", false)
				);
	      	}
		}		

	} else {
		
		if ($exec == "auteurs") {			
			if ($connect_statut == '0minirezo' AND $connect_toutes_rubriques) {
				$flux["data"] .= bloc_des_raccourcis(
				icone_horizontale(_T('ciimport:icone_import_auteurs'), generer_url_ecrire("ciimport_auteurs", ''), "breve-24.gif", "creer.gif", false)
				.icone_horizontale(_T('ciimport:icone_export_auteurs'), generer_url_ecrire("ciexport_auteurs", ''), "breve-24.gif", "", false)
				.icone_horizontale(_T('ciimport:icone_export_visiteurs'), generer_url_ecrire("ciexport_auteurs", 'statut=6forum'), "breve-24.gif", "", false)
				);
			}
		}
		
		if ($exec == "mots_tous") {
			if ($connect_statut == '0minirezo' AND $connect_toutes_rubriques) {
				$flux["data"] .= bloc_des_raccourcis(
				icone_horizontale(_T('ciimport:icone_import_motscles'), generer_url_ecrire("ciimport_motscles", ''), "breve-24.gif", "creer.gif", false)
				.icone_horizontale(_T('ciimport:icone_export_motscles'), generer_url_ecrire("ciexport_motscles", ''), "breve-24.gif", "creer.gif", false)
				);
			}
		}
		
		if ($exec == "articles_forum") {
			$objet = 'article';
	      	$id_objet = $flux["args"]["id_article"];
	      	if (autoriser('modererforum', $objet, $id_objet)){
				$flux["data"] .= bloc_des_raccourcis(
				icone_horizontale(_T('ciimport:icone_export_forum_publie'), generer_url_ecrire("ciexport_forum", "objet=$objet&id_objet=$id_objet"), "breve-24.gif", "creer.gif", false)
				.icone_horizontale(_T('ciimport:icone_export_forum_tous'), generer_url_ecrire("ciexport_forum", "objet=$objet&id_objet=$id_objet&statut=tous"), "breve-24.gif", "creer.gif", false)
				.icone_horizontale(_T('ciimport:icone_export_forum_nonpublie'), generer_url_ecrire("ciexport_forum", "objet=$objet&id_objet=$id_objet&statut=nonpublie"), "breve-24.gif", "creer.gif", false)
				);
	      	}
		}

	}

  return $flux;
}

?>