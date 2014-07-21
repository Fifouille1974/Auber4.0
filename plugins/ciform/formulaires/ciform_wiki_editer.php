<?php
/**
 * Plugin Formulaire
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 *
 * Syntaxe à mettre dans un squelette d'article :
 * #FORMULAIRE_CIFORM_WIKI_EDITER{#ID_ARTICLE,#SELF} 
 */

include_spip('inc/editer'); 
include_spip('inc/filtres');

function formulaires_ciform_wiki_editer_charger_dist($id_article, $retour=''){

	// Verifier que l'utilisateur est authentifie
	if (!isset($GLOBALS['visiteur_session']['id_auteur']))
		$return = false;
	
	$id_article = intval($id_article);
	$valeurs['id_article'] = $id_article;

	if ($id_article) {
		$row = sql_fetsel('texte', 'spip_articles',"id_article=".$id_article,'','','');
		$valeurs['texte'] = $row['texte'];

		// Ajouter les controles md5 si l'article existe
		$valeurs['_hidden'] = controles_md5($row);
		
		// Est-ce que quelqu'un a deja ouvert l'article en edition ?
		if ($GLOBALS['meta']['articles_modif'] != 'non') {
			include_spip('inc/drapeau_edition');
			$modif = mention_qui_edite($id_article, 'article');
			if ($modif) {
				$valeurs['_ciform_qui_edite'] = _T('avis_article_modifie', $modif);
			}
		}

	}

	return $valeurs;
}


function formulaires_ciform_wiki_editer_verifier_dist($id_article, $retour=''){
	$erreurs = array();
	
	$id_article = intval($id_article);
	if ($id_article) {
		$conflits = controler_contenu('article',$id_article);
		if (count($conflits)) {
			foreach($conflits as $champ=>$conflit){
				$erreurs[$champ] .= _T("alerte_modif_info_concourante")."<br /><textarea readonly='readonly' class='forml'>".$conflit['base']."</textarea>";
			}
		}
	}

	return $erreurs;
}


function formulaires_ciform_wiki_editer_traiter_dist($id_article, $retour=''){

	// traiter
	if ($id_article > 0) {
		// enregistrer que si le texte a ete modifie	
		if (md5(_request('texte')) != _request('ctr_texte')) {
			include_spip('inc/modifier');

			if (spip_version()>=3)
				modifier_contenu('article',$id_article, array('date_modif' => 'date_modif'), array("texte" => _request('texte')));
			else
				revision_article($id_article, array("texte" => _request('texte')));

				
			// envoyer un mail
			$envoyer_mail = charger_fonction('envoyer_mail','inc');

			$row = sql_fetsel('titre', 'spip_articles',"id_article=".$id_article,'','','');
			$titrebrut = nettoyer_titre_email(corriger_caracteres($row['titre']));
			
			$nom_site_spip = nettoyer_titre_email($GLOBALS['meta']['nom_site']);
			$url = generer_url_public("article", "id_article=$id_article", true, false);
		
			$sujet = "[$nom_site_spip] $titrebrut";
			
			$courr = _T('ciform:ci_message_auto')."\n\n";
			$courr .= _T('ciform:wiki_mail1', array('titre' => $titrebrut, 'login' => $GLOBALS['visiteur_session']['nom']))."\n";
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
	}	
	
	// retour a l'article (et pas au formulaire)
	$res['message_ok'] = "";
	$res['redirect'] = generer_url_public("article", "id_article=$id_article");;

	return $res;
}

?>