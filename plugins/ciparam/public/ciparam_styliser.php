<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
function ciparam_styliser($flux){

	// securite anti-hack pour éviter des appels tels que page=article-commentaire&id_article=8
    if ($page = $_GET['page'] AND $fond = $flux['args']['fond']) {
		if ($page == $fond) {
	    	$hacks = array("article-" => "article",
	    		"rubrique-" => "rubrique",
	    		"haut-" => "haut",
	    		"pied-" => "pied");
			foreach ($hacks as $key=>$val) { 
				if (strpos($page, $key) !== false) {
					$flux['args']['fond'] = str_replace($page,$val,$flux['args']['fond']);
					$flux['data'] = str_replace($page,$val,$flux['data']);
				}
			}
		}
    }

    $cifond = "";
	$fond = $flux['args']['fond'];

	if ($fond) {
		// enlever le chemin au cas ou "contenu/article"
		$pos = strrpos($fond,"/");
		if (!($pos === false))
			$cifond = substr($fond,$pos+1);
		else
			$cifond = $fond;
	}
	
    // si article ou rubrique ou type de colonne droite (extra) et gauche (navigation)
    if (($cifond) AND in_array($cifond, array('article','rubrique'))) {
		$flux['data'] = ciparam_choix_squelette($flux['data'], $fond, $cifond, $flux['args']['id_rubrique'], $flux['args']['ext']);
    } else {
		$flux['data'] = ciparam_choix_squelette($flux['data'], $fond, $cifond, '', $flux['args']['ext']);
    }

    return $flux;
}


function ciparam_choix_squelette($squelette, $fond, $cifond, $id_objet, $ext='html'){

	include_spip('inc/ciparam_inc_meta');

	$return ="";
	$ciforme = "";
	$ciforme_parent = "";
	$ci_ajout_forme = "";
	$ci_ajout_meta = "";
	$ci_id_parent = 0;
	$ci_id_article = 0;
	
	$ci_dossier_mobile = '';
	if (isset($GLOBALS['cimobile_dossier_squelettes'])) {
		if ($GLOBALS['cimobile_dossier_squelettes']) {
			if ($GLOBALS['cimobile_impacter_ciparam']=='oui') {
				$ci_dossier_mobile = $GLOBALS['cimobile_dossier_squelettes']."/";
			}
		}
	}

	// application du choix de la forme de rubrique ou d'article
	// application de l'ordre de tri des rubriques
	if ($id_objet) {
		$id_objet = intval($id_objet);
		if ($id_objet>0) {
			if (in_array($fond,array("article","contenu/article","extra/article","navigation/article"))) {
				if (isset($GLOBALS['contexte']['id_article'])) {
					$ci_id_article = intval($GLOBALS['contexte']['id_article']);

					$result = sql_select("ciforme", "spip_articles", "id_article=$ci_id_article");
					while ($row = sql_fetch($result)) {
						$ciforme = $row['ciforme'];
					}
					if (!$ciforme){
						// rechercher la forme de la rubrique de l'article
						$ci_id_parent = quete_rubrique($ci_id_article,'');
						$result = sql_select("ciforme", "spip_rubriques", "id_rubrique=$ci_id_parent");
						while ($row = sql_fetch($result)) {
							$ciforme_parent = $row['ciforme'];
						}
						if ($ciforme_parent){
							// Existe-t-il une forme équivalente pour l'article (héritage)
							if (in_array($fond,array("extra/article","navigation/article")))
								$fond_path = str_replace("/","/forme/",$fond);
							else
								$fond_path = $fond;
						
							if (find_in_path($ci_dossier_mobile.$fond_path."-".ciparam_compatibilite_forme($ciforme_parent).".".$ext)) {
								$ciforme = $ciforme_parent;
							}
						}					
					}
				}
			}
			if (in_array($fond,array("rubrique","contenu/rubrique","extra/rubrique","navigation/rubrique"))) {
				$result = sql_select("ciforme", "spip_rubriques", "id_rubrique=$id_objet");
				while ($row = sql_fetch($result)) {
					$ciforme = $row['ciforme'];
				}
			}
		}
	}
	
	if ($ciforme)
		$ci_ajout_forme = "-".ciparam_compatibilite_forme($ciforme);
		
		
	// application du choix du type de colonne de droite et de gauche
	// application du choix de l'espacement du menu, etc.
	$formes_meta = array("extra/article","navigation/article","extra/rubrique","navigation/rubrique","noisettes/haut","noisettes/pied");
	foreach ($formes_meta as $forme_meta) { 
		if ($fond==$forme_meta) {
			$forme_meta = str_replace("noisettes/","",$forme_meta);	
			if($ci_meta = ciparam_lire_meta('ci_'.str_replace("/","_",$forme_meta))) {
				$ci_ajout_meta = "-".$ci_meta;
			}
			break;
		}
	}

	// par defaut, on prend le gabarit standard
	$return = $squelette;

	
	// si une option mobile est active et qu'un gabarit mobile existe on le prend
	if ($ci_dossier_mobile) {
		$find = find_in_path($ci_dossier_mobile.$fond.".".$ext);
		if ($find)
			$return = substr($find,0,-(strlen($ext)+1));
	}

	
	// si le fichier forme existe, on le prend
	if ($ci_ajout_forme) {
		// Pour ne pas confondre les formes et les types de colonnes dans "extra" et "navigation"
		// les formes sont stockees dans le sous dossier "forme"
		if (in_array($fond,array("extra/article","navigation/article","extra/rubrique","navigation/rubrique")))
			$fond_path = str_replace("/","/forme/",$fond);
		else
			$fond_path = $fond;

		$find_forme = find_in_path($ci_dossier_mobile.$fond_path.$ci_ajout_forme.".".$ext);
		if ($find_forme) {
			// enlever l'extension
			$return = substr($find_forme,0,-(strlen($ext)+1));
		}
	}


	// si le fichier forme n'existe pas et que le fichier meta existe, on prend le fichier meta
	if ($ci_ajout_meta AND (!$ci_ajout_forme OR !$find_forme)) {
		$find_meta = find_in_path($ci_dossier_mobile.$fond.$ci_ajout_meta.".".$ext);
		if ($find_meta) {
			// enlever l'extension
			$return = substr($find_meta,0,-(strlen($ext)+1));
		}
	}

	
	return $return;
}


// compatibilité ascendante
function ciparam_compatibilite_forme($forme) {
	if (substr($forme,0,1)=="_")
		$forme = substr($forme,1);

	return $forme;
}

?>