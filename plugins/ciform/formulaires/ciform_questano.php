<?php
/**
 * Plugin Formulaire
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 *
 * Syntaxe à mettre dans un squelette d'article :
 * #FORMULAIRE_CIFORM_QUESTANO{#ID_RUBRIQUE, #ID_ARTICLE, #URL_ARTICLE} 
 */

include_spip('action/editer_article'); // pour insert_article
include_spip('inc/texte');	// pour safehtml et inc/filtres
include_spip('inc/ciform_commun');


function formulaires_ciform_questano_charger_dist($id_rubrique=0, $id_conteneur=0, $retour=''){	

	$ciautorise = true;

	//  si plugin ciparam est actif, verifier que le conteneur a la forme d'article requise
	$id_conteneur = intval($id_conteneur);
	if ($id_conteneur>0) {
		if (defined('_DIR_PLUGIN_CIPARAM')) {
			$ciautorise = false;
			$row = sql_fetsel("ciforme", "spip_articles", "id_article=$id_conteneur", "", "");
			if ($row['ciforme']=='_questano')
				$ciautorise = true;
		}
	}
	
	if (!$ciautorise)
		return false;
	
	$valeurs = array('question_titre'=>'', 'question_descriptif'=>'');

	// Antispam
	$valeurs['_hidden'] = "<input type='hidden' name='ciformcle' value='".ciform_cle()."' />";
		
	return $valeurs;
}


function formulaires_ciform_questano_verifier_dist($id_rubrique=0, $id_conteneur=0, $retour=''){
	$erreurs = array();

	// verifier que les champs obligatoires sont bien la :	
	foreach(array('question_titre','question_descriptif') as $obligatoire)
		if (!_request($obligatoire)) $erreurs[$obligatoire] = _T('info_obligatoire');
	
	// longueur minimale a respecter
	if (strlen(_request('question_titre')) < 3) $erreurs['question_titre'] = _T('ciform:question_attention_trois_caracteres');
	if (strlen(_request('question_descriptif')) < 10) $erreurs['question_descriptif'] = _T('ciform:question_attention_dix_caracteres');

			
	if (count($erreurs))
		$erreurs['message_erreur'] = _T('avis_erreur');

	return $erreurs;
}


function formulaires_ciform_questano_traiter_dist($id_rubrique=0, $id_conteneur=0, $retour=''){
	
	// Antispam
	$cispam = ciform_antispam('questano', array('question_descriptif')); 	
	if (isset($cispam) AND is_array($cispam))
		return array('message_ok'=>$cispam['message_erreur']);


	// securite
	$ciidconteneur = intval($id_conteneur);
	$titre_safe = safehtml(_request('question_titre'));
	$descriptif_safe = safehtml(_request('question_descriptif'));	

	// ne pas enregistrer l'article dans le SGBD		

	// envoyer un mail
	$envoyer_mail = charger_fonction('envoyer_mail','inc');
	$nom_site_spip = nettoyer_titre_email($GLOBALS['meta']['nom_site']);
	$adresse_site = $GLOBALS['meta']['adresse_site'];
	$titrebrut = nettoyer_titre_email(corriger_caracteres($titre_safe));
	$descriptifbrut = textebrut(propre(corriger_caracteres($descriptif_safe)));

	$sujet = "[$nom_site_spip] $titrebrut";
	
	$courr = "( "._T('ciform:questano_mail_depuis_site')." : ".$adresse_site." )";
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