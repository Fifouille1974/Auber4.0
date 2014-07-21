<?php
/**
 * Plugin Formulaire
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 *
 * Syntaxe à mettre dans un squelette article :
 * #FORMULAIRE_CIFORM_CONTACTER{#ID_ARTICLE} 
 */

include_spip('inc/texte');	// pour safehtml et inc/filtres
include_spip('inc/ciform_commun');


function formulaires_ciform_contacter_charger_dist($id_article, $titre_article=''){	
	
	$valeurs = array('session_email'=>'', 'question_titre'=>'', 'question_descriptif'=>'');

	// legend du fieldset
	$valeurs['legend'] = supprimer_numero($titre_article);	
	
	// Antispam
	$valeurs['_hidden'] = "<input type='hidden' name='ciformcle' value='".ciform_cle()."' />";
		
	return $valeurs;
}


function formulaires_ciform_contacter_verifier_dist($id_article, $titre_article=''){
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


function formulaires_ciform_contacter_traiter_dist($id_article, $titre_article=''){
	
	// Antispam
	$cispam = ciform_antispam('contacter', array('question_descriptif')); 	
	if (isset($cispam) AND is_array($cispam))
		return array('message_ok'=>$cispam['message_erreur']);

	$envoyer_mail = charger_fonction('envoyer_mail','inc');

	// securite
	$id_article = intval($id_article);
	$nom_site_spip = nettoyer_titre_email($GLOBALS['meta']['nom_site']);
	$titrebrut = nettoyer_titre_email(corriger_caracteres(_request('question_titre')));
	$descriptifbrut = textebrut(propre(corriger_caracteres(_request('question_descriptif'))));
	if ($GLOBALS['visiteur_session']['email'])
		$mail_expediteur = $GLOBALS['visiteur_session']['email'];
	else
		$mail_expediteur = safehtml(_request('session_email'));

	// article
	$article_titre = sql_getfetsel("titre", "spip_articles", "id_article=".$id_article);
	$article_titre = textebrut(supprimer_numero(typo($article_titre)));
	$article_url = generer_url_public("article", "id_article=$id_article", true, false);


	// envoyer un mail
	$sujet = '['.$nom_site_spip.'] '.$titrebrut;
	$parauteur = " " ._T('ciform:ci_par_auteur', array('auteur' => $mail_expediteur));
	
	$courr = _T('ciform:ci_message_auto')."\n\n";
	$courr .= _T('ciform:ci_poste_par', array('parauteur' => $parauteur, 'titre'=>$article_titre))."\n";
	$courr .= $article_url."\n";
	$courr .= "\n\n".$titrebrut."\n\n".$descriptifbrut."\n";
	
	if (spip_version()>=3)
		$result = sql_select("auteurs.*", "spip_auteurs AS auteurs, spip_auteurs_liens AS lien", "lien.objet='article' AND lien.id_objet=$id_article AND auteurs.id_auteur=lien.id_auteur","","id_auteur");
	else
		$result = sql_select("auteurs.*", "spip_auteurs AS auteurs, spip_auteurs_articles AS lien", "lien.id_article=$id_article AND auteurs.id_auteur=lien.id_auteur","","id_auteur");

	while ($row = sql_fetch($result)) {
		$email_auteur = trim($row["email"]);
		if (strlen($email_auteur) < 3) continue;
		$envoyer_mail($email_auteur, $sujet, $courr);
	}
	
	return array('message_ok'=>_T('ciform:envoi_effectue'));
	
}

?>