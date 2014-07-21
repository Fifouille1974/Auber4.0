<?php
/**
 * Plugin ciarchive : Archivage d'articles
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');


/**
 * Ajoute un bloc dans la page naviguer
 *
 * @param array
 * @return array
 */
function ciarchive_affiche_gauche($flux) {
	$exec = $flux["args"]["exec"];

	if (spip_version()>=3) {
		include_spip('inc/presentation');
		include_spip('inc/filtres_boites');
				
		if ($en_cours = trouver_objet_exec($flux['args']['exec']) AND $en_cours['type']=='rubriques'){
	      	$id_rubrique = intval($flux["args"]["id_rubrique"]);
	      	if ($id_rubrique)
				$url = generer_url_ecrire("ciarchive_articles","id_rubrique=".$id_rubrique);
	      	else
		      	$url = generer_url_ecrire("ciarchive_articles");

			$ret = boite_ouvrir('', 'raccourcis');
			$ret .= icone_horizontale(_T('ciarchive:archives'), $url, "images/ciarchive.png", "", false);
			$ret .= boite_fermer();
			if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
				$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
			else
				$flux["data"] .= $ret;
		}
		
	} else {
	
		if ($exec=="naviguer") {
	      	$id_rubrique = intval($flux["args"]["id_rubrique"]);
	      	if ($id_rubrique)
				$url = generer_url_ecrire("ciarchive_articles","id_rubrique=".$id_rubrique);
	      	else
		      	$url = generer_url_ecrire("ciarchive_articles");

			$ret = icone_horizontale(_T('ciarchive:archives'), $url, chemin("images/ciarchive.png"), "",false);
			$flux["data"] .= bloc_des_raccourcis($ret);
		}
	}

	return $flux;
}


function ciarchive_declarer_tables_objets_sql($tables){
	$tables['spip_articles']['statut_titres']['archive'] = 'ciarchive:texte_statut_archive';
	$tables['spip_articles']['statut_textes_instituer']['archive'] = 'ciarchive:texte_statut_archive';
	$tables['spip_articles']['statut_images'] = array(
					'prepa'=>'puce-preparer-8.png',
					'prop'=>'puce-proposer-8.png',
					'publie'=>'puce-publier-8.png',
					'refuse'=>'puce-refuser-8.png',
					'poubelle'=>'puce-supprimer-8.png',
					'poub'=>'puce-supprimer-8.png',
					'archive'=>'puce-archiver-8.png'
				);

	
	return $tables;
}

?>