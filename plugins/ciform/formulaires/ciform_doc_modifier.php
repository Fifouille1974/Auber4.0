<?php
/**
 * Plugin Saisie facile
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/actions');
include_spip('inc/editer');
include_spip('ciform_fonctions');
include_spip('inc/filtres');

function formulaires_ciform_doc_modifier_charger_dist($id_document, $id_article, $id_rubrique, $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden='')
{
	$id_document = intval($id_document);
	$id_article = intval($id_article);
	
	if(!$id_document OR !$id_article)
		return false;
		
	// verifier que l'utilisateur est authentifie
	if (!isset($GLOBALS['visiteur_session']['id_auteur']))
		return false;

	// verifier que le document appartient a l'article
	$n = sql_countsel("spip_documents_liens", "id_document=".$id_document." AND objet='article' AND id_objet=".$id_article);
	if (!$n)
		return false;

	//  si plugin ciparam est actif, verifier que l'article a la forme d'article requise
	// ne pas utiliser la variable row car elle sert par ailleurs
	if (defined('_DIR_PLUGIN_CIPARAM')) {
		$row2 = sql_fetsel("ciforme", "spip_articles", "id_article=$id_article", "", "");
		if ($row2['ciforme']!='_wiki')
			return false;
	}

		
	$valeurs = formulaires_editer_objet_charger('document',$id_document,$id_article,$lier_trad,$retour,$config_fonc,$row,$hidden);

	// Pour SPIP 2.1
	$valeurs['id_rubrique'] = $id_rubrique;

	// Impératif : preciser que le formulaire doit etre securise auteur/action
	// sinon rejet
	$valeurs['_action'] = array("ciform_doc_modifier",$id_document);

	// préfixe ci_ sinon, en cas d'erreur, l'url d'action sera nettoyée
	// des champs figurant dans le tableau valeur. 
	$valeurs['ci_id_article'] = $id_article;
	
	$documents = _request("show_docs");
	if ($documents)	{		
		$pos = strpos($documents, ",");
		if ($pos === false) {
			// au plus un document
		} else {
			// un zip decompresse, rediriger vers l'article
			
			// syntaxe avec '&' sinon cela ne fonctionne pas ici
			$ciredirect = generer_url_public('article').'&id_article='.$id_article;
			include_spip('inc/headers');
		    redirige_par_entete($ciredirect);
		}
	}
	
	return $valeurs;
}

function formulaires_ciform_doc_modifier_verifier_dist($id_document, $id_article, $id_rubrique, $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden='')
{
	$erreurs = array();

	return $erreurs;
}

function formulaires_ciform_doc_modifier_traiter_dist($id_document, $id_article, $id_rubrique, $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden='')
{
	// Preparation du retour
	$retour = generer_url_public("article", "id_article=$id_article");

	if ($id_document>0) {

		// Enregistrer les modifications
		$modifs = array();
		$cititre = _request('titre');
		$cidescriptif = _request('descriptif');

		if ($cititre !== NULL)
			$modifs["titre"] = $cititre;
		else
			$modifs["titre"] = '';

		if ($cidescriptif !== NULL)
			$modifs["descriptif"] = $cidescriptif;
		else
			$modifs["descriptif"] = '';

		sql_updateq("spip_documents", $modifs, "id_document=$id_document");
	

		// Enlever les réservations de l'auteur
		$texte = ciform_ps($id_article);

		if ($texte AND $GLOBALS['visiteur_session']['id_auteur']) {
			$cle = strval($id_document);
			$pattern = ',<.*>'.$GLOBALS['visiteur_session']['id_auteur'].'</.*>,Uims';
			$texte = preg_replace($pattern, '', $texte);	
			sql_updateq("spip_articles", array("ps" => $texte), "id_article=$id_article");
		}

		
		// Prevenir par mail les auteurs affectés à l'article
		$envoyer_mail = charger_fonction('envoyer_mail','inc');
		$nom_site_spip = nettoyer_titre_email($GLOBALS['meta']['nom_site']);
		$row3 = sql_fetsel("titre", "spip_articles", "id_article=$id_article", "", "");
		$article = nettoyer_titre_email(corriger_caracteres($row3['titre']));
		$titrebrut = nettoyer_titre_email(corriger_caracteres($cititre));
		$url = generer_url_public("article", "id_article=$id_article", true, false);
	
		$sujet = "[$nom_site_spip] $article";
		
		$courr = _T('ciform:ci_message_auto')."\n\n";
		$courr .= _T('ciform:wiki_upload', array('article' => $article, 'document' => $titrebrut,'nom' => $GLOBALS['visiteur_session']['nom']))."\n";
		$courr .= "$url\n";
		
		if (spip_version()>=3)
			$result = sql_select("auteurs.*", "spip_auteurs AS auteurs, spip_auteurs_liens AS lien", "lien.objet='article' AND lien.id_objet=$id_article AND auteurs.id_auteur=lien.id_auteur","","id_auteur");
		else
			$result = sql_select("auteurs.*", "spip_auteurs AS auteurs, spip_auteurs_articles AS lien", "lien.id_article=$id_article AND auteurs.id_auteur=lien.id_auteur","","id_auteur");
	
		while ($row = sql_fetch($result)) {
			$email_auteur = trim($row["email"]);
			if (strlen($email_auteur) >= 3) {
				if ($email_auteur!=$GLOBALS['visiteur_session']['email']) {
					$envoyer_mail($email_auteur, $sujet, $courr);
				}
			}
		}
	
	}	

	$res['message_ok'] = "";
	$res['redirect'] = $retour;

	return $res;
}


?>