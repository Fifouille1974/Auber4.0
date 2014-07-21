<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;



/**
 * Ajoute un bloc dans la page naviguer
 * ajoute un bloc dans la page auteur_infos
 *
 * @param array
 * @return array
 */
function ciag_affiche_gauche($flux) {
	$exec = $flux["args"]["exec"];

	if (spip_version()>=3) {
		include_spip('inc/presentation');
		include_spip('inc/filtres_boites');
				
		if ($exec == "auteurs") {
			$ret = boite_ouvrir('', 'raccourcis');
			$ret .= icone_horizontale(_T('ciag:titre_groupes_auteurs'), generer_url_ecrire("ciag_groupes_auteurs", ''), "ciag-annonce.png", "", false);
			$ret .= boite_fermer();
			if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
				$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
			else
				$flux["data"] .= $ret;
		}
		
		if ($exec == "auteur") {
	      	$id_auteur = $flux["args"]["id_auteur"];
	      	if (autoriser('modifier', 'auteur', $id_auteur)) {
				$ret = boite_ouvrir('', 'raccourcis');
				$ret .= icone_horizontale(_T('ciag:grpauteurs_auteur'), generer_url_ecrire("ciag_gerer_grpauteurs_auteur", "id_auteur=$id_auteur"), "ciag-annonce.png", "", false);
				$ret .= boite_fermer();
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
	      	}
		}

		if ($en_cours = trouver_objet_exec($flux['args']['exec']) AND $en_cours['type']=='rubrique'){
	      	$id_rubrique = $flux["args"]["id_rubrique"];
	      	if (autoriser('modifier', 'rubrique', $id_rubrique)) {				
				$ret = boite_ouvrir('', 'raccourcis');
				$ret .= icone_horizontale(_T('ciag:grpauteurs_rubrique'), generer_url_ecrire("ciag_gerer_grpauteurs_rubrique", "id_rubrique=$id_rubrique"), "ciag-annonce.png", "", false);
				$ret .= boite_fermer();
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
	      	}
		}
		
	} else {
	
		if ($exec=="auteurs") {
			$flux["data"] .= bloc_des_raccourcis(icone_horizontale(_T('ciag:titre_groupes_auteurs'), generer_url_ecrire("ciag_groupes_auteurs", ''), "annonce.gif", "", false));
		}
		
		if ($exec=="auteur_infos") {
	      	$id_auteur = $flux["args"]["id_auteur"];
	      	if (autoriser('modifier', 'auteur', $id_auteur)) {
				$ret = icone_horizontale(_T('ciag:grpauteurs_auteur'), generer_url_ecrire("ciag_gerer_grpauteurs_auteur","id_auteur=$id_auteur"), "annonce.gif", "",false);
				$flux["data"] .= bloc_des_raccourcis($ret);
	      	}
		}
						
		if ($exec=="naviguer") {
	      	$id_rubrique = $flux["args"]["id_rubrique"];
	      	if (autoriser('modifier', 'rubrique', $id_rubrique)) {
				$ret = icone_horizontale(_T('ciag:grpauteurs_rubrique'), generer_url_ecrire("ciag_gerer_grpauteurs_rubrique","id_rubrique=$id_rubrique"), "annonce.gif", "",false);
				$flux["data"] .= bloc_des_raccourcis($ret);
	      	}
		}
	}

	return $flux;
}


/**
 * Optimiser la base de donnee en supprimant les liens des auteurs supprimes
 *
 * @param int $n
 * @return int
 */
function ciag_optimiser_base_disparus($n){

	// les liens dans ciag_grpauteurs_auteurs
	$res = sql_select("lien.id_auteur AS id",
	 	        "spip_ciag_grpauteurs_auteurs AS lien
		        LEFT JOIN spip_auteurs AS auteurs
		          ON lien.id_auteur=auteurs.id_auteur",
			"auteurs.statut='5poubelle'");

	$t = optimiser_sansref('spip_ciag_grpauteurs_auteurs', 'id_auteur', $res);


	// les liens dans ciag_grpauteurs_gestionnaires
	$res = sql_select("lien.id_auteur AS id",
	 	        "spip_ciag_grpauteurs_gestionnaires AS lien
		        LEFT JOIN spip_auteurs AS auteurs
		          ON lien.id_auteur=auteurs.id_auteur",
			"auteurs.statut='5poubelle'");

	$t = optimiser_sansref('spip_ciag_grpauteurs_gestionnaires', 'id_auteur', $res);

	
	// les liens dans ciag_grpauteurs_rubriques
	$res = sql_select("grp.id_rubrique AS id",
	 	        "spip_ciag_grpauteurs_rubriques AS grp
		        LEFT JOIN spip_rubriques AS rubriques
		          ON grp.id_rubrique=rubriques.id_rubrique",
			"rubriques.id_rubrique IS NULL");

	$t = optimiser_sansref('spip_ciag_grpauteurs_rubriques', 'id_rubrique', $res);
	
	
    return $n;
}	


// Indispensable pour les autorisations sous SPIP 3
function ciag_declarer_tables_objets_surnoms($surnoms) {
	$surnoms['groupeauteur'] = 'ciag_grpauteurs';
	return $surnoms;
}

?>