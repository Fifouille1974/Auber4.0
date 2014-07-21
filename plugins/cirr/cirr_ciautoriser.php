<?php
/**
 * Plugin redacteur restreint
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function cirr_ciautoriser($param) {
	$faire = $param['faire'];
	$type = $param['type'];
	$id = $param['id'];
	$qui = $param['qui'];
	$opt = $param['opt'];
	$cifonction = $type.'_'.$faire;


	// Autoriser a publier dans la rubrique
	if ($cifonction=='rubrique_publierdans') {
		// avec l'operateur 'AND' mettre true par defaut
		$autoriser = true;

		// les redacteurs restreints (et admin restreints)
		// ne doivent voir que leurs rubriques
		if (!$id) {
			// mettre imperativement true
			// sinon on n'a plus le bouton pour creer une rubrique, etc. 
			$autoriser = true;
		} else {
			if (in_array($qui['statut'], array('0minirezo', '1comite'))	
				&& ($qui['restreint'] AND $id AND !in_array($id, $qui['restreint'])))
					$autoriser = false;
		}
	
		// utilisation l'operateur 'AND' pour retrecir ce droit 
		$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');

	
	// Autoriser a voir la rubrique
	} elseif ($cifonction=='rubrique_voir') {
		// avec l'operateur 'AND' mettre true par defaut
		$autoriser = true;
		
		if (!$id) {
			// mettre imperativement true
			// sinon on n'a plus le bouton pour creer une rubrique, etc. 
			$autoriser = true;
		} else {
			// les redacteurs restreints (et admin restreints)
			// ne doivent voir que leurs rubriques
			if (in_array($qui['statut'], array('0minirezo', '1comite'))	
				&& ($qui['restreint'] AND $id AND !in_array($id, $qui['restreint'])))
					$autoriser = false;
		}

		// utilisation l'operateur 'AND' pour retrecir ce droit 
		$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');


	// Autoriser a voir l'article, la breve, le site reference
	} elseif (in_array($cifonction, array('article_voir','breve_voir','site_voir'))) {
		// avec l'operateur 'AND' mettre true par defaut
		$autoriser = true;
		
		if (!$id) {
			$autoriser = false;
		} else {
			// les redacteurs restreints (et admin restreints)
			// ne doivent voir que les articles de leurs rubriques
			if ($cifonction=='article_voir')
				$row = sql_fetsel('id_rubrique', 'spip_articles',"id_article=".intval($id),'','','');
			elseif ($cifonction=='breve_voir')
				$row = sql_fetsel('id_rubrique', 'spip_breves',"id_breve=".intval($id),'','','');
			elseif ($cifonction=='site_voir')
				$row = sql_fetsel('id_rubrique', 'spip_syndic',"id_syndic=".intval($id),'','','');

			$id_rubrique = $row['id_rubrique'];
			if (in_array($qui['statut'], array('0minirezo', '1comite'))	
				&& ($qui['restreint'] AND $id_rubrique AND !in_array($id_rubrique, $qui['restreint'])))
					$autoriser = false;
		}

		// utilisation l'operateur 'AND' pour retrecir ce droit 
		$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');
	}
	
	return $param;	
}

?>