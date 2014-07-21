<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

function ciparam_affiche_milieu($flux) {
	$exec = $flux["args"]["exec"];

	if (spip_version()>=3) {
		if ($en_cours = trouver_objet_exec($flux['args']['exec'])
			AND $en_cours['edition']!==true // page visu
			AND $en_cours['type']=='rubrique'
			AND ($id_rubrique = intval($flux['args']['id_rubrique']))){
			if (autoriser('modifier','rubrique',$id_rubrique)) {
				$ret = "<div id='pave_selection'>";
				$ret .= recuperer_fond('prive/editer/ciparam_choix_rubrique',array_merge($_GET,array('type'=>'rubrique','id'=>$id_rubrique)));
				$ret .= "</div>";
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
			}
		}
		
		if ($en_cours = trouver_objet_exec($flux['args']['exec'])
			AND $en_cours['edition']!==true // page visu
			AND $en_cours['type']=='article'
			AND ($id_article = intval($flux['args']['id_article']))){
			if (autoriser('modifier','article',$id_article)) {
				$ret = "<div id='pave_selection'>";
				$ret .= recuperer_fond('prive/editer/ciparam_choix_article',array_merge($_GET,array('type'=>'article','id'=>$id_article)));
				$ret .= "</div>";
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
			}
		}

		if ($en_cours = trouver_objet_exec($flux['args']['exec'])
			AND $en_cours['edition']!==true // page visu
			AND $en_cours['type']=='site'
			AND ($id_site = intval($flux['args']['id_syndic']))){
			if (autoriser('modifier','site',$id_site)) {
				$ret = "<div id='pave_selection'>";
				$ret .= recuperer_fond('prive/editer/ciparam_choix_syndic',array_merge($_GET,array('type'=>'site','id'=>$id_site)));
				$ret .= "</div>";
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
			}
		}
		
	} else {
	
		if ($exec == "naviguer") {
			$id_rubrique = $flux["args"]["id_rubrique"];
			$ret = "<div id='pave_selection'>";
			$ret .= recuperer_fond('prive/editer/ciparam_choix_rubrique',array_merge($_GET,array('type'=>'rubrique','id'=>$id_rubrique)));
			$ret .= "</div>";
			$flux["data"] .= $ret;
		}
	
		if ($exec == "articles") {
			$id_article = $flux["args"]["id_article"];
			$ret = "<div id='pave_selection'>";
			$ret .= recuperer_fond('prive/editer/ciparam_choix_article',array_merge($_GET,array('type'=>'article','id'=>$id_article)));
			$ret .= "</div>";
			$flux["data"] .= $ret;
		}
	
		if ($exec == "sites") {
			$id_syndic = $flux["args"]["id_syndic"];
			$ret = "<div id='pave_selection'>";
			$ret .= recuperer_fond('prive/editer/ciparam_choix_syndic',array_merge($_GET,array('type'=>'syndic','id'=>$id_syndic)));
			$ret .= "</div>";
			$flux["data"] .= $ret;
		}
	}

	return $flux;
}

?>