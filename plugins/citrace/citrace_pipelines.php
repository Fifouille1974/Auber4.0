<?php
/**
 * Plugin citrace : tracer certaines actions
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/citrace');
include_spip('inc/filtres');


/**
 * Tracer dans des logs specifiques
 *
 * @param array
 * @return array
 */
function citrace_post_edition($tableau){
	// contourner le cas d'un double appel du pipeline sur la meme table avec la meme action
	static $actions = array();
	if (isset($tableau['args']['table'])){
		$table = $tableau['args']['table'];
		if ($actions AND isset($tableau['args']['action']) AND in_array($table.'/'.$tableau['args']['action'],$actions)) 
			return $tableau;
		else
			$actions[] = $table.'/'.$tableau['args']['action'];
	}
	
	// action sur un article
	if (isset($tableau['args']['action']) 
	    AND isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_articles') {
	    	$id_article = intval($tableau['args']['id_objet']);
		    if ($id_article>0) {
				include_spip('inc/texte');
				$row = sql_fetsel('*', 'spip_articles', 'id_article='.$id_article);
				$id_rubrique = $row['id_rubrique'];
				$article_msg = '('.interdire_scripts(supprimer_numero($row['titre'])).')'." - id_rubrique:".$id_rubrique;

				$citrace = charger_fonction('citrace', 'inc');
				
				// instituer un article
				if ($tableau['args']['action']=='instituer' AND isset($tableau['args']['statut_ancien'])){
		    		if ($row['statut'] != $tableau['args']['statut_ancien']){
						$article_msg .= " - statut_new:".$row['statut']." - statut_old:".$tableau['args']['statut_ancien']." - date_publication:".$row['date']." (meta post_dates :".$GLOBALS['meta']["post_dates"].")";
						$action = "changement de statut de l'article";
						
						// publication d'un article
			    		if ($row['statut'] == 'publie')
							$action = "publication article";
						// depublication d'un article
			    		elseif ($tableau['args']['statut_ancien'] == 'publie')
							$action = "depublication article";
							
						// mise a la poubelle d'un article
			    		if ($row['statut'] == 'poubelle')
							$action = "poubelle article";
							
						$citrace('article', $id_article, $action, $article_msg, $id_rubrique);
		    		}					
				}
				// modifier un article
				elseif ($tableau['args']['action']=='modifier'){
					// uniquement pour les articles publies
		    		if ($row['statut'] == 'publie')
						$citrace('article', $id_article, 'modification article', $article_msg, $id_rubrique);
				}				
			}
    }	

	// action sur une rubrique
	if (isset($tableau['args']['action']) 
	    AND isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_rubriques') {
	    	$id_rubrique = intval($tableau['args']['id_objet']);
		    if ($id_rubrique>0) {
				include_spip('inc/texte');
				$row = sql_fetsel('*', 'spip_rubriques', 'id_rubrique='.$id_rubrique);
				$rubrique_msg = '('.interdire_scripts(supprimer_numero($row['titre'])).')';
				
				// modifier un rubrique
				if ($tableau['args']['action']=='modifier'){
					// uniquement pour les rubriques publies
		    		if ($row['statut'] == 'publie'){
						$citrace = charger_fonction('citrace', 'inc');
						$citrace('rubrique', $id_rubrique, 'modification rubrique', $rubrique_msg, $id_rubrique);
		    		}
				}				
			}
    }	
    
    // action sur un document ou une image
	if (isset($tableau['args']['operation'])  
		AND ((isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_documents') 
			OR (isset($tableau['args']['table_objet']) AND $tableau['args']['table_objet']=='documents'))) {
		$id_document = intval($tableau['args']['id_objet']);
		if ($id_document>0) {
			$row = sql_fetsel('*', 'spip_documents', 'id_document='.$id_document);
			$document_msg = '('.$row['fichier'].')';

		    // ajout ou remplacement de document ou d'image
			if ($tableau['args']['operation']=='ajouter_document'){
							
				// le pipeline n'indique pas si c'est un remplacement, aussi il faut indiquer date et maj
				$commentaire = $document_msg." - champ date:".$row['date']." - champ maj:".$row['maj'];
	
				// le pipeline ne passe pas le lien, aussi il faut les indiquer
				$commentaire .= " - liens :";
				$id_rubrique = '';
				$res = sql_select('*', 'spip_documents_liens', 'id_document='.$id_document);
				while ($row = sql_fetch($res)){
					$commentaire .= " ".$row['objet'].$row['id_objet'];
					if (!$id_rubrique)
						$id_rubrique = citrace_rubrique_de_objet($row['objet'], $row['id_objet']);
				}

				$citrace = charger_fonction('citrace', 'inc');
				$citrace('document', $id_document, 'ajouter document', $commentaire, $id_rubrique);
			}
			
		    // delier un document ou une image
			if ($tableau['args']['operation']=='delier_document'){
				if (isset($tableau['args']['objet']) AND isset($tableau['args']['id'])) {
					$commentaire = $document_msg." - lien : ".$tableau['args']['objet'].$tableau['args']['id'];
					$id_rubrique = citrace_rubrique_de_objet($tableau['args']['objet'], $tableau['args']['id']);
					$citrace = charger_fonction('citrace', 'inc');
					$citrace('document', $id_document, 'delier document', $commentaire, $id_rubrique);
				}
			}

		    // supprimer un document ou une image
			if ($tableau['args']['operation']=='supprimer_document'){
				$commentaire = $id_document;
				$citrace = charger_fonction('citrace', 'inc');
				$citrace('document', $id_document, 'supprimer document', $commentaire);
			}
		}
	}

	// action sur un forum
	if (isset($tableau['args']['action']) AND in_array($tableau['args']['action'], array('instituer','modifier'))
	    AND isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_forum') {

    	$id_forum = intval($tableau['args']['id_objet']);
	    if ($id_forum>0) {
			$row = sql_fetsel('*', 'spip_forum', 'id_forum='.$id_forum);

			// forum public uniquement
			if (substr($row['statut'],0,3)!='pri') {								
				$commentaire = 'statut:'.$row['statut'];
				$f_objet = '';
				$f_id_objet = '';
				
				if (spip_version()>=3) {
					$f_objet = $row['objet'];
					$f_id_objet = $row['id_objet'];
				} else {
					if (intval($row['id_article'])>0){
						$f_objet = 'article';
						$f_id_objet = $row['id_article'];
					} elseif (intval($row['id_rubrique'])>0){
						$f_objet = 'rubrique';
						$f_id_objet = $row['id_rubrique'];
					} elseif (intval($row['id_breve'])>0){
						$f_objet = 'breve';
						$f_id_objet = $row['id_breve'];
					}
				}

				$accepter_forum = $GLOBALS['meta']["forums_publics"];
				if ($f_objet=='article'){
					$art_accepter_forum = sql_getfetsel('accepter_forum', 'spip_articles', "id_article = ". intval($f_id_objet));
					if ($art_accepter_forum)
						$accepter_forum = $art_accepter_forum;
				}

				$commentaire .= " - lien: ".$f_objet.$f_id_objet." (accepter_forum: ".$accepter_forum.")";

				$id_rubrique = citrace_rubrique_de_objet($f_objet, $f_id_objet);
				
				$citrace = charger_fonction('citrace', 'inc');
				$citrace('forum', $id_forum, ($row['statut']=='publie' ? 'publication ' : 'depublication').'forum', $commentaire, $id_rubrique);
			}
		}
    }	
	
	return $tableau;
}


function citrace_pre_edition($tableau){
	// contourner le cas d'un double appel du pipeline sur la meme table avec la meme action
	static $actions = array();
	if (isset($tableau['args']['table'])){
		$table = $tableau['args']['table'];
		if ($actions AND isset($tableau['args']['action']) AND in_array($table.'/'.$tableau['args']['action'],$actions)) 
			return $tableau;
		else
			$actions[] = $table.'/'.$tableau['args']['action'];
	}

	// changement de rubrique pour un article publie
	if (isset($tableau['args']['action']) AND $tableau['args']['action']=='instituer' 
	    AND isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_articles') {
	    	$id_article = intval($tableau['args']['id_objet']);
		    if ($id_article>0) {
				include_spip('inc/texte');
				$row = sql_fetsel('*', 'spip_articles', 'id_article='.$id_article);
				if ($row){
					$old_rubrique = $row['id_rubrique'];
					if ($row['statut']=='publie'){
						$new_rubrique = (isset($tableau['data']['id_rubrique']) ? intval($tableau['data']['id_rubrique']) : 0);
		
			    		if ($new_rubrique>=1 AND $new_rubrique!=$old_rubrique){
							$commentaire = '('.interdire_scripts(supprimer_numero($row['titre'])).')'." - id_rubrique_new:".$new_rubrique." - id_rubrique_old:".$old_rubrique;
							$citrace = charger_fonction('citrace', 'inc');
							$citrace('article', $id_article, 'changement de rubrique pour article', $commentaire, $new_rubrique);
			    		}
					}
				}
			}
    }	

	// changement de statut ou de l'email d'un auteur
	if (isset($tableau['args']['action'])  
	    AND isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_auteurs') {

    	$id_auteur = intval($tableau['args']['id_objet']);
	    if ($id_auteur>0) {
			include_spip('inc/texte');
			$row = sql_fetsel('*', 'spip_auteurs', 'id_auteur='.$id_auteur);
			if ($row){
				// changement de statut d'un auteur
				if ($tableau['args']['action']=='instituer'){
					$old_statut = $row['statut'];
					$new_statut = (isset($tableau['data']['statut']) ? $tableau['data']['statut'] : '');
					$old_webmestre = $row['webmestre'];
					$new_webmestre = (isset($tableau['data']['webmestre']) ? $tableau['data']['webmestre'] : '');
	
		    		if ($new_statut AND $new_statut!=$old_statut){
						$commentaire = interdire_scripts(supprimer_numero($row['nom']))
						.' ('.interdire_scripts($row['email']).')'
						." - statut_new:".$new_statut." - statut_old:".$old_statut 
						." - webmestre_new:".$new_webmestre." - webmestre_old:".$old_webmestre;
						$citrace = charger_fonction('citrace', 'inc');
						$citrace('auteur', $id_auteur, "changement de statut pour l'auteur", $commentaire);
		    		}
				}
				
				// modifier l'email d'un auteur
				if ($tableau['args']['action']=='modifier'){
					$old_email = $row['email'];
					$new_email = (isset($tableau['data']['email']) ? $tableau['data']['email'] : '');
	
		    		if ($new_email!=$old_email){
						$commentaire = '('.interdire_scripts(supprimer_numero($row['nom'])).')'
						." - email_new:".$new_email." - email_old:".$old_email; 
						$citrace = charger_fonction('citrace', 'inc');
						$citrace('auteur', $id_auteur, "changement d'email pour l'auteur", $commentaire);
		    		}
				}
			}
		}
    }	
    
	// changement de date de publication (ou de depublication) d'un article
	if (isset($tableau['args']['action']) 
	    AND isset($tableau['args']['table']) AND $tableau['args']['table']=='spip_articles') {
	    	$id_article = intval($tableau['args']['id_objet']);
		    if ($id_article>0) {
		    	// lors du changement de statut, la date de publication est tracee par le pipeline post_edition
		    	// aussi ne pas doublonner
	    		if (!isset($tableau['data']['statut']) OR !isset($tableau['args']['statut_ancien']) OR $tableau['data']['statut'] == $tableau['args']['statut_ancien']){
					include_spip('inc/texte');
					$row = sql_fetsel('*', 'spip_articles', 'id_article='.$id_article);
					$date_old = $row['date'];
					$id_rubrique = $row['id_rubrique'];
				
					if (isset($tableau['data']['date']) AND $date_old != $tableau['data']['date']){
						$article_msg = '('.interdire_scripts(supprimer_numero($row['titre'])).')'." - id_rubrique:".$id_rubrique;
						$article_msg .= " - date_publication:".$tableau['data']['date']." - date_publication_old:".$date_old." (meta post_dates :".$GLOBALS['meta']["post_dates"].")";
						$citrace = charger_fonction('citrace', 'inc');
						$citrace('article', $id_article, 'modifier_date', $article_msg, $id_rubrique);					
					}
	    		}
		    }
    }

	return $tableau;
}


function citrace_rubrique_de_objet($objet, $id_objet){
	$id_rubrique = '';

	if ($objet AND $id_objet){
		if ($objet=='rubrique')
			$id_rubrique = $id_objet;
		elseif ($objet=='article')
			$id_rubrique = sql_getfetsel('id_rubrique', 'spip_articles', "id_article = ". intval($id_objet));
		elseif ($objet=='breve')
			$id_rubrique = sql_getfetsel('id_rubrique', 'spip_breves', "id_breve = ". intval($id_objet));
	}
	
	return $id_rubrique;
}

?>