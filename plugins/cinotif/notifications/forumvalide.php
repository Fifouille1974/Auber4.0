<?php
/*
 * Plugin Notifications (modifie par le plugin cinotif)
 * (c) 2009 SPIP
 * Distribue sous licence GPL
 *
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/cinotif_commun');


/**
 * cette notification s'execute quand on valide un message 'prop'ose,
 * dans ecrire/inc/forum_insert.php ; ici on va notifier ceux qui ne l'ont
 * pas ete a la notification forumposte (sachant que les deux peuvent se
 * suivre si le forum est valide directement ('pos' ou 'abo')
 * http://doc.spip.org/@notifications_forumvalide_dist
 *
 * @param string $quoi
 * @param int $id_forum
 */
function notifications_forumvalide_dist($quoi, $id_forum, $options) {

	$t = sql_fetsel("*", "spip_forum", "id_forum=".intval($id_forum));
	if (!$t
		// forum sur un message prive : pas de notification ici (cron)
		OR @$t['statut'] == 'perso')
		return;

	// plugin notification si present
	$prevenir_auteurs = isset($GLOBALS['notifications']['prevenir_auteurs']) AND $GLOBALS['notifications']['prevenir_auteurs'];
	// sinon voie normale
	if (spip_version()>=3) {
		if ($t['objet']=='article' AND !$prevenir_auteurs){
			$s = sql_getfetsel('accepter_forum','spip_articles',"id_article=" . $t['id_objet']);
			if (!$s)  $s = substr($GLOBALS['meta']["forums_publics"],0,3);
	
			$prevenir_auteurs = (strpos(@$GLOBALS['meta']['prevenir_auteurs'],",$s,")!==false
				OR @$GLOBALS['meta']['prevenir_auteurs'] === 'oui'); // compat
		}
	} else {	
		if ($t['id_article'] AND !$prevenir_auteurs){
			$s = sql_getfetsel('accepter_forum','spip_articles',"id_article=" . $t['id_article']);
			if (!$s)  $s = substr($GLOBALS['meta']["forums_publics"],0,3);
	
			$prevenir_auteurs = (strpos(@$GLOBALS['meta']['prevenir_auteurs'],",$s,")!==false
				OR @$GLOBALS['meta']['prevenir_auteurs'] === 'oui'); // compat
		}
	}

	include_spip('inc/texte');
	include_spip('inc/filtres');
	include_spip('inc/autoriser');

	// Qui va-t-on prevenir ?
	$tous = array();
	// Ne pas ecrire au posteur du message, ni au moderateur qui valide le forum,
	$pasmoi = array($t['email_auteur'],$GLOBALS['visiteur_session']['email']);

	// 1. Les auteurs de l'article ; si c'est un article, ceux qui n'ont
	// pas le droit de le moderer (les autres l'ont recu plus tot)
	if (spip_version()>=3) {
		if ($prevenir_auteurs) {
			$result = sql_select("auteurs.*","spip_auteurs AS auteurs, spip_auteurs_liens AS lien","lien.objet=".sql_quote($t['objet'])." AND lien.id_objet=".intval($t['id_objet'])." AND auteurs.id_auteur=lien.id_auteur");
	
	
			while ($qui = sql_fetch($result)) {
				$tous[] = $qui['email'];
/*
				if ($qui['email']) {
					if (!autoriser('modererforum', $t['objet'], $t['id_objet'], $qui['id_auteur']))
						$tous[] = $qui['email'];
					else
						// Ne pas ecrire aux auteurs deja notifies precedemment
						$pasmoi[] = $qui['email'];
				}
*/
			}
		}
	} else {
		if ($t['id_article']
		AND $prevenir_auteurs) {
			$result = sql_select("auteurs.*","spip_auteurs AS auteurs, spip_auteurs_articles AS lien","lien.id_article=".intval($t['id_article'])." AND auteurs.id_auteur=lien.id_auteur");
	
			while ($qui = sql_fetch($result)) {
				$tous[] = $qui['email'];
/*				
				if (!autoriser('modererforum', 'article', $t['id_article'], $qui['id_auteur']))
					$tous[] = $qui['email'];
				else
					// Ne pas ecrire aux auteurs deja notifies precedemment
					$pasmoi[] = $qui['email'];
*/	
			}
		}
	}

	// Quand on repond à un message dans un forum, l'auteur a qui l'on repond sera prevenu par courriel
	if (defined('_DIR_PLUGIN_CINOTIF')){
		
		// Id du message parent de id_forum
		$id_parent = intval($t['id_parent']);	
		$email_parent = '';
		$email_auteur_parent = '';
		$id_auteur_parent = 0;

		// Recherche de l'auteur du message parent
		if ($id_parent>0) {
			$row = sql_fetsel("*", "spip_forum", "id_forum=".$id_parent);
			if ($row) {
				$id_auteur_parent = intval($row['id_auteur']);
				$email_parent = $row['email_auteur'];
			}
			
			// Si l'auteur est connu dans la base
			if ($id_auteur_parent>0) {
				$row = sql_fetsel("*", "spip_auteurs", "id_auteur=".$id_auteur_parent);
				if ($row)
					$email_auteur_parent = $row['email'];
			}
		}

		// Eviter les envois en double
		if ($email_auteur_parent) {
			// si l'email est pris dans spip_auteurs
			if (!in_array($email_auteur_parent,$tous) AND !in_array($email_auteur_parent,$pasmoi))
				 $tous[] = $email_auteur_parent;
		} elseif ($email_parent) {
			// si l'email est renseigne par l'internaute, le verifier, puis le comparer en minuscules
			include_spip('inc/filtres');
			if (email_valide($email_parent)){
				$email_parent = strtolower($email_parent);
				 if (!in_array($email_parent,array_map('strtolower',$tous)) AND !in_array($email_parent,array_map('strtolower',$pasmoi)))
					 $tous[] = $email_parent;
			}
		}
	}
	
	
	$options['forum'] = $t;
	$destinataires = pipeline('notifications_destinataires',
		array(
			'args'=>array('quoi'=>$quoi,'id'=>$id_forum,'options'=>$options)
		,
			'data'=>$tous)
	);

	// Nettoyer le tableau
	// en enlevant les exclus
	notifications_nettoyer_emails($destinataires,$pasmoi);

	//
	// Envoyer les emails
	//
	if ($t['statut']!='publie'){
		if (spip_version()>=3) {
			$email_notification_forum = charger_fonction('email_notification_forum','inc');
			foreach ($destinataires as $email) {
				$texte = $email_notification_forum($t, $email);
				if ($t['statut']=='publie')
					$texte = cinotif_remplace_url_de_base($texte);
				notifications_envoyer_mails($email, $texte);
			}
		} else {	
			foreach ($destinataires as $email) {
				$texte = email_notification_forum($t, $email);
				if ($t['statut']=='publie')
					$texte = cinotif_remplace_url_de_base($texte);
				notifications_envoyer_mails($email, $texte);
			}
		}
	}

	// uniquement pour les commentaires publies sur le site public
	if (defined('_DIR_PLUGIN_CINOTIF') AND $t['statut']=='publie'){
		if (spip_version()>=3) {
			if ($t['objet']=='article')
				$id_article = intval($t['id_objet']);
		} else {
			$id_article = intval($t['id_article']);
		}
		$id_forum = intval($id_forum);

		if ($id_article) {
			$row = sql_fetsel("id_rubrique", "spip_articles", "id_article=".$id_article);
			$id_rubrique = $row['id_rubrique'];
			
			// URL specifiques
			$cipage = 'article&id_article='.$id_article.'&id_forum='.$id_forum;
			if (defined('_DIR_PLUGIN_CISQUEL')){

				// trouver le message le plus ascendant
				$id_forum_top = $id_forum;
				$id_forum_top_1 = $id_forum;
				$id_fils = $id_forum;
				while ($parent = sql_getfetsel('id_parent', 'spip_forum', 'id_forum='.$id_fils)) {
					$id_forum_top_1 = $id_fils;
					$id_forum_top = $parent;
					$id_fils = $parent;
				}

				// commentaireautrepage
				if (sql_countsel('spip_articles', "id_article=".intval($id_article)." AND ciforme='_commentaireautrepage'")>0){
					$cipage = 'commentaire&id_article='.$id_article.'&id_forum='.$id_forum_top;

				// forum	
				} elseif (sql_countsel('spip_rubriques', "id_rubrique=".intval($id_rubrique)." AND ciforme='_forum'")>0){
					// trouver la pagination
					$pos_id_forum = 0;
					if ($id_forum_top) {
						$result = sql_select('id_forum', 'spip_forum', "id_parent=".intval($id_forum_top)." AND statut='publie'","","date_heure");
						while ($row = sql_fetch($result)){
							$pos_id_forum = $pos_id_forum + 1;
							if ($row['id_forum']==$id_forum)
								break;
						}
					}
					$debut_forum = 10 * floor($pos_id_forum/10);
					$cipage = 'sujet&id_article='.$id_article.'&id_forum='.$id_forum_top.'&debut_forum='.$debut_forum;

				// forumhierarchique	
				} elseif (sql_countsel('spip_rubriques', "id_rubrique=".intval($id_rubrique)." AND ciforme='_forumhierarchique'")>0){
					// trouver la pagination
					$pos_top_1 = 0;
					if ($id_forum_top_1) {
						$result = sql_select('id_forum', 'spip_forum', "id_parent=".intval($id_forum_top)." AND statut='publie'","","date_heure");
						while ($row = sql_fetch($result)){
							$pos_top_1 = $pos_top_1 + 1;
							if ($row['id_forum']==$id_forum_top_1)
								break;
						}
					}
					$debut_forum = 5 * floor($pos_top_1/5);
					$cipage = 'sujethierarchique&id_article='.$id_article.'&id_forum='.$id_forum_top.'&debut_forum='.$debut_forum;
				}
			}

			
			// compatibilite avec le plugin CIMS
			$tableau_multisite = cinotif_tableau_multisite_rubrique($id_rubrique);
			
			// lister les evenements
			$evenements = cinotif_tableau_evenements('forumvalide', 'site', '', $tableau_multisite);
			$evenements = array_merge($evenements, cinotif_tableau_evenements('forumvalide', 'rubrique', cinotif_ascendance($id_rubrique), $tableau_multisite));
			$evenements = array_merge($evenements, cinotif_tableau_evenements('forumvalide', 'article', $id_article, $tableau_multisite));
			
			if (defined('_DIR_PLUGIN_CISQUEL')) {
				// Multirubricage
				$rub_multirubricage = cinotif_multirubricage($id_article);
				if ($rub_multirubricage)
					$evenements = array_merge($evenements,cinotif_tableau_evenements('forumvalide', 'rubrique', $rub_multirubricage, $tableau_multisite));
			}
			
			// les abonnes de ces evenements
			$tableau_abonnes = cinotif_tableau_abonnes_evenement($evenements); 
			
			// enlever les destinataires de SPIP pour ne pas doublonner des envois
			// les emails des abonnes sont stockes en minuscules
			$tableau_id_destinataires = cinotif_id_abonnes('',$destinataires);
			$tableau_id_pasmoi = cinotif_id_abonnes('',$pasmoi);
			$tableau_abonnes = array_diff($tableau_abonnes,$tableau_id_destinataires,$tableau_id_pasmoi);

			// texte identique pour tous les abonnes
			$envoyer_mail = charger_fonction('envoyer_mail','inc'); // pour nettoyer_titre_email
			$texte = recuperer_fond("notifications/cinotif_forum_poste",array('id_article'=>$id_article,'id_forum'=>$id_forum,'cipage'=>$cipage));
		
			// envoyer aux destinataires au sens SPIP
			// cela permet de beneficier du texte et des URL specifiques
			// tout en evitant de mettre un lien de desabonnement pour les destinataires sans abonnement
			foreach ($destinataires as $email)
				notifications_envoyer_mails($email, $texte);

			// envoyer aux abonnes
			if ($tableau_abonnes AND $texte) {
				$notification = array('quoi'=>$quoi,
									'objet'=> 'forum',
									'id_objet'=> $id_forum,
									'parent'=> 'article',
									'id_parent'=> $id_article,
									'id_version'=> 0,
									'texte'=>$texte,
									'sujet'=>'');
				cinotif_notifier($tableau_abonnes,$notification,$evenements);
			}
			
		}
	}	
}


// Si forum public, SPIP utilise generer_url_entite qui indirectement utilise url_de_base
// Ceci pose probleme si l'URL pour l'espace prive est intranet et celle du site public internet, etc.
function cinotif_remplace_url_de_base($texte){
	$url_de_base = url_de_base();
	$ci_url_site = strtolower($GLOBALS['meta']['adresse_site']).'/';
	$texte = str_replace($url_de_base,$ci_url_site,$texte);

	return $texte;
}

?>