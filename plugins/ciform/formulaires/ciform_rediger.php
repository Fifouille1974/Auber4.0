<?php
/**
 * Plugin Formulaire
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 *
 * Syntaxe à mettre dans un squelette :
 * #FORMULAIRE_CIFORM_REDIGER{#SELF} 
 */

include_spip('action/editer_article'); // pour insert_article
include_spip('inc/texte');	// pour safehtml et inc/filtres
include_spip('inc/ciform_commun');


function formulaires_ciform_rediger_charger_dist($retour=''){

	$valeurs = array('session_nom'=>'', 'session_email'=>'', 'rubrique'=>'', 'titre'=>'', 'texte'=>'', 'descriptif'=>'');

	// liste des rubriques possibles pour proposer l'article
	// si pas de rubrique, le formulaire ne sera pas actif
	$rediger_actif = 'non';
	$res = "";

	if (defined('_DIR_PLUGIN_CIPARAM')) {
		$result = sql_select("rub.id_rubrique, rub.titre", "spip_rubriques AS rub, spip_ci_raccourcis_rubriques AS lien", "rub.id_rubrique=lien.id_rubrique AND lien.raccourci='_recevoirarticlevisiteur' AND rub.statut='publie'", "", "rub.titre");
		while ($row = sql_fetch($result)) { 
      		$res .=	'<option value="'.$row['id_rubrique'].'">'.interdire_scripts(supprimer_numero($row['titre'])).'&nbsp;</option>';
      		$rediger_actif = 'oui';
		}
	} else {
		$row_mot = sql_fetsel("id_mot", "spip_mots", "titre='_recevoirarticlevisiteur'","","");
		if ($row_mot) {
			$id_mot = $row_mot['id_mot'];
			if (spip_version()>=3)
				$result = sql_select("rub.id_rubrique, rub.titre", "spip_rubriques AS rub, spip_mots_liens AS lien", "lien.objet='rubrique' AND rub.id_rubrique=lien.id_objet AND lien.id_mot=".$id_mot." AND rub.statut='publie'","","rub.titre");
			else
				$result = sql_select("rub.id_rubrique, rub.titre", "spip_rubriques AS rub, spip_mots_rubriques AS lien", "rub.id_rubrique=lien.id_rubrique AND lien.id_mot=".$id_mot." AND rub.statut='publie'", "", "rub.titre");
			while ($row = sql_fetch($result)) { 
	      		$res .=	'<option value="'.$row['id_rubrique'].'">'.interdire_scripts(supprimer_numero($row['titre'])).'&nbsp;</option>';
	      		$rediger_actif = 'oui';
			}
		}
	}

	$valeurs['_ciform_rediger_rubrique'] = $res;
	$valeurs['rediger_actif'] = $rediger_actif;
		
	// Antispam
	$valeurs['_hidden'] = "<input type='hidden' name='ciformcle' value='".ciform_cle()."' />";
		
	return $valeurs;
}


function formulaires_ciform_rediger_verifier_dist($retour=''){
	$erreurs = array();
	
	// verifier que les champs obligatoires sont bien la :
	if (!isset($GLOBALS['visiteur_session']['nom']) 
		AND !_request('session_nom')) 
		$erreurs['session_nom'] = _T('info_obligatoire');
	
	if (!isset($GLOBALS['visiteur_session']['email']) 
		AND !_request('session_email')) 
		$erreurs['session_email'] = _T('info_obligatoire');
		
	foreach(array('rubrique', 'titre') as $obligatoire)
		if (!_request($obligatoire)) $erreurs[$obligatoire] = _T('info_obligatoire');
		
	// id_rubrique est un nombre
	if (intval(_request('rubrique'))<1) $erreurs['rubrique'] = _T('info_obligatoire');
		
	// longueur minimale a respecter
	if (strlen(_request('titre')) < 3) $erreurs['titre'] = _T('ciform:rediger_attention_trois_caracteres');
	if (strlen(_request('texte')) < 10) $erreurs['texte'] = _T('ciform:rediger_attention_dix_caracteres');

	// verifier que si un mail a ete saisi, il est bien valide :
	if (_request('session_email') AND !email_valide(_request('session_email')))
		$erreurs['session_email'] = _T('info_email_invalide');
		
	if (count($erreurs))
		$erreurs['message_erreur'] = _T('avis_erreur');

	return $erreurs;
}


function formulaires_ciform_rediger_traiter_dist($retour=''){
	
	// Antispam
	$cispam = ciform_antispam('rediger', array('texte','descriptif')); 	
	if (isset($cispam) AND is_array($cispam))
		return array('message_ok'=>$cispam['message_erreur']);


	// impératif sinon pas de maj
	$id_article = 'new';
	$id_rubrique = intval(_request('rubrique'));

			
	// securite
	$pseudo = "\n\n"._T('ciform:auteur')." : ";
	if ($GLOBALS['visiteur_session']['nom'])
	      $pseudo .= $GLOBALS['visiteur_session']['nom'];
	else
	      $pseudo .= safehtml(_request('session_nom'));

	// stocker adresse mail dans le champ ps
	if ($GLOBALS['visiteur_session']['email'])
		set_request('ps', $GLOBALS['visiteur_session']['email']);
	else
		set_request('ps', safehtml(_request('session_email')));
	      
	set_request('titre', safehtml(_request('titre')));
	set_request('descriptif', safehtml(_request('descriptif')));
	// Mettre le pseudo a la fin du texte
	set_request('texte', safehtml(_request('texte')).$pseudo);
		

	// traiter
	$id_article = insert_article($id_rubrique);
	if ($id_article > 0) $err = articles_set($id_article);
	// Mettre le statut prop (directement sinon on se heurte a la gestion des droits) 
	sql_updateq("spip_articles", array("statut" => "prop"), "id_article=$id_article");
	

	// si message d'erreur
	if ($err)
		return array('message_ok'=>_T('avis_erreur').' : '.$err);

	
	// Notifications
	if ($notifications = charger_fonction('notifications', 'inc')) {
		$notifications('instituerarticle', $id_article,
			array('statut' => 'prop', 'statut_ancien' => 'prepa')
		);
	}
	
	return array('message_ok'=>_T('ciform:envoi_effectue'));	
}

?>