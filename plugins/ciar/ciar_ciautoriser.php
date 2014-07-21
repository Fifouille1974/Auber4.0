<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('ciar_fonctions');    	

/**
 * Droit de voir une rubrique dans l'espace prive
 * L'auteur n'a pas le droit de voir les rubriques EC sauf celles auxquelles il est affecte
 * Si l'auteur est restreint à des rubriques, il n'a pas le droit de voir les autres rubriques
 * 
 * @return boolean
 */
function ciar_ciautoriser($param) {
	$faire = $param['faire'];
	$type = $param['type'];
	$id = $param['id'];
	$qui = $param['qui'];
	$opt = $param['opt'];
	$cifonction = $type.'_'.$faire;


	// Autoriser a voir la rubrique
	if ($cifonction=='rubrique_voir') {
		// avec l'operateur 'AND' mettre true par defaut
		$autoriser = true;
		
		if (!$id) {
			// mettre imperativement a true
			$autoriser = true;	
		} else {
			$autoriser = !ciar_rubrique_interdite($id);

			// cas du selecteur de rubrique dans la creation d'article
			// lorsque le statut est visiteur (par surcharge)
			// et compatibilite avec le plugin cisf
			if ($autoriser) {
				if (_request('exec')=='selectionner' OR _request('page')=='cisf_rubart'){
					$statut_surcharge = ciar_auteur_ec_statut_normalise($id);
					if ($statut_surcharge AND $statut_surcharge!=$qui['statut']) {
						if ($statut_surcharge=='6forum')
							$autoriser = false;
					}
				}
			}
		}

		// utilisation l'operateur 'AND' pour retrecir ce droit 
		$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');
		
		
	// Autoriser a modifier la rubrique
	} elseif ($cifonction=='rubrique_modifier') {
		
		if ($id) {
			// avec l'operateur 'AND' mettre true par defaut
			$autoriser = true;
		
			$autoriser = !ciar_rubrique_interdite($id);
			$statut_surcharge = ciar_auteur_ec_statut_normalise($id);
			$qui_surcharge = "";
			if ($statut_surcharge AND $statut_surcharge!=$qui['statut']) {
				$qui_surcharge = $qui;
				$qui_surcharge['statut'] = $statut_surcharge;
				if ($statut_surcharge=='6forum')
					$autoriser_surcharge = false;
				else
					$autoriser_surcharge = autoriser_rubrique_modifier($faire, $type, $id, $qui_surcharge, $opt);
			}
			
			// surcharge de statut dans un EC (pour retrecir le droit, le cas echeant)
			if ($qui_surcharge)
				$autoriser = $autoriser_surcharge;

			// utilisation l'operateur 'AND' pour retrecir ce droit 
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');

		
			// Cas de surcharge de statut dans un EC (pour elargir le droit, le cas echeant)
			// ou d'un auteur qui a le droit de gerer une rubrique espace collaboratif
			// avec l'operateur 'OR' mettre false par defaut
			$autoriser = false;
		
			// surcharge de statut dans un EC (pour elargir le droit, le cas echeant)
			if ($qui_surcharge)
				$autoriser = $autoriser_surcharge;
				
			// Cas d'un auteur qui a le droit de gerer une rubrique espace collaboratif
			if ($statut_surcharge=='0minirezo')    	
				$autoriser = true;

			// utilisation l'operateur 'OR' pour elargir ce droit 
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
		}


	// Autoriser a voir l'article, la breve, le site reference, le document
	} elseif (in_array($cifonction, array('article_voir','breve_voir','site_voir','document_voir'))) {
		// avec l'operateur 'AND' mettre true par defaut
		$autoriser = true;

		if (!$id) {
			$autoriser = false;
			
			// subtilite de SPIP dans generer_url_document_dist
			if ($cifonction=='document_voir')
				$autoriser = true;

		} else {
			if ($cifonction=='document_voir') {
				$autoriser = ciar_autoriser_document(intval($id));

			} else {
				if ($cifonction=='article_voir')
					$row = sql_fetsel('id_rubrique', 'spip_articles',"id_article=".intval($id),'','','');
				elseif ($cifonction=='breve_voir')
					$row = sql_fetsel('id_rubrique', 'spip_breves',"id_breve=".intval($id),'','','');
				elseif ($cifonction=='site_voir')
					$row = sql_fetsel('id_rubrique', 'spip_syndic',"id_syndic=".intval($id),'','','');
	
				$id_rubrique = $row['id_rubrique'];
				$autoriser = !ciar_rubrique_interdite($id_rubrique);
			}
		}

	
		// utilisation l'operateur 'AND' pour retrecir ce droit 
		$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');


	// Autoriser a modifier l'article, la breve, le site reference
	} elseif (in_array($cifonction, array('article_modifier','breve_modifier','site_modifier'))) {
		// avec l'operateur 'AND' mettre true par defaut
		$autoriser = true;
	
		if ($id) {
			if ($cifonction=='article_modifier')
				$row = sql_fetsel('id_rubrique', 'spip_articles',"id_article=".intval($id),'','','');
			elseif ($cifonction=='breve_modifier')
				$row = sql_fetsel('id_rubrique', 'spip_breves',"id_breve=".intval($id),'','','');
			elseif ($cifonction=='site_modifier')
				$row = sql_fetsel('id_rubrique', 'spip_syndic',"id_syndic=".intval($id),'','','');

			$id_rubrique = $row['id_rubrique'];
			$autoriser = !ciar_rubrique_interdite($id_rubrique);
			
			$statut_surcharge = ciar_auteur_ec_statut_normalise($id_rubrique);
			$qui_surcharge = "";
			if ($statut_surcharge AND $statut_surcharge!=$qui['statut']) {
				$qui_surcharge = $qui;
				$qui_surcharge['statut'] = $statut_surcharge;
				$ci_fonction = "autoriser_".$cifonction;
				if ($statut_surcharge=='6forum')
					$autoriser_surcharge = false;
				else
					$autoriser_surcharge = $ci_fonction($faire, $type, $id, $qui_surcharge, $opt);
			}
	
			// surcharge de statut dans un EC (pour retrecir le droit, le cas echeant)
			if ($qui_surcharge)
				$autoriser = $autoriser_surcharge;
			
			// utilisation l'operateur 'AND' pour retrecir ce droit 
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');
	
			
			// Cas de surcharge de statut dans un EC (pour elargir le droit, le cas echeant)
			// ou d'un auteur qui a le droit de gerer une rubrique espace collaboratif
			// avec l'operateur 'OR' mettre false par defaut
			$autoriser = false;
				
			// surcharge de statut dans un EC (pour elargir le droit, le cas echeant)
			if ($qui_surcharge)
				$autoriser = $autoriser_surcharge;
			
			// utilisation l'operateur 'OR' pour elargir ce droit 
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
		}
		
		
	// Autoriser a publier dans la rubrique
	} elseif ($cifonction=='rubrique_publierdans') {

		if (!$id) {
			// empecher de voir les commentaires de tous les forums
			// car  ils comprennent ceux des EC
			// Page 'controle_forum' dans l'espace prive
/*			
			if (_request('exec')=='controle_forum')	{
				$autoriser = false;
			
				// utilisation l'operateur 'AND' pour retrecir ce droit 
				$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');
			}
*/
		}
		
		
		if ($id) {
			
			// avec l'operateur 'AND' mettre true par defaut
			$autoriser = true;
			
			$autoriser = !ciar_rubrique_interdite($id);
			
			$statut_surcharge = ciar_auteur_ec_statut_normalise($id);
			$qui_surcharge = "";
			if ($statut_surcharge AND $statut_surcharge!=$qui['statut']) {
				$qui_surcharge = $qui;
				$qui_surcharge['statut'] = $statut_surcharge;
				if ($statut_surcharge=='6forum')
					$autoriser_surcharge = false;
				else
					$autoriser_surcharge = autoriser_rubrique_publierdans($faire, $type, $id, $qui_surcharge, $opt);
			}
			
			// surcharge de statut dans un EC (pour retrecir le droit, le cas echeant)
			if ($qui_surcharge)
				$autoriser = $autoriser_surcharge;
			
			// utilisation l'operateur 'AND' pour retrecir ce droit 
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');
	

			// Cas d'un auteur qui a le droit de gerer une rubrique espace collaboratif
			// avec l'operateur 'OR' mettre false par defaut
			$autoriser = false;

			// surcharge de statut dans un EC (pour elargir le droit, le cas echeant)
			if ($qui_surcharge)
				$autoriser = $autoriser_surcharge;

			// Cas d'un auteur qui a le droit de gerer une rubrique espace collaboratif
			if ($statut_surcharge=='0minirezo')
				$autoriser = true;

			// utilisation l'operateur 'OR' pour elargir ce droit 
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'OR');
		}
		
	// Autoriser a creer un article dans la rubrique
	} elseif ($cifonction=='rubrique_creerarticledans') {
		
		if ($id) {
			// avec l'operateur 'AND' mettre true par defaut
			$autoriser = true;

			// ajout
			$autoriser = !ciar_rubrique_interdite($id);
			
			$statut_surcharge = ciar_auteur_ec_statut_normalise($id);
			$qui_surcharge = "";
			if ($statut_surcharge AND $statut_surcharge!=$qui['statut']) {
				$qui_surcharge = $qui;
				$qui_surcharge['statut'] = $statut_surcharge;
				if ($statut_surcharge=='6forum')
					$autoriser_surcharge = false;
				else
					$autoriser_surcharge = autoriser_rubrique_creerarticledans($faire, $type, $id, $qui_surcharge, $opt);
			}
			
			// surcharge de statut dans un EC (pour retrecir le droit, le cas echeant)
			if ($qui_surcharge)
				$autoriser = $autoriser_surcharge;

			// utilisation l'operateur 'AND' pour retrecir ce droit 
			$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');
		}
		
	}
	
	return $param;	
}

?>