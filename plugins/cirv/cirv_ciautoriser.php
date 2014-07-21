<?php
/**
 * Plugin redacteur valideur
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/filtres');
 
function cirv_ciautoriser($param) {
	$faire = $param['faire'];
	$type = $param['type'];
	$id = $param['id'];
	$qui = $param['qui'];
	$opt = $param['opt'];
	$cifonction = $type.'_'.$faire;
	
	
	// Autoriser a publier dans la rubrique
	if ($cifonction=='rubrique_publierdans') {
		
		// avec l'operateur 'OR' mettre false par defaut
		$autoriser = false;
		$id_article = 0;
	
		// Pages 'articles','articles_edit' dans l'espace prive
		if (in_array(_request('exec'),array('articles','articles_edit')) AND intval(_request('id_article'))>0)
			$id_article = _request('id_article');
		// Action instituer article
		elseif (_request('action')=='instituer_article' AND intval(_request('arg'))>0)
			$id_article = _request('arg');
		// Compatibilite avec le plugin cisf
		elseif (in_array(_request('page'),array('cisf_article','cisf_rubart'))) {
			if (intval(_request('id_article'))>0)
				$id_article = _request('id_article');
			elseif (intval(_request('arg'))>0)
				$id_article = _request('arg');
		}

		if (spip_version()>=3) {
			if (in_array(_request('exec'),array('article')) AND intval(_request('id_article'))>0)
				$id_article = _request('id_article');
		}

		if ($id_article>0) {
			// Cas du redacteur valideur
			$id_auteur = isset($qui['id_auteur']) ? $qui['id_auteur'] : $GLOBALS['visiteur_session']['id_auteur'];
			$cistatut = cirv_cistatut_auteur_rubrique($id, $qui['cistatut']);
	
			if ($cistatut=='ciredval' OR ($cistatut=='ciredvaltout' AND !defined('_CIRV_PAS_CIREDVALTOUT'))) {
				if (!$qui['restreint'] OR !$id OR in_array($id, $qui['restreint'])) {					
					// donner le droit de publier l'article au redacteur valideur s'il est auteur de l'article
					if (spip_version()>=3)
						$row = sql_fetsel("id_auteur", "spip_auteurs_liens", "objet='article' AND id_objet=".intval($id_article)." AND id_auteur=".intval($id_auteur));
					else
						$row = sql_fetsel("id_auteur", "spip_auteurs_articles", "id_article=".intval($id_article)." AND id_auteur=".intval($id_auteur));
					if ($row) {
						$autoriser = true;
						// utilisation l'operateur 'OR' pour elargir ce droit 
						$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
					}
				}
			}
			
			// Cas du redacteur qui publie tous les articles (les siens et ceux des rédacteurs qui ne peuvent publier) contenus dans ses rubriques
			if ($cistatut=='ciredvaltout' AND !defined('_CIRV_PAS_CIREDVALTOUT')) {
				if (!$qui['restreint'] OR !$id OR in_array($id, $qui['restreint'])) {
					// si les autres auteurs de l'article ne peuvent pas le publier	
					if (!cirv_un_autre_auteur_peut_publier($id_article, $id_auteur)) {
						$autoriser = true;
						// utilisation l'operateur 'OR' pour elargir ce droit 
						$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
					}					
				}
			}

		} elseif (_request('page')=='cisf_article') {
			// Compatibilite avec le plugin cisf en creation d'article
			// Cas du redacteur valideur
			$id_auteur = isset($qui['id_auteur']) ? $qui['id_auteur'] : $GLOBALS['visiteur_session']['id_auteur'];
			$cistatut = cirv_cistatut_auteur_rubrique($id, $qui['cistatut']);
	
			if ($cistatut=='ciredval' OR ($cistatut=='ciredvaltout' AND !defined('_CIRV_PAS_CIREDVALTOUT'))) {
				if (!$qui['restreint'] OR !$id OR in_array($id, $qui['restreint'])) {					
					// donner le droit de publier l'article au redacteur valideur s'il cree l'article
					$autoriser = true;
					// utilisation l'operateur 'OR' pour elargir ce droit 
					$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
				}
			}
		}


	// Autoriser a modifier l'article
	} elseif ($cifonction=='article_modifier') {
		// avec l'operateur 'OR' mettre false par defaut
		$autoriser = false;
		$id_rubrique = cirv_quete_rubrique($id);
	
		$id_auteur = isset($qui['id_auteur']) ? $qui['id_auteur'] : $GLOBALS['visiteur_session']['id_auteur'];	
		$cistatut = cirv_cistatut_auteur_rubrique($id_rubrique, $qui['cistatut']);
		
		// Cas du redacteur valideur
		if ($cistatut=='ciredval' OR ($cistatut=='ciredvaltout' AND !defined('_CIRV_PAS_CIREDVALTOUT'))) {
			// le redacteur valideur doit pouvoir modifier son article meme publie
			if (spip_version()>=3)
				$row = sql_fetsel("id_auteur", "spip_auteurs_liens", "objet='article' AND id_objet=".intval($id)." AND id_auteur=".intval($id_auteur));
			else
				$row = sql_fetsel("id_auteur", "spip_auteurs_articles", "id_article=".intval($id)." AND id_auteur=".intval($id_auteur));

			if ($row) {
				$autoriser = true;
				// utilisation l'operateur 'OR' pour elargir ce droit 
				$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
			}
		}

	
		// Cas du redacteur qui publie tout
		if ($cistatut=='ciredvaltout' AND !defined('_CIRV_PAS_CIREDVALTOUT')) {
			if (!$qui['restreint'] OR !$id_rubrique OR in_array($id_rubrique, $qui['restreint'])) {					
				// si les autres auteurs de l'article ne peuvent pas le publier	
				if (!cirv_un_autre_auteur_peut_publier($id, $id_auteur)) {
					$autoriser = true;
					// utilisation l'operateur 'OR' pour elargir ce droit 
					$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
				}
			}
		}

	}
	
	return $param;	
}


function cirv_cistatut_auteur_rubrique($id_rubrique=0, $cistatut='') {
	if (!$cistatut){
		if (isset($GLOBALS['visiteur_session']['cistatut']) && $GLOBALS['visiteur_session']['cistatut'])
			$cistatut = $GLOBALS['visiteur_session']['cistatut'];
	}

	// compatibilite avec le plugin CIAR
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');		
		if ($statut_dans_ec = ciar_auteur_ec_statut($id_rubrique))
			$cistatut = $statut_dans_ec;    	
	}
	
	return $cistatut;
}


function cirv_quete_rubrique($id_article) {
	$id_rubrique = 0;

	if ($id_article>0) {
		$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=".intval($id_article));
		if ($row)
			$id_rubrique = $row['id_rubrique'];
	}
	
	return $id_rubrique;
}

function cirv_un_autre_auteur_peut_publier($id_article, $id_auteur) {
	static $return;

	if (!isset($return[$id_article][$id_auteur])) {
		$return = array($id_article=>array($id_auteur=>false));
	
		// les auteurs de l'article et leur statut
		if (spip_version()>=3)
			$result = sql_select("auteurs.*","spip_auteurs AS auteurs LEFT JOIN spip_auteurs_liens AS lien ON auteurs.id_auteur=lien.id_auteur", "lien.objet='article' AND lien.id_objet=".intval($id_article));
		else
			$result = sql_select("auteurs.*","spip_auteurs AS auteurs LEFT JOIN spip_auteurs_articles AS lien ON auteurs.id_auteur=lien.id_auteur","lien.id_article=".intval($id_article));
			
		while ($row = sql_fetch($result)) {
			if ($row['id_auteur']!=$id_auteur){
				$statut_auteur = $row['statut'];
				if ($row['cistatut'])
					$statut_auteur = $row['cistatut'];
				
				// compatibilite avec le plugin CIAR
				if (defined('_DIR_PLUGIN_CIAR')){
					$id_rubrique = cirv_quete_rubrique($id_article);
					$row = sql_fetsel("cistatut_auteur_rub", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=".$id_rubrique." AND id_auteur=".$row['id_auteur'],"","");
					if ($row) {
						if ($row['cistatut_auteur_rub'])
							$statut_auteur = $row['cistatut_auteur_rub'];
					}
				}
				
				// un autre auteur peut publier
				if (in_array($statut_auteur, array("eccma","0minirezo","ciredval","ciredvaltout"))){
					$return = array($id_article=>array($id_auteur=>true));
					break;
				}
			}
		}
	}
	
	return $return[$id_article][$id_auteur];
}
?>