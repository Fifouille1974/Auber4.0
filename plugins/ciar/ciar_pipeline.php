<?php
/**
 * Plugin Acces restreints Giseh 
 *
 * Le contenu du présent fichier est une adaptation
 * du fichier publi/acces_restreint du plugin "Acces restreint 3"
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');

/**
 * Modifier la clause where pour tenir compte des acces restreints
 *
 * @param array $boucle
 * @return array
 */
function ciar_pre_boucle(&$boucle){
	$securise = false;
	
	switch ($boucle->type_requete){
		case 'hierarchie':
		case 'rubriques':
		case 'articles':
		case 'breves':
		case 'syndication':
			$t = $boucle->id_table . '.id_rubrique';
			$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
			$boucle->where[] = ciar_rubriques_accessibles_where($t);
			$securise = true;
			break;
		case 'forums':
			if (spip_version()>=3) {
				$t = $boucle->id_table . '.id_objet';
				$objet = $boucle->id_table . '.objet';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres

				$where_rubrique = "array('AND','".$objet."=\'rubrique\'',".ciar_rubriques_accessibles_where($t).")";
		
				$where_article = "array('AND','".$objet."=\'article\'',".ciar_articles_accessibles_where($t).")";
		
				$where_breve = "array('AND','".$objet."=\'breve\'',".ciar_breves_accessibles_where($t).")";
				
				$where = "array('OR',$where_rubrique,$where_article)";
				$boucle->where[] = "array('OR',$where,$where_breve)";
				
			} else {
				$t = $boucle->id_table . '.id_rubrique';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
	//			$where = ciar_rubriques_accessibles_where($t);
				$where_rubrique = "array('AND','".$t.">0',".ciar_rubriques_accessibles_where($t).")";
		
				$t = $boucle->id_table . '.id_article';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
	//			$where = "array('OR',$where,".ciar_articles_accessibles_where($t).")";
				$where_article = "array('AND','".$t.">0',".ciar_articles_accessibles_where($t).")";
		
				$t = $boucle->id_table . '.id_breve';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
	//			$boucle->where[] = "array('OR',$where,".ciar_breves_accessibles_where($t).")";
				$where_breve = "array('AND','".$t.">0',".ciar_breves_accessibles_where($t).")";
				
				$where = "array('OR',$where_rubrique,$where_article)";
				$boucle->where[] = "array('OR',$where,$where_breve)";
			}			
			$securise = true;
			break;
		case 'evenements':
			$t = $boucle->id_table . '.id_article';
			$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
			$boucle->where[] = ciar_articles_accessibles_where($t);
			$securise = true;
			break;
		case 'petitions':
			if (spip_version()>=3) {
				$t = $boucle->id_table . '.id_article';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$boucle->where[] = ciar_articles_accessibles_where($t);
			}
			$securise = true;
			break;
		case 'signatures':
			if (spip_version()>=3) {
			} else {	
				$t = $boucle->id_table . '.id_article';
				$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
				$boucle->where[] = ciar_articles_accessibles_where($t);
			}
			$securise = true;
			break;
		case 'syndic_articles':
//			$t = $boucle->id_table . '.' . $boucle->primary;
			$t = $boucle->id_table . '.id_syndic';
			$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
			$boucle->where[] = ciar_syndic_articles_accessibles_where($t);
			$securise = true;
			break;
		case 'documents':
			$t = $boucle->id_table . '.' . $boucle->primary;
			$boucle->select = array_merge($boucle->select, array($t)); // pour postgres
			$boucle->where[] = ciar_documents_accessibles_where($t);
			$securise = true;
			break;
	}

	if ($securise){
		$boucle->hash .= "if (!defined('_DIR_PLUGIN_CIAR')){
		\$link_empty = generer_url_ecrire('admin_vider'); \$link_plugin = generer_url_ecrire('admin_plugin');
		\$message_fr = 'La restriction d\'acc&egrave;s a ete desactiv&eacute;e. <a href=\"'.\$link_plugin.'\">Corriger le probl&egrave;me</a> ou <a href=\"'.\$link_empty.'\">vider le cache</a> pour supprimer les restrictions.';
		\$message_en = 'Acces Restriction is now unusable. <a href=\"'.\$link_plugin.'\">Correct this trouble</a> or <a href=\"'.generer_url_ecrire('admin_vider').'\">empty the cache</a> to finish restriction removal.';
		die(\$message_fr.'<br />'.\$message_en);
		}";
	}
		

	return $boucle;
}


/**
 * Renvoyer le code de la condition where pour la liste des rubriques accessibles
 *
 * @param string $primary
 * @return string
 */
function ciar_rubriques_accessibles_where($primary,$not='NOT', $_publique=''){
	return "sql_in('$primary', ciar_tableau_rubriques_exclues(), '$not')";
}

/**
 * Renvoyer la condition where pour la liste des articles accessibles
 *
 * @param string $primary
 * @return string
 */
function ciar_articles_accessibles_where($primary, $_publique=''){
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	return "array('NOT IN','$primary','('.sql_get_select('zzza.id_article','spip_articles as zzza',".ciar_rubriques_accessibles_where('zzza.id_rubrique','',$_publique).",'','','','',\$connect).')')";
}

/**
 * Renvoyer la condition where pour la liste des breves accessibles
 *
 * @param string $primary
 * @return string
 */
function ciar_breves_accessibles_where($primary, $_publique=''){
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	return "array('NOT IN','$primary','('.sql_get_select('zzzb.id_breve','spip_breves as zzzb',".ciar_rubriques_accessibles_where('zzzb.id_rubrique','',$_publique).",'','','','',\$connect).')')";
}

/**
 * Renvoyer le code de la condition where pour la liste des syndic articles accessibles
 *
 * @param string $primary
 * @return string
 */
function ciar_syndic_articles_accessibles_where($primary, $_publique=''){
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	return "array('NOT IN','$primary','('.sql_get_select('zzzs.id_syndic','spip_syndic as zzzs',".ciar_rubriques_accessibles_where('zzzs.id_rubrique','',$_publique).",'','','','',\$connect).')')";
	#return "array('IN','$primary',array('SUBSELECT','id_syndic','spip_syndic',array(".ciar_rubriques_accessibles_where('id_rubrique').")))";
}


/**
 * Renvoyer le code de la condition where pour la liste des forums accessibles
 * on ne rend visible que les forums qui sont lies a un article, une breve ou une rubrique visible
 *
 * @param string $primary
 * @return string
 */
function ciar_forums_accessibles_where($primary, $_publique=''){
	if (spip_version()>=3) {
		$where_rubrique = "array('AND','zzzf.objet=\'rubrique\'',".ciar_rubriques_accessibles_where('zzzf.id_objet','NOT',$_publique).")";
		$where_article = "array('AND','zzzf.objet=\'article\'',".ciar_articles_accessibles_where('zzzf.id_objet',$_publique).")";
		$where_breve = "array('AND','zzzf.objet=\'breve\'',".ciar_breves_accessibles_where('zzzf.id_objet',$_publique).")";
	} else {
		# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
		$where_rubrique = "array('AND','zzzf.id_rubrique>0',".ciar_rubriques_accessibles_where('zzzf.id_rubrique','NOT',$_publique).")";
		$where_article = "array('AND','zzzf.id_article>0',".ciar_articles_accessibles_where('zzzf.id_article',$_publique).")";
		$where_breve = "array('AND','zzzf.id_breve>0',".ciar_breves_accessibles_where('zzzf.id_breve',$_publique).")";
		
	}

	$where = "array('OR',$where_rubrique,$where_article)";
	if ($GLOBALS['meta']['activer_breves']=='oui')
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
function ciar_documents_accessibles_where($primary, $_publique=''){	
	# hack : on utilise zzz pour eviter que l'optimiseur ne confonde avec un morceau de la requete principale
	$where = "array('AND','zzzd.objet=\'article\'',".ciar_articles_accessibles_where('zzzd.id_objet',$_publique).")";

//	if ($GLOBALS['meta']['documents_rubrique']=='oui') {
		$where = "array('OR',$where,array('AND','zzzd.objet=\'rubrique\'',".ciar_rubriques_accessibles_where('zzzd.id_objet','NOT',$_publique)."))";
//	}

	if ($GLOBALS['meta']['activer_breves']=='oui') {
		$where = "array('OR',$where,array('AND','zzzd.objet=\'breve\'',".ciar_breves_accessibles_where('zzzd.id_objet',$_publique)."))";
	}

	if ($GLOBALS['meta']['formats_documents_forum']) {
		$where = "array('OR',$where,array('AND','zzzd.objet=\'forum\'',".ciar_forums_accessibles_where('zzzd.id_objet',$_publique)."))";
	}

	$where = "array('IN','$primary','('.sql_get_select('zzzd.id_document','spip_documents_liens as zzzd',array($where),'','','','',\$connect).')')";

	return $where;
}

/**
 * Ajoute un bloc dans la page naviguer
 * ajoute un bloc dans la page auteur_infos
 *
 * @param array
 * @return array
 */
function ciar_affiche_milieu($flux) {
  $exec = $flux["args"]["exec"];
 
	if (spip_version()>=3) {
		if ($en_cours = trouver_objet_exec($flux['args']['exec'])
			AND $en_cours['edition']!==true // page visu
			AND $en_cours['type']=='rubrique'
			AND ($id_rubrique = intval($flux['args']['id_rubrique']))){
			if (autoriser('modifier','rubrique',$id_rubrique) OR (autoriser('ecmodifier','rubrique',$id_rubrique))) {
				$ret = "<div id='pave_selection'>";
				$ret .= recuperer_fond('prive/editer/ciar',array_merge($_GET,array('type'=>'rubrique','id'=>$id_rubrique)));
				$ret .= "</div>";
				if ($p=strpos($flux['data'],"<!--affiche_milieu-->"))
					$flux['data'] = substr_replace($flux['data'],$ret,$p,0);
				else
					$flux["data"] .= $ret;
			}
		}
	}
		
	if ($exec == "naviguer") {
		$id_rubrique = $flux["args"]["id_rubrique"];
		if (intval($id_rubrique)>0) {
			if (autoriser('modifier','rubrique',$id_rubrique) OR (autoriser('ecmodifier','rubrique',$id_rubrique))) {
				$ret = "<div id='pave_selection'>";
				$ret .= recuperer_fond('prive/editer/ciar',array_merge($_GET,array('type'=>'rubrique','id'=>$id_rubrique)));
				$ret .= "</div>";
				$flux["data"] .= $ret;
			}
		}
	}
  
  if ($exec == "auteur_infos") {
      $id_auteur = $flux["args"]["id_auteur"];
      $ret = "<div id='pave_selection'>";
      $ret .= recuperer_fond('prive/editer/ciar_cioption',array_merge($_GET,array('type'=>'auteur','id'=>$id_auteur)));
      $ret .= "</div>";
      $flux["data"] .= $ret;
  }
  
  return $flux;
}

function ciar_afficher_complement_objet($flux){
	if (spip_version()>=3) {
		if ($flux['args']['type']=='auteur'
			AND $id_auteur=intval($flux['args']['id'])
			AND (autoriser('modifier','auteur',$id_auteur))) {
		      $ret = "<div id='pave_selection'>";
		      $ret .= recuperer_fond('prive/editer/ciar_cioption',array_merge($_GET,array('type'=>'auteur','id'=>$id_auteur)));
		      $ret .= "</div>";
		      $flux["data"] .= $ret;
		}
	}
	return $flux;
}


/**
 * Ajoute des outils additionnels aux plugin CISF
 *
 * @param array $param
 * @return array
 */
function ciar_cisf_article_options($param){

	// Outils additionnels pour le plugin CISF
	if ($param['args']['type']=='outils_additionnels') {
		$id_article = intval($param['args']['id_article']);
		if($id_article){
			$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=$id_article");
			if (ciar_rub_ec($row['id_rubrique'])) {
				$param['data'][] = '<input type="submit" name="bt_envoimembre" class="bt_envoimembre"  value="'._T('ciar:envoyer_membres').'" onKeyPress="document.pressed=this.name" onClick="document.pressed=this.name">';
			}
		}
	}
	
	return $param;
}


/**
 * Ce pipeline permet de decider d'une page d'erreur plus pertinente
 *
 * @param $contexte
 * @return $contexte
 */
function ciar_page_indisponible($contexte){

	if ($contexte['status']=='404' AND isset($contexte['type'])){
		$objet = $contexte['type'];
		$table_sql = table_objet_sql($objet);
		$id_table_objet = id_table_objet($objet);
		
		if ($id = intval($contexte[$id_table_objet])){
			$publie = false;

			$trouver_table = charger_fonction('trouver_table','base');
			$desc = $trouver_table($table_sql);
			if (isset($desc['field']['statut'])){
				$statut = sql_getfetsel('statut', $table_sql, "$id_table_objet=".intval($id));
				if ($statut=='publie')
					$publie = true;
			}
			
			include_spip('inc/autoriser');
			if ($publie AND !autoriser('voir',$objet,$id)){
				$contexte['status'] = '401';
				$contexte['code'] = '401 Unauthorized';
				$contexte['fond'] = '401';
				$contexte['erreur'] = _T('ciar:info_page_protegee');
				$contexte['cible'] = self();
			}
		}
	}

	return $contexte;
}

/**
 * Optimiser la base de donnee en supprimant les documents orphelins
 *
 * @param int $n
 * @return int
 */
function ciar_optimiser_base_disparus($n){

	// les liens dans ciar_auteurs_acces_rubriques
	$res = sql_select("lien.id_auteur AS id",
	 	        "spip_ciar_auteurs_acces_rubriques AS lien
		        LEFT JOIN spip_auteurs AS auteurs
		          ON lien.id_auteur=auteurs.id_auteur",
			"auteurs.id_auteur IS NULL");

	$t = optimiser_sansref('spip_ciar_auteurs_acces_rubriques', 'id_auteur', $res);	
	
	
	if (!defined('_DIR_PLUGIN_CID')){
		
		// les liens de documents d'articles effaces
		$result = sql_select("lien.id_document AS id, lien.id_objet AS ido",
			    "spip_documents_liens AS lien LEFT JOIN spip_articles AS articles
			          ON lien.id_objet=articles.id_article",
				"lien.objet='article' AND articles.id_article IS NULL");
	
		// on dissocie
		while ($row = sql_fetch($result))
			sql_delete("spip_documents_liens", "id_document=".$row['id']." AND id_objet=".$row['ido']." AND objet='article'");
	
	
	
		// les liens de documents de rubriques effaces
		$result = sql_select("lien.id_document AS id, lien.id_objet AS ido",
			    "spip_documents_liens AS lien LEFT JOIN spip_rubriques AS rubriques
			          ON lien.id_objet=rubriques.id_rubrique",
				"lien.objet='rubrique' AND rubriques.id_rubrique IS NULL");
	
		// on dissocie
		while ($row = sql_fetch($result))
			sql_delete("spip_documents_liens", "id_document=".$row['id']." AND id_objet=".$row['ido']." AND objet='rubrique'");
	
	
	
		// les documents sans liens
		$result = sql_select("doc.id_document AS id",
			    "spip_documents AS doc LEFT JOIN spip_documents_liens AS lien
			          ON doc.id_document=lien.id_document",
				"lien.id_document IS NULL");
	
	    $documents = array();
		while ($row = sql_fetch($result))
			$documents[] = $row['id'];
			
		sql_free($result);
		
		
		// On supprime chaque document sans liens
		foreach($documents AS $id_document)
			ciar_supprimer_document($id_document);
	
	}
    return $n;
}	


function ciar_supprimer_document($id_document) {

	include_spip('inc/documents');
	if (!$doc = sql_fetsel('*', 'spip_documents', 'id_document='.$id_document))
		return false;

	spip_log("Suppression du document $id_document (".$doc['fichier'].")");

	// Si c'est un document ayant une vignette, supprimer aussi la vignette
	if ($doc['id_vignette']) {
		ciar_supprimer_document($doc['id_vignette']);
		sql_delete('spip_documents_liens', 'id_document='.$doc['id_vignette']);
	}

	// Supprimer le fichier si le doc est local,
	// et la copie locale si le doc est distant
	if ($doc['distant'] == 'oui') {
		include_spip('inc/distant');
		if ($local = copie_locale($doc['fichier'],'test'))
			spip_unlink($local);
	}
	else spip_unlink(get_spip_doc($doc['fichier']));

	sql_delete('spip_documents', 'id_document='.$id_document);

	if (defined('_DIR_PLUGIN_CITRACE')){
		$message = '('.$doc['fichier'].')';	
		if ($citrace = charger_fonction('citrace', 'inc'))
			$citrace('document', $id_document, 'supprimer document', $message);
	}	
	
	return true;
}


/**
 * Modifier les en-tetes
 *
 * @param int $n
 * @return int
 */
function ciar_affichage_entetes_final($entetes){

	// si utilisateur authentifie
	if (isset($GLOBALS['visiteur_session']['id_auteur'])){
		// sur le site public
		$espace_prive = defined('_ESPACE_PRIVE') ? _ESPACE_PRIVE : false;
		if (!$espace_prive){
			$entetes["Cache-Control"] = "no-store,no-cache,must-revalidate";
			$entetes["Pragma"] = "no-cache";
			$entetes["Expires"] = "0";
			if (!isset($entetes["Last-Modified"]))
				$entetes["Last-Modified"] = gmdate("D, d M Y H:i:s")." GMT";
	
			if (isset($entetes['X-Spip-Cache']))
				unset($entetes['X-Spip-Cache']);
			if (isset($entetes['X-Spip-Statique']))
				unset($entetes['X-Spip-Statique']);
		}
	}
	
    return $entetes;	
}

?>