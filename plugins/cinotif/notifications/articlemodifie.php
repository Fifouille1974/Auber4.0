<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/cinotif_commun');
 
/**
 * Cette notification s'execute lorsqu'un article est modifie
 *
 * @param string $quoi
 * @param int $id_article
 * @param array $options
 */
function notifications_articlemodifie($quoi, $id_article, $options) {
	$id_article = intval($id_article);

	
	// La modification d'article necessite que le suivi des revisions soit active
	if (spip_version()>=3) {
		$liste_objets_versionnees = is_array(unserialize($GLOBALS['meta']['objets_versions'])) ? unserialize($GLOBALS['meta']['objets_versions']) : array();
			if (!in_array('spip_articles',$liste_objets_versionnees))
				return true;
	} else {
		if  ($GLOBALS['meta']["articles_versions"] != 'oui')
			return true;
	}

	// modification d'un article
	if ($quoi=='articlemodifie' AND $id_article>0){

		$id_version = 0;
		$id_auteur = 0;
		$date = '';

		$row = sql_fetsel("id_rubrique,statut", "spip_articles", "id_article=".$id_article);
		if ($row){
			$id_rubrique = $row['id_rubrique'];
			$statut = $row['statut'];
		}

		// uniquement pour les articles publies
		if ($statut=='publie') {
			if (spip_version()>=3)
				$where = "objet='article' AND id_objet=$id_article AND id_version > 0";
			else
				$where = "id_article=$id_article AND id_version > 0";

			$row = sql_fetsel("*", "spip_versions", $where, "", "id_version DESC", "1");
			if ($row) {
				$id_version = $row['id_version'];
				$id_auteur = intval($row['id_auteur']);	// le champ id_auteur est un varchar dans cette table
				$champs = @unserialize($row['champs']);
			}
	
			// notifier uniquement si au moins le texte de l'article a ete modifie
			if (!isset($champs['texte']))
				return true;
		
	
			// compatibilite avec le plugin CIMS
			$tableau_multisite = cinotif_tableau_multisite_rubrique($id_rubrique);
				
			// lister les evenements
			$evenements = cinotif_tableau_evenements('articlemodifie', 'site', '', $tableau_multisite);
			if ($id_rubrique)
				$evenements = array_merge($evenements, cinotif_tableau_evenements('articlemodifie', 'rubrique', cinotif_ascendance($id_rubrique), $tableau_multisite));

			if ($id_article){
				$evenements = array_merge($evenements, cinotif_tableau_evenements('articlemodifie', 'article', $id_article, $tableau_multisite));
			
				if (defined('_DIR_PLUGIN_CISQUEL')) {
					// Multirubricage
					$rub_multirubricage = cinotif_multirubricage($id_article);
					if ($rub_multirubricage)
						$evenements = array_merge($evenements,cinotif_tableau_evenements('articlemodifie', 'rubrique', $rub_multirubricage, $tableau_multisite));
				}
			}

			// les abonnes de ces evenements
			$tableau_abonnes = cinotif_tableau_abonnes_evenement($evenements); 
			
						
			$envoyer_mail = charger_fonction('envoyer_mail','inc'); // pour nettoyer_titre_email
			$texte = recuperer_fond("notifications/articlemodifie",array('id_article'=>$id_article,'id_version'=>$id_version,'id_auteur'=>$id_auteur));

			// envoyer
			if ($tableau_abonnes AND $texte) {
				$notification = array('quoi'=>'articlemodifie',
									'objet'=> 'article',
									'id_objet'=> $id_article,
									'id_version'=> $id_version,
									'parent'=> 'rubrique',
									'id_parent'=> $id_rubrique,
									'texte'=>$texte,
									'sujet'=>'');
				cinotif_notifier($tableau_abonnes,$notification,$evenements);
			}
		}
	}	
}


?>