<?php
/**
 * Plugin cilien
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
function cilien_affiche_gauche($flux) {
	$exec = $flux["args"]["exec"];

	if (spip_version()>=3) {
		include_spip('inc/presentation');
		include_spip('inc/filtres_boites');
				
		if ($en_cours = trouver_objet_exec($flux['args']['exec']) AND $en_cours['type']=='rubriques'){
	      	$id_rubrique = intval($flux["args"]["id_rubrique"]);
	      	if ($id_rubrique)
				$url = generer_url_ecrire("cilienmesliens","id_rubrique=".$id_rubrique);
	      	else
		      	$url = generer_url_ecrire("cilienmesliens");

			$ret = boite_ouvrir('', 'raccourcis');
			$ret .= icone_horizontale(_T('cilien:cilien_titre'), $url, "images/cilien-16.png", "", false);
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
				$url = generer_url_ecrire("cilienmesliens","id_rubrique=".$id_rubrique);
	      	else
		      	$url = generer_url_ecrire("cilienmesliens");

			$ret = icone_horizontale(_T('cilien:cilien_titre'), $url, chemin("images/cilien-16.png"), "",false);
			$flux["data"] .= bloc_des_raccourcis($ret);
		}
	}

	return $flux;
}

?>