<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/ciag_commun');


/**
 * Droit de modifier un auteur
 * Si un auteur est affecté à un groupe d’auteurs, qui est affecté à la rubrique A,
 * alors par dénormalisation, l’auteur est affecté directement à la rubrique A.
 * Aussi, dans les formulaires de SPIP de gestion des auteurs,
 * il ne doit pas être possible de retirer la rubrique A à cet auteur.
 * 
 * @return boolean
 */
function ciag_ciautoriser($param) {
	$faire = $param['faire'];
	$type = $param['type'];
	$id = $param['id'];
	$qui = $param['qui'];
	$opt = $param['opt'];
	$cifonction = $type.'_'.$faire;


	// Autoriser a modifier un auteur (ses affectations de rubriques)
	if ($cifonction=='auteur_modifier' AND isset($opt['restreint'])) {
		// avec l'operateur 'AND' mettre true par defaut
		$autoriser = true;
		
		if (!$id) {
			// mettre imperativement a true
			$autoriser = true;
		} else {
			$qui = $GLOBALS['visiteur_session'];

			// seul un administrateur du site peut modifier les rubriques affectees a l'auteur
			if (($qui['statut']=='0minirezo' AND !$qui['restreint']) OR autoriser('webmestre')) {
				
				// veut-on enlever des rubriques qui sont affectees a l'auteur via des groupes ?
				$rubriques_via_groupes = ciag_liste_rubriques_de_auteur_via_grpauteurs($id);
				if (count($rubriques_via_groupes)>=1) {
					$rubriques = $opt['restreint'];
					if (count($rubriques)>=1) {
						$in = sql_in('id_rubrique',$rubriques_via_groupes);
						$not_in = sql_in('id_rubrique',$rubriques, 'NOT');
						if (sql_countsel("spip_ciag_grpauteurs_rubriques",$in." AND ".$not_in)>=1)
							$autoriser = false;
					} else {
						$autoriser = false;
					}
				}
			}
		}

		// utilisation l'operateur 'AND' pour retrecir ce droit 
		$param['autorisations'][] = array('autoriser' => $autoriser, 'operateur' => 'AND');
	}
	
	return $param;	
}

?>