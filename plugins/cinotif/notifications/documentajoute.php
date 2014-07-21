<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/cinotif_commun');
 
/**
 * Cette notification s'execute lorsqu'un document est ajoute
 *
 * @param string $quoi
 * @param int $id_document
 * @param array $options
 */
function notifications_documentajoute($quoi, $id_document, $options) {
	$id_document = intval($id_document);
	$objet = '';
	$id_objet = 0;
	$id_article = 0;
	$id_rubrique = 0;

	// ajout d'un document
	if ($quoi=='documentajoute' AND $id_document>0){
		
		$row = sql_fetsel("mode", "spip_documents", "id_document=".$id_document);
		if ($row)
			$mode = $row['mode'];
		
		if ($mode=='document') {	
			$result = sql_select("id_objet,objet", "spip_documents_liens", "id_document=".$id_document);
			while ($row = sql_fetch($result)) {
				$parent = $row['objet'];
				$id_parent = $row['id_objet'];
			}
			
			// objet de rattachement du document
			if ($parent=='article') {
				$id_article = intval($id_parent);
				$row = sql_fetsel("id_rubrique,statut", "spip_articles", "id_article=".$id_article);
				if ($row) {
					$id_rubrique = $row['id_rubrique'];
					$statut = $row['statut'];
				}
			} elseif ($parent=='rubrique') {
				$id_rubrique = intval($id_parent);
				$row = sql_fetsel("statut", "spip_rubrique", "id_rubrique=".$id_rubrique);
				if ($row) {
					$statut = $row['statut'];
				}
			}

			if ($statut=='publie') {

				// compatibilite avec le plugin CIMS
				$tableau_multisite = cinotif_tableau_multisite_rubrique($id_rubrique);
				
				// lister les evenements
				$evenements = cinotif_tableau_evenements('documentajoute', 'site', '', $tableau_multisite);
				if ($id_rubrique)
					$evenements = array_merge($evenements, cinotif_tableau_evenements('documentajoute', 'rubrique', cinotif_ascendance($id_rubrique), $tableau_multisite));
	
				if ($id_article) {
					$evenements = array_merge($evenements, cinotif_tableau_evenements('documentajoute', 'article', $id_article, $tableau_multisite));
					
					if (defined('_DIR_PLUGIN_CISQUEL')) {
						// Multirubricage
						$rub_multirubricage = cinotif_multirubricage($id_article);
						if ($rub_multirubricage)
							$evenements = array_merge($evenements,cinotif_tableau_evenements('documentajoute', 'rubrique', $rub_multirubricage, $tableau_multisite));
					}
				}				
				
				// les abonnes de ces evenements
				$tableau_abonnes = cinotif_tableau_abonnes_evenement($evenements); 	

				// envoyer
				if ($tableau_abonnes) {
					$nomduquoi = "documentajoute"; 

					// cas d'un remplacement de document
					$id_version = 0;
					$where_version = "quoi=".sql_quote($quoi)." AND objet='document' AND id_objet=".$id_document;
					$versions = sql_fetsel('MAX(id_version) AS max','spip_cinotif_courriers',$where_version);
					if ($versions AND !is_null($versions['max'])){
						$id_version = $versions['max']+1;
						$nomduquoi = "documentremplace"; 
					}

					$envoyer_mail = charger_fonction('envoyer_mail','inc'); // pour nettoyer_titre_email
					if ($id_article)
						$texte = recuperer_fond("notifications/".$nomduquoi."_article",array('id_article'=>$id_article,'id_document'=>$id_document));
					elseif ($id_rubrique)
						$texte = recuperer_fond("notifications/".$nomduquoi."_rubrique",array('id_rubrique'=>$id_rubrique,'id_document'=>$id_document));
	

										
					$notification = array('quoi'=>$quoi,
										'objet'=> 'document',
										'id_objet'=> $id_document,
										'id_version'=> $id_version,
										'parent'=> $parent,
										'id_parent'=> $id_parent,
										'texte'=>$texte,
										'sujet'=>'');
					cinotif_notifier($tableau_abonnes,$notification,$evenements);
				}
			}
		}
	}
}

?>