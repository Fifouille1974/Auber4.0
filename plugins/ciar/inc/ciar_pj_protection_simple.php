<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/filtres');
 
/**
 * Protection simple
 * Ajouter une cle a la fin du nom des pieces jointes et images
 *
 * @param array $tableau
 * @return array
 */
function ciar_post_edition($tableau) {

	// Par defaut on met la cle
	$cle_pj = 'oui';
	
	// Choix avec ou sans cle
	if (isset($GLOBALS['meta']['ciar'])) {
		$ciar_config = @unserialize($GLOBALS['meta']['ciar']);
		if (isset($ciar_config['cle_pj']))
			$cle_pj = $ciar_config['cle_pj'];
	}

	// Choix impose dans un fichier d'option
	if (defined('_CIAR_DOC_SANS_CLE'))
		$cle_pj = 'non';
	

	if ($cle_pj=='oui') {
		if ($tableau["args"]["operation"] == "ajouter_document") {
			$id_document = $tableau["args"]["id_objet"];
			if ($id_document) {
				$doc = sql_fetsel("titre, fichier, mode, distant", "spip_documents","id_document=$id_document");
				if ($doc['distant']!='oui') {
					$chemin = $doc['fichier'];
					$titre = $doc['titre'];
					$mode = $doc['mode'];

					$chemin_avec_cle = $chemin;
					$titre_sans_cle = "";

					$citableau = ciar_chemin_avec_cle($chemin);
					if ($citableau) {
						if ($citableau['chemin'])
							$chemin_avec_cle = $citableau['chemin'];
						if ($citableau['titre'])
							$titre_sans_cle = $citableau['titre'];
					}

					// si plugin ciparam est actif, ajouter le cas echeant un titre mais sans cle
					if (defined('_DIR_PLUGIN_CIPARAM')) {
						// tenir compte du cas de remplacement d'un document (plugin cisf)
						if ((!$titre OR $titre==ciar_titrefichier($chemin)) AND $mode == 'document')
							sql_updateq("spip_documents", array("titre" => $titre_sans_cle), "id_document=$id_document");
					}

					if ($chemin_avec_cle!=$chemin) {
						sql_updateq("spip_documents", array("fichier" => $chemin_avec_cle), "id_document=$id_document");
						@rename(_DIR_IMG.$chemin,_DIR_IMG.$chemin_avec_cle);
					}
				}
			}
		}
	}
	
	// dans le meme pipeline, sur un sujet tres different
	// empecher d'enlever de spip_auteurs_rubriques les rubriques EC de l'auteur (denormalisation)
	// mais ne rien faire si l'auteur est un administrateur du site qui doit le rester
    if ($tableau['args']['table']=='spip_auteurs') {
    	$id_auteur = intval($tableau['args']['id_objet']);
	    if ($id_auteur>0) {
   			$row = sql_fetsel("statut", "spip_auteurs","id_auteur=$id_auteur","","");
   			if ($row)
	   			$statut = $row['statut'];

	   		// deux requetes, par mesure de securite, en cas d'absence de la colonne cioption dans la table	
	   		$cioption = '';
   			$row = sql_fetsel("cioption", "spip_auteurs","id_auteur=$id_auteur","","");
   			if ($row) {
	   			if ($row['cioption'])
					$cioption = $row['cioption'];
   			}

			// surtout ne rien faire si l'auteur est 'ecadminsite'
			if ($cioption!='ecadminsite') {
		    	$rubriques = array();
		    	$insertrubriques = array();
				if (spip_version()>=3)
					$result = sql_select("id_objet as id_rubrique", "spip_auteurs_liens", "objet='rubrique' AND id_auteur=$id_auteur","","");
				else
					$result = sql_select("id_rubrique", "spip_auteurs_rubriques", "id_auteur=$id_auteur","","");
				while ($row = sql_fetch($result))
					$rubriques[] = $row['id_rubrique'];

				if (!$cioption AND $statut=='0minirezo' AND !$rubriques) {
					// si un admin du site n'a pas encore de cioption, ne rien faire
				} elseif ($statut=='6forum') {
					// si visiteur, ne rien faire
				} elseif ($statut=='1comite' AND !defined('_DIR_PLUGIN_CIRR')) {
					// si redacteur et plugin CIRR non actif, ne rien faire
				} else {
					$result = sql_select("id_rubrique", "spip_ciar_auteurs_acces_rubriques", "id_auteur=$id_auteur","","");
					while ($row = sql_fetch($result)) {
						if (!in_array($row['id_rubrique'],$rubriques)) {
							$insertrubriques[] = $row['id_rubrique'];
						}
					}
		
					foreach ($insertrubriques as $id_rubrique) {
						if (spip_version()>=3)
							sql_insertq('spip_auteurs_liens', array('id_auteur' => $id_auteur, 'objet' => 'rubrique', 'id_objet' => $id_rubrique));
						else					
							sql_insertq('spip_auteurs_rubriques', array('id_auteur' => $id_auteur, 'id_rubrique' => $id_rubrique));
					}
				}
			}
	    }
    }

	return $tableau;
}


/**
 * Nom du fichier sans chemin et sans extension
 *
 * @param string $fichier
 * @return string $titre
 */
function ciar_titrefichier($fichier) {
	// enlever l'extension et le chemin
	$titre=$fichier;
	$pos1 = strrpos($titre,".");
	if (!($pos1 === false)) $titre=substr($titre,0,$pos1);

	$pos3 = strrpos($titre,"/");
	if (!($pos3 === false)) $titre=substr($titre,$pos3+1);
	
	return $titre;
}


/**
 * Nom du fichier avec son chemin et la cle
 *
 * @param string $chemin
 * @return array
 */
function ciar_chemin_avec_cle($chemin) {

	// important : chercher le dernier slash
	$lastpos = strrpos($chemin,'/');
	$dir = substr($chemin,0,$lastpos+1);
	$fichier = substr($chemin,$lastpos+1);
	$info_fichier = explode(".",$fichier);	
	$nom = $info_fichier[0];
	$ext = $info_fichier[1];

	// si le document a deja une cle, l'enlever pour eviter qu'il en ait plusieurs
	$cisearch = $nom;
	$cioffset =0;
	while ($poscle = strpos($cisearch,"_cle")) {
		$cisearch = substr($cisearch,$poscle+4);
		if ($cioffset>0) $cioffset+=4;
		$cioffset += $poscle;
		$cititre=substr($nom,0,$cioffset);
		$cicle=substr($nom,$cioffset,10);
		if ($cicle==ciar_creer_cle($cititre)) {
			$nom = $cititre;
			break;	
		}
	}
	
	// affecter la cle
	$dest = $nom.ciar_creer_cle($nom);
	$chemin_avec_cle = _DIR_IMG . $dir . $dest .'.'.$ext;
	
	// si chemin inchange, ne rien faire
	if ($chemin_avec_cle==_DIR_IMG.$chemin) {
		$chemin_avec_cle = $chemin;
	} else {
		// si ce $chemin_avec_cle est deja utilise, tourner jusqu'a trouver un numero correct
		if (@file_exists($chemin_avec_cle)) {
			$n = 0;
			while (true) {
				$n++;
				$chemin_avec_cle = _DIR_IMG . $dir . $dest .'-'.$n.'.'.$ext;
				// si chemin inchange, ne rien faire
				if ($chemin_avec_cle==_DIR_IMG.$chemin)
					break;
				if (!@file_exists($chemin_avec_cle))
					break;
			}
		}
	}

	// donne le chemin du fichier relatif a _DIR_IMG
	// pour stockage 'tel quel' dans la base de donnees
	if (strpos($chemin_avec_cle, _DIR_IMG) === 0)
		$chemin_avec_cle= substr($chemin_avec_cle, strlen(_DIR_IMG));


	return array("titre" => $nom, "chemin" => $chemin_avec_cle);
}


/**
 * Calcul de la cle
 *
 * @param string $cititre
 * @return string
 */
function ciar_creer_cle($cititre) {

	$listevar="10,3,1,2";
	if (defined('_CIAR_CLE')) $listevar=_CIAR_CLE;
	$ciarray = explode(",",$listevar);

	$nbre = (strlen($cititre)+ $ciarray[0])*(strlen($cititre)+ $ciarray[1]);
	$texte = substr(strval($nbre),$ciarray[2],1).$cititre.substr(strval($nbre),$ciarray[3],1);
	$trans = substr(md5($texte),1,5);
	$res = str_replace(".","a",$trans);
	$res = str_replace("0","1",$res);
	$s = substr(strval($nbre),-1,1).$res;

	return '_cle'.$s;
}


/**
 * Cree un alea
 *
 * @return int
 */
function ciar_creer_alea50() {
	static $seeded;

	if (!$seeded) {
		$seed = (double) (microtime() + 1) * time();
		mt_srand($seed);
		$seeded = true;
	}

	$alea = mt_rand(10,50);

	return $alea;
}


/**
 * Protection simple
 * Avant l’enregistrement d’un article ou d'une rubrique, verifier que les raccourcis <doc…>, etc
 * qu’il contient ne correspondent pas a une piece jointe
 * a laquelle on n'a pas le droit d'acceder
 *
 * @param array $flux
 * @return array
 */
function ciar_pre_edition($flux){
	// contourner le cas d'un double appel du pipeline sur la meme table
	static $tables = array();
	if (isset($flux['args']['table'])){
		if ($tables AND  in_array($flux['args']['table'],$tables))
			return $flux;
		else
			$tables[] = $flux['args']['table'];
	}

	$champs = array();	
    if ($flux['args']['table']=='spip_articles')
		$champs = array('surtitre', 'titre', 'soustitre', 'descriptif', 'nom_site', 'url_site', 'chapo', 'texte', 'ps');
    elseif ($flux['args']['table']=='spip_rubriques')
		$champs = array('titre', 'texte', 'descriptif');
		
    if ($champs) {
		include_spip('ciar_fonctions');    	
		foreach ($champs as $champ) {
			if ($flux['data'][$champ])
	            $flux['data'][$champ] = ciar_anti_scan_pj($flux['data'][$champ]);
		}
    }

    return $flux;
}


/**
 * Anti scan de document via <doc...>, etc
 *
 * @param string $texte
 * @return string
 */
function ciar_anti_scan_pj($texte){
	if (preg_match_all(',<([a-z]+)([0-9]+)(\|([^>]*))?'.'\s*/?'.'>,i', $texte, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			$raccourci = $match[0];
			$id_document = $match[2];
			if (!ciar_autoriser_document($id_document))
				$texte = str_replace($raccourci,'',$texte);
		}
	}
	
	return $texte;
}


?>