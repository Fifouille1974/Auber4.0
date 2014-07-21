<?php
/**
 * Plugin Formulaire
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 *
 * Syntaxe à mettre dans un squelette d'article :
 * #FORMULAIRE_CIFORM_QUESTIONNER{#ID_RUBRIQUE, #ID_ARTICLE, #URL_ARTICLE} 
 */

include_spip('action/editer_article'); // pour insert_article
include_spip('inc/texte');	// pour safehtml et inc/filtres
include_spip('inc/ciform_commun');


function formulaires_ciform_questionner_charger_dist($id_rubrique=0, $id_conteneur=0, $retour=''){	

	$ciautorise = true;

	//  si plugin ciparam est actif, verifier que le conteneur a la forme d'article requise
	$id_conteneur = intval($id_conteneur);
	if ($id_conteneur>0) {
		if (defined('_DIR_PLUGIN_CIPARAM')) {
			$ciautorise = false;
			$row = sql_fetsel("ciforme", "spip_articles", "id_article=$id_conteneur", "", "");
			if ($row['ciforme']=='_questionner')
				$ciautorise = true;
		}
	}
	
	if (!$ciautorise)
		return false;
	
	$valeurs = array('session_email'=>'', 'question_titre'=>'', 'question_descriptif'=>'');

	// Antispam
	$valeurs['_hidden'] = "<input type='hidden' name='ciformcle' value='".ciform_cle()."' />";
		
	return $valeurs;
}


function formulaires_ciform_questionner_verifier_dist($id_rubrique=0, $id_conteneur=0, $retour=''){
	$erreurs = array();

	// verifier que les champs obligatoires sont bien la :
	if (!isset($GLOBALS['visiteur_session']['email']) 
		AND !_request('session_email')) 
		$erreurs['session_email'] = _T('info_obligatoire');
	
	foreach(array('question_titre','question_descriptif') as $obligatoire)
		if (!_request($obligatoire)) $erreurs[$obligatoire] = _T('info_obligatoire');
	
	// longueur minimale a respecter
	if (strlen(_request('question_titre')) < 3) $erreurs['question_titre'] = _T('ciform:question_attention_trois_caracteres');
	if (strlen(_request('question_descriptif')) < 10) $erreurs['question_descriptif'] = _T('ciform:question_attention_dix_caracteres');

	// verifier que si un mail a ete saisi, il est bien valide :
	if (_request('session_email') AND !email_valide(_request('session_email')))
		$erreurs['session_email'] = _T('info_email_invalide');
			
	if (count($erreurs))
		$erreurs['message_erreur'] = _T('avis_erreur');

	return $erreurs;
}


function formulaires_ciform_questionner_traiter_dist($id_rubrique=0, $id_conteneur=0, $retour=''){
	
	// Antispam
	$cispam = ciform_antispam('questionner', array('question_descriptif')); 	
	if (isset($cispam) AND is_array($cispam))
		return array('message_ok'=>$cispam['message_erreur']);


	// securite
	$ciidconteneur = intval($id_conteneur);
	set_request('titre', safehtml(_request('question_titre')));
	set_request('descriptif', safehtml(_request('question_descriptif')));
	// stocker adresse mail dans le champ ps
	if ($GLOBALS['visiteur_session']['email'])
		set_request('ps', $GLOBALS['visiteur_session']['email']);
	else
		set_request('ps', safehtml(_request('session_email')));
	

	// traiter
	$err = '';
	$id_article = insert_article($id_rubrique);
	if ($id_article > 0) $err = articles_set($id_article);
	// Mettre le statut prop (directement sinon on se heurte a la gestion des droits) 
	sql_updateq("spip_articles", array("statut" => "prop"), "id_article=$id_article");
	

	// si message d'erreur
	if ($err)
		return array('message_ok'=>_T('avis_erreur').' : '.$err);
		

	// envoyer un mail
	$envoyer_mail = charger_fonction('envoyer_mail','inc');
	$nom_site_spip = nettoyer_titre_email($GLOBALS['meta']['nom_site']);
	$titrebrut = nettoyer_titre_email(corriger_caracteres(_request('titre')));
	$descriptifbrut = textebrut(propre(corriger_caracteres(_request('descriptif'))));
	$url = generer_url_ecrire("articles", "id_article=$id_article", true);
	if (defined('_DIR_PLUGIN_CISF'))
		$url = generer_url_public("cisf_article", "id_article=$id_article", true, false);

	$sujet = _T('info_propose_1', array('nom_site_spip' => $nom_site_spip, 'titre' => $titrebrut));
	
	$courr = _T('ciform:ci_message_auto')."\n\n";
	$courr .= _T('ciform:mail_de_la_part')._request('ps')."\n";
	$courr .= "$url\n";
	$courr .= "\n\n".$titrebrut."\n\n".$descriptifbrut."\n";
	
	if (spip_version()>=3)
		$result = sql_select("auteurs.*", "spip_auteurs AS auteurs, spip_auteurs_liens AS lien", "lien.objet='article' AND lien.id_objet=$ciidconteneur AND auteurs.id_auteur=lien.id_auteur","","id_auteur");
	else
		$result = sql_select("auteurs.*", "spip_auteurs AS auteurs, spip_auteurs_articles AS lien", "lien.id_article=$ciidconteneur AND auteurs.id_auteur=lien.id_auteur","","id_auteur");

	while ($row = sql_fetch($result)) {
		$email_auteur = trim($row["email"]);
		if (strlen($email_auteur) < 3) continue;
		$envoyer_mail($email_auteur, $sujet, $courr);
	}
	
	return array('message_ok'=>_T('ciform:envoi_effectue'));
	
}

?>