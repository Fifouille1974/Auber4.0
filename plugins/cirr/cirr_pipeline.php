<?php
/**
 * Plugin redacteur restreint
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Modifier la clause where
 *
 * @param array $boucle
 * @return array
 */
function cirr_pre_boucle(&$boucle){
	if (spip_version()>=3 AND test_espace_prive()) {
		$securise = false;
		
		switch ($boucle->type_requete){
			case 'hierarchie':
			case 'rubriques':
			case 'articles':
			case 'breves':
			case 'syndication':
				$t = $boucle->id_table . '.id_rubrique';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$boucle->where[] = "array('OR',".cirr_where_auteur_non_restreint().",".cirr_rubriques_accessibles_where($t).")";
				$securise = true;
				break;
			case 'forums':
				$t = $boucle->id_table . '.id_objet';
				$objet = $boucle->id_table . '.objet';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$where_rubrique = "array('AND','".$objet."=\'rubrique\'',".cirr_rubriques_accessibles_where($t).")";
				$where_article = "array('AND','".$objet."=\'article\'',".cirr_articles_accessibles_where($t).")";
				$where_breve = "array('AND','".$objet."=\'breve\'',".cirr_breves_accessibles_where($t).")";
				$where = "array('OR',$where_rubrique,$where_article)";
				$where = "array('OR',$where,$where_breve)";
				$boucle->where[] = "array('OR',".cirr_where_auteur_non_restreint().",$where)";
				$securise = true;
				break;
			case 'versions':
				$t = $boucle->id_table . '.id_objet';
				$objet = $boucle->id_table . '.objet';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$where_rubrique = "array('AND','".$objet."=\'rubrique\'',".cirr_rubriques_accessibles_where($t).")";
				$where_article = "array('AND','".$objet."=\'article\'',".cirr_articles_accessibles_where($t).")";
				$where_breve = "array('AND','".$objet."=\'breve\'',".cirr_breves_accessibles_where($t).")";
				$where = "array('OR',$where_rubrique,$where_article)";
				$where = "array('OR',$where,$where_breve)";
				$boucle->where[] = "array('OR',".cirr_where_auteur_non_restreint().",$where)";
				$securise = true;
				break;
			case 'evenements':
				$t = $boucle->id_table . '.id_article';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$boucle->where[] = "array('OR',".cirr_where_auteur_non_restreint().",".cirr_articles_accessibles_where($t).")";
				$securise = true;
				break;
			case 'petitions':
				$t = $boucle->id_table . '.id_article';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$boucle->where[] = "array('OR',".cirr_where_auteur_non_restreint().",".cirr_articles_accessibles_where($t).")";
				$securise = true;
				break;
			case 'signatures':
				$securise = true;
				break;
			case 'syndic_articles':
//				$t = $boucle->id_table . '.' . $boucle->primary;
				$t = $boucle->id_table . '.id_syndic';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$boucle->where[] = "array('OR',".cirr_where_auteur_non_restreint().",".cirr_syndic_articles_accessibles_where($t).")";
				$securise = true;
				break;
			case 'documents':
				$t = $boucle->id_table . '.' . $boucle->primary;
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$boucle->where[] = "array('OR',".cirr_where_auteur_non_restreint().",".cirr_documents_accessibles_where($t).")";
				$securise = true;
				break;
		}

		if ($securise){
			$boucle->hash .= "if (!defined('_DIR_PLUGIN_CIRR')){
			\$link_empty = generer_url_ecrire('admin_vider'); \$link_plugin = generer_url_ecrire('admin_plugin');
			\$message_fr = 'Le plugin CIRR a ete desactiv&eacute;. Vider le cache.';
			\$message_en = 'Plugin CIRR is now unusable. Empty the cache.';
			die(\$message_fr.'<br />'.\$message_en);
			}";
		}
			
	}
	return $boucle;
}


/**
 * Auteur non restreint
 */
function cirr_where_auteur_non_restreint($_publique=''){
	return "cirr_auteur_non_restreint()";
}

 
/**
 * Renvoyer le code de la condition where pour la liste des rubriques accessibles
 *
 * @param string $primary
 * @return string
 */
function cirr_rubriques_accessibles_where($primary,$not='', $_publique=''){
	return "sql_in('$primary', cirr_tableau_rubriques_auteur(), '$not')";
}

/**
 * Renvoyer la condition where pour la liste des articles accessibles
 *
 * @param string $primary
 * @return string
 */
function cirr_articles_accessibles_where($primary, $_publique=''){
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	return "array('IN','$primary','('.sql_get_select('zzza.id_article','spip_articles as zzza',".cirr_rubriques_accessibles_where('zzza.id_rubrique','',$_publique).",'','','','',\$connect).')')";
}

/**
 * Renvoyer la condition where pour la liste des breves accessibles
 *
 * @param string $primary
 * @return string
 */
function cirr_breves_accessibles_where($primary, $_publique=''){
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	return "array('IN','$primary','('.sql_get_select('zzzb.id_breve','spip_breves as zzzb',".cirr_rubriques_accessibles_where('zzzb.id_rubrique','',$_publique).",'','','','',\$connect).')')";
}

/**
 * Renvoyer le code de la condition where pour la liste des syndic articles accessibles
 *
 * @param string $primary
 * @return string
 */
function cirr_syndic_articles_accessibles_where($primary, $_publique=''){
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	return "array('IN','$primary','('.sql_get_select('zzzs.id_syndic','spip_syndic as zzzs',".cirr_rubriques_accessibles_where('zzzs.id_rubrique','',$_publique).",'','','','',\$connect).')')";
	#return "array('IN','$primary',array('SUBSELECT','id_syndic','spip_syndic',array(".cirr_rubriques_accessibles_where('id_rubrique').")))";
}


/**
 * Renvoyer le code de la condition where pour la liste des forums accessibles
 * on ne rend visible que les forums qui sont lies a un article, une breve ou une rubrique visible
 *
 * @param string $primary
 * @return string
 */
function cirr_forums_accessibles_where($primary, $_publique=''){
	if (spip_version()>=3) {
		$where_rubrique = "array('AND','zzzf.objet=\'rubrique\'',".cirr_rubriques_accessibles_where('zzzf.id_objet','NOT',$_publique).")";
		$where_article = "array('AND','zzzf.objet=\'article\'',".cirr_articles_accessibles_where('zzzf.id_objet',$_publique).")";
		$where_breve = "array('AND','zzzf.objet=\'breve\'',".cirr_breves_accessibles_where('zzzf.id_objet',$_publique).")";
	} else {
		# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
		$where_rubrique = "array('AND','zzzf.id_rubrique>0',".cirr_rubriques_accessibles_where('zzzf.id_rubrique','NOT',$_publique).")";
		$where_article = "array('AND','zzzf.id_article>0',".cirr_articles_accessibles_where('zzzf.id_article',$_publique).")";
		$where_breve = "array('AND','zzzf.id_breve>0',".cirr_breves_accessibles_where('zzzf.id_breve',$_publique).")";
		
	}

	$where = "array('OR',$where_rubrique,$where_article)";
	$where = "array('OR',$where,$where_breve)";

	return "array('IN','$primary','('.sql_get_select('zzzf.id_forum','spip_forum as zzzf',array($where),'','','','',\$connect).')')";

}


/**
 * Renvoyer le code de la condition where pour la liste des documents accessibles
 * on ne rend visible que les docs qui sont lies a un article, une breve ou une rubrique visible
 *
 * @param string $primary
 * @return string
 */
function cirr_documents_accessibles_where($primary, $_publique=''){	
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	$where = "array('AND','zzzd.objet=\'article\'',".cirr_articles_accessibles_where('zzzd.id_objet',$_publique).")";

	if ($GLOBALS['meta']['documents_rubrique']=='oui') {
		$where = "array('OR',$where,array('AND','zzzd.objet=\'rubrique\'',".cirr_rubriques_accessibles_where('zzzd.id_objet','',$_publique)."))";
	}
	
	if ($GLOBALS['meta']['activer_breves']=='oui') {
		$where = "array('OR',$where,array('AND','zzzd.objet=\'breve\'',".cirr_breves_accessibles_where('zzzd.id_objet',$_publique)."))";
	}
/*
	if ($GLOBALS['meta']['formats_documents_forum']) {
		$where = "array('OR',$where,array('AND','zzzd.objet=\'forum\'',".cirr_forums_accessibles_where('zzzd.id_objet',$_publique)."))";
	}
*/
	$where = "array('IN','$primary','('.sql_get_select('zzzd.id_document','spip_documents_liens as zzzd',array($where),'','','','',\$connect).')')";

	return $where;
}

?>