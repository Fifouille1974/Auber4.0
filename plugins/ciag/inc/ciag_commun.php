<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

 
function ciag_liste_grpauteurs_de_auteur($id_auteur) {
	$grpauteurs = array();

	if ($id_auteur = intval($id_auteur)) {
		$result = sql_select("id_groupe","spip_ciag_grpauteurs_auteurs","id_auteur=".$id_auteur,"","id_groupe");
		while ($row = sql_fetch($result))
			$grpauteurs[] = $row['id_groupe'];
	}
	return $grpauteurs;
}
 

function ciag_liste_grpauteurs_de_rubrique($id_rubrique) {
	$grpauteurs = array();

	if ($id_rubrique = intval($id_rubrique)) {
		$result = sql_select("id_groupe","spip_ciag_grpauteurs_rubriques","id_rubrique=".$id_rubrique,"","id_groupe");
		while ($row = sql_fetch($result))
			$grpauteurs[] = $row['id_groupe'];
	}
	return $grpauteurs;
}
 

function ciag_liste_rubriques_de_auteur($id_auteur) {
	$rubriques_auteur = array();

	if ($id_auteur = intval($id_auteur)) {
		if (spip_version()>=3)
			$result = sql_select("id_objet AS id_rubrique", "spip_auteurs_liens", "objet='rubrique' AND id_auteur=$id_auteur","","id_rubrique");
		else
			$result = sql_select("id_rubrique", "spip_auteurs_rubriques", "id_auteur=$id_auteur","","id_rubrique");

		while ($row = sql_fetch($result))
			$rubriques_auteur[] = $row['id_rubrique'];
	}
	return $rubriques_auteur;
}


function ciag_liste_rubriques_de_auteur_via_grpauteurs($id_auteur) {
 	$rubriques = array();
	
	if ($id_auteur = intval($id_auteur)) {
		
		// l'auteur est-il dans des groupes d'auteurs ?
		$result = sql_select("id_groupe","spip_ciag_grpauteurs_auteurs","id_auteur=".$id_auteur,"","");
		while ($row = sql_fetch($result))
			$grpauteurs[] = $row['id_groupe'];
		
		// Rubriques de ses groupes d'auteurs ?
		if (count($grpauteurs)>=1) {
			$in = sql_in('id_groupe',$grpauteurs);
			$result = sql_select("id_rubrique","spip_ciag_grpauteurs_rubriques",$in,"","id_rubrique");
			while ($row = sql_fetch($result))
				$rubriques[] = $row['id_rubrique'];
		}
	}
	
	// eviter les doublons
	return array_unique($rubriques);
} 


function ciag_liste_rubriques_des_grpauteurs($grpauteurs) {
 	$rubriques = array();
	
	if ($grpauteurs) {
		if (is_array($grpauteurs)) {
			if (count($grpauteurs)>=1) {
				$in = sql_in('id_groupe',$grpauteurs);
				// Rubriques des groupes d'auteurs ?
				$result = sql_select("id_rubrique","spip_ciag_grpauteurs_rubriques",$in,"","id_rubrique");
				while ($row = sql_fetch($result))
					$rubriques[] = $row['id_rubrique'];					
			}
		}
	}

	// eviter les doublons
	return array_unique($rubriques);
} 


function ciag_liste_rubriques_de_grpauteurs($id_groupe) {
 	$rubriques = array();
	
	if ($id_groupe = intval($id_groupe)) {
		// Rubriques du groupe d'auteurs ?
		$result = sql_select("id_rubrique","spip_ciag_grpauteurs_rubriques","id_groupe=".$id_groupe,"","id_rubrique");
		while ($row = sql_fetch($result))
			$rubriques[] = $row['id_rubrique'];
	}
	return $rubriques;
} 


function ciag_liste_auteurs_des_grpauteurs($grpauteurs) {
 	$auteurs = array();
	
	if ($grpauteurs) {
		if (is_array($grpauteurs)) {
			if (count($grpauteurs)>=1) {
				$in = sql_in('id_groupe',$grpauteurs);
				// auteurs des groupes d'auteurs ?
				$result = sql_select("id_auteur","spip_ciag_grpauteurs_auteurs",$in,"","id_auteur");
				while ($row = sql_fetch($result))
					$auteurs[] = $row['id_auteur'];					
			}
		}
	}

	// eviter les doublons
	return array_unique($auteurs);
} 

function ciag_liste_gestionnaires_de_grpauteurs($id_groupe,$pagination=0,$debut_auteur=0) {
 	$auteurs = array();
	
	// gestionnaires du groupe d'auteurs ?
	if ($id_groupe = intval($id_groupe)) {
		$in = '';
		// si pagination
		if ($pagination>0) {
			// limiter aux statuts qui correspondent
			$where = "statut='0minirezo'";
			if (defined('_DIR_PLUGIN_CIAR'))
				$where = "statut IN ('0minirezo','1comite')";

			// quels sont les id_auteur de cette tranche de pagination ?
			$auteurs_tranche = ciag_liste_auteurs_tranche($pagination,$debut_auteur,$where);
			$in = ' AND '.sql_in('id_auteur',$auteurs_tranche);
		}				
		$result = sql_select("id_auteur","spip_ciag_grpauteurs_gestionnaires","id_groupe=".$id_groupe.$in,"","id_auteur");
		while ($row = sql_fetch($result))
			$auteurs[] = $row['id_auteur'];					
	}
	return $auteurs;
} 

function ciag_liste_auteurs_tranche($pagination=0,$debut=0,$where='') {
	
	$fin = $debut + $pagination;
	$compteur = 0;
	$auteurs_ok = array();
		
	// tri par nom
	$result = sql_select("id_auteur","spip_auteurs",$where,"","nom");
	while ($row = sql_fetch($result)){
		if ($compteur>=$fin)
			break;
		if ($compteur>=$debut)
			$auteurs_ok[] = $row['id_auteur'];
			
		$compteur++;
	}
	return $auteurs_ok;
}

function ciag_liste_auteurs_de_grpauteurs($id_groupe,$pagination=0,$debut_auteur=0) {
 	$auteurs = array();
	
	// auteurs du groupe d'auteurs ?
	if ($id_groupe = intval($id_groupe)) {
		$in = '';
		// si pagination
		if ($pagination>0) {
			// limiter aux statuts qui correspondent
			$where = "statut='0minirezo'";
			if (defined('_DIR_PLUGIN_CIAR'))
				$where = "statut IN ('0minirezo','1comite','6forum')";
			elseif (defined('_DIR_PLUGIN_CIRR'))
				$where = "statut IN ('0minirezo','1comite')";
			
			// quels sont les id_auteur de cette tranche de pagination ?
			$auteurs_tranche = ciag_liste_auteurs_tranche($pagination,$debut_auteur,$where);
			$in = ' AND '.sql_in('id_auteur',$auteurs_tranche);
		}				
		$result = sql_select("id_auteur","spip_ciag_grpauteurs_auteurs","id_groupe=".$id_groupe.$in,"","id_auteur");
		while ($row = sql_fetch($result))
			$auteurs[] = $row['id_auteur'];
	}
	return $auteurs;
} 

function ciag_liste_auteurs_restreints_de_grpauteurs($id_groupe) {
	$auteurs_retreints = array();
	$auteurs = ciag_liste_auteurs_de_grpauteurs($id_groupe);
	
	if ($auteurs) {
		$in = sql_in('id_auteur',$auteurs);

		if (spip_version()>=3)
			$result = sql_select("id_auteur", "spip_auteurs_liens", "objet='rubrique' AND ".$in,"id_auteur","");
		else
			$result = sql_select("id_auteur","spip_auteurs_rubriques",$in,"id_auteur","");
		
		while ($row = sql_fetch($result))
			$auteurs_retreints[] = $row['id_auteur'];
	}
	return $auteurs_retreints;
}


function ciag_auteur_restreint($id_auteur) {
	$return = false;
	
	if ($id_auteur = intval($id_auteur)) {
		if (spip_version()>=3) {
			if (sql_countsel("spip_auteurs_liens", "objet='rubrique' AND id_auteur=".$id_auteur)>=1)
				$return = true;
		} else {
			if (sql_countsel("spip_auteurs_rubriques", "id_auteur=".$id_auteur)>=1)
				$return = true;
		}
	}
	return $return;
}


function ciag_3_auteur_lien_messagerie($id_auteur,$en_ligne,$statut,$imessage,$email=''){
	static $time = null;
	if (!in_array($statut, array('0minirezo', '1comite')))
		return '';

	if (is_null($time))
		$time = time();
	$parti = (($time-strtotime($en_ligne))>15*60);

	if ($imessage != 'non' AND !$parti AND $GLOBALS['meta']['messagerie_agenda'] != 'non')
		return generer_action_auteur("editer_message","normal/$id_auteur");

	elseif (strlen($email) AND autoriser('voir', 'auteur', $id_auteur))
		return 'mailto:' . $email;

	else
		return '';
}


function ciag_formater_auteur_mail($id_auteur,$statut,$imessage,$email='') {
	global $connect_id_auteur;
	
	if (!in_array($statut, array('0minirezo', '1comite')))
		return '';

	if (($id_auteur == $connect_id_auteur))
		return '';
		
	if ($imessage != 'non'
	AND $GLOBALS['meta']['messagerie_agenda'] != 'non')
		$href = generer_action_auteur("editer_message","normal/$id_auteur");
	else if (strlen($email)
	AND autoriser('voir', 'auteur', $id_auteur))
		$href = 'mailto:' . $email;
	else	return '';

	return "<a href='$href' title=\""
	  .  _T('info_envoyer_message_prive')
	  . "\" class='message'>&nbsp;</a>";
}


function ciag_puce_statut($statut) {
	$puce_statut = charger_fonction('puce_statut', 'inc');
	return $puce_statut(0, $statut, 0, 'auteur');
}


// Modifier la liste des gestionnaires du groupe d'auteurs
function ciag_modifier_gestionnaires_dans_grpauteurs($auteurs,$id_groupe,$ctrl_concurrent=false,$image_avant='',$pas=0,$debut_auteur=0) {
	$return = true;
	$id_groupe = intval($id_groupe);
	$auteurs = ciag_securise($auteurs,"array_intval");

	
	if ($id_groupe>0) {
		$insertauteurs = array();
		$deleteauteurs = array();
		$oldauteurs = array();
					
		// gestionnaires de ce groupe memorises dans la base
		$oldauteurs = ciag_liste_gestionnaires_de_grpauteurs($id_groupe,$pas,$debut_auteur);

		// Prévention des accès concurents
		if ($ctrl_concurrent)
			$concurrent = ciag_acces_concurrents($image_avant,$oldauteurs);
		else
			$concurrent = false;
	
		if (!$concurrent) {
			// limiter aux statuts qui correspondent a la situation 
			$auteurs_ok = array();
			$in = sql_in('id_auteur',$auteurs);
			$where = "statut='0minirezo'";
			if (defined('_DIR_PLUGIN_CIAR'))
				$where = "statut IN ('0minirezo','1comite')";

			$result = sql_select("id_auteur","spip_auteurs",$where." AND ".$in,"","id_auteur");
			while ($row = sql_fetch($result))
				$auteurs_ok[] = $row['id_auteur'];
	
			$insertauteurs = ciag_a_inserer($auteurs_ok,$oldauteurs);
			$deleteauteurs = ciag_a_supprimer($auteurs_ok,$oldauteurs,true);
						
			if ($insertauteurs) {
				$tab_couples = array();
				$n = 1;
				$max = ciag_nb_max_insert();

				foreach ($insertauteurs as $insertauteur) {
					$n++;
					$tab_couples[] = array('id_auteur' => $insertauteur, 'id_groupe' => $id_groupe);
					if ($n>$max) {
						// Inserer par groupes de 50 maximun (ou moins)
						sql_insertq_multi('spip_ciag_grpauteurs_gestionnaires',$tab_couples);
						$tab_couples = array();
						$n = 1;
					}
				}
				// Inserer le reste
				if ($tab_couples)
					sql_insertq_multi('spip_ciag_grpauteurs_gestionnaires',$tab_couples);
				
			}
			
			
			if ($deleteauteurs) {
				$in = sql_in('id_auteur',$deleteauteurs);
				// on ne retire pas celui qui manipule (prévention)
				$id_exclu = $GLOBALS['visiteur_session']['id_auteur'];					
				sql_delete("spip_ciag_grpauteurs_gestionnaires", $in." AND id_auteur!=$id_exclu AND id_groupe=$id_groupe");
			}
			
		} else {
			$return = false;
		}			
		 
	}
	return $return;
}


// Modifier la liste des auteurs du groupe d'auteurs
function ciag_modifier_auteurs_dans_grpauteurs($auteurs,$id_groupe,$ctrl_concurrent=false,$image_avant='',$pas=0,$debut_auteur=0) {
	$return = true;
	$id_groupe = intval($id_groupe);
	$auteurs = ciag_securise($auteurs,"array_intval");
	
	if ($id_groupe>0) {
		$insertauteurs = array();
		$deleteauteurs = array();
		$oldauteurs = array();
					
		// auteurs de ce groupe memorises dans la base
		$oldauteurs = ciag_liste_auteurs_de_grpauteurs($id_groupe,$pas,$debut_auteur);
		
		// Prévention des accès concurents
		if ($ctrl_concurrent)
			$concurrent = ciag_acces_concurrents($image_avant,$oldauteurs);
		else
			$concurrent = false;


		if (!$concurrent) {
			// limiter aux statuts qui correspondent a la situation 
			$auteurs_ok = array();
			$in = sql_in('id_auteur',$auteurs);
			$where = "statut='0minirezo'";
			if (defined('_DIR_PLUGIN_CIAR'))
				$where = "statut!='5poubelle'";
			elseif (defined('_DIR_PLUGIN_CIRR'))	
				$where = "statut IN ('0minirezo','1comite')";

			$result = sql_select("id_auteur","spip_auteurs",$where." AND ".$in,"","id_auteur");
			while ($row = sql_fetch($result))
				$auteurs_ok[] = $row['id_auteur'];
			
	
			$insertauteurs = ciag_a_inserer($auteurs_ok,$oldauteurs);
			$deleteauteurs = ciag_a_supprimer($auteurs_ok,$oldauteurs);

			if ($insertauteurs) {
				$tab_couples = array();
				$n = 1;
				$max = ciag_nb_max_insert();

				foreach ($insertauteurs as $insertauteur) {
					$n++;
					$tab_couples[] = array('id_auteur' => $insertauteur, 'id_groupe' => $id_groupe);
					if ($n>$max) {
						// Inserer par groupes de 50 maximun (ou moins)
						sql_insertq_multi('spip_ciag_grpauteurs_auteurs',$tab_couples);
						$tab_couples = array();
						$n = 1;
					}
				}
				// Inserer le reste
				if ($tab_couples)
					sql_insertq_multi('spip_ciag_grpauteurs_auteurs',$tab_couples);
				
				ciag_denormaliser_ajout_auteurs($insertauteurs,$id_groupe);
			}
			
			
			if ($deleteauteurs) {
				$in = sql_in('id_auteur',$deleteauteurs);
				sql_delete("spip_ciag_grpauteurs_auteurs", $in." AND id_groupe=$id_groupe");
				ciag_denormaliser_retrait_auteurs($deleteauteurs,$id_groupe);
			}
			
		} else {
			$return = false;
		}			
		 
	}
	return $return;
}


function ciag_denormaliser_ajout_auteurs($auteurs,$id_groupe) {

	if ($id_groupe = intval($id_groupe)) {
		$rubriques = ciag_liste_rubriques_de_grpauteurs($id_groupe);

		if ($auteurs) {
			if (is_array($auteurs)) {						
				if (defined('_DIR_PLUGIN_CIAR')){
					include_spip('inc/ciar_commun');
					$rubriques_ec = ciar_ec_direct_parmi_rubriques($rubriques);
					foreach ($rubriques_ec as $id_rubrique_ec)
						ciar_ajouter_auteurs_dans_ec($auteurs,$id_rubrique_ec,true);

					$rubriques_hors_ec = array_diff($rubriques,$rubriques_ec);
					foreach ($auteurs as $id_auteur)
						$insert_auteurs = ciag_ajouter_rubriques_a_auteur($rubriques_hors_ec,$id_auteur);

				} else {
					foreach ($auteurs as $id_auteur)
						$insert_auteurs = ciag_ajouter_rubriques_a_auteur($rubriques,$id_auteur);
				}
			}
		}
	}
	return true;
}			


function ciag_denormaliser_retrait_auteurs($auteurs,$id_groupe) {

	if ($id_groupe = intval($id_groupe)) {
		$rubriques = ciag_liste_rubriques_de_grpauteurs($id_groupe);

		if (defined('_DIR_PLUGIN_CIAR')){
			include_spip('inc/ciar_commun');
			$rubriques_ec = ciar_ec_direct_parmi_rubriques($rubriques);
		}
		
		if ($auteurs) {
			if (is_array($auteurs)) {		
				foreach ($auteurs as $id_auteur) {
					// ne rien faire si l'auteur est lié à la rubrique via un autre groupe
					$rubriques_auteur = ciag_liste_rubriques_de_auteur_via_grpauteurs($id_auteur);

					// retirer les rubriques du groupe a chaque auteur
					$suppr = array();					
					foreach ($rubriques as $id_rubrique) {
						if ($id_rubrique = intval($id_rubrique)) {
							if (!in_array($id_rubrique,$rubriques_auteur))
								$suppr[] = $id_rubrique;
						}
					}
					if ($suppr) {
						ciag_retirer_rubriques_a_auteur($suppr,$id_auteur);

						if (defined('_DIR_PLUGIN_CIAR')){
							$suppr_ec = array_intersect($suppr,$rubriques_ec);
							if ($suppr_ec) {
								foreach ($suppr_ec as $id_rubrique_ec)
									ciar_retirer_auteurs_dans_ec(array($id_auteur),$id_rubrique_ec,false);
							}
						}
					}
				}
			}
		}
	}
	return true;
}			


// Modifier la liste des rubriques du groupe d'auteurs
function ciag_modifier_rubriques_dans_grpauteurs($rubriques,$id_groupe,$ctrl_concurrent=false,$image_avant='') {
	$return = true;
	$id_groupe = intval($id_groupe);
	$rubriques = ciag_securise($rubriques,"array_intval");


	if ($id_groupe>0) {
		$insertrubriques = array();
		$deleterubriques = array();
		$oldrubriques = array();
					
		// rubriques de ce groupe memorises dans la base
		$oldrubriques = ciag_liste_rubriques_de_grpauteurs($id_groupe);
		
		// Prévention des accès concurents
		if ($ctrl_concurrent)
			$concurrent = ciag_acces_concurrents($image_avant,$oldrubriques);
		else
			$concurrent = false;
			
	
		if (!$concurrent) {
	
			$insertrubriques = ciag_a_inserer($rubriques,$oldrubriques);
			$deleterubriques = ciag_a_supprimer($rubriques,$oldrubriques);
			
			// ne pas ajouter une rubrique et sa fille, ...
			$insertrubriques = ciag_filtrer_enfants($insertrubriques);
			
			if ($insertrubriques) {
				$interdit = array();
				
				// si rubrique EC, verifier droit de gerer cet EC					
				if (defined('_DIR_PLUGIN_CIAR')){
					include_spip('ciar_fonctions');
					$rubriques_ec_et_descendance = ciar_tableau_rub_ec();
					$insert_ec = array_intersect($insertrubriques,$rubriques_ec_et_descendance);
					foreach ($insert_ec as $id_ec) {
						if (!autoriser('ecmodifier','rubrique',$id_ec))
							$interdit[] = $id_ec;
					}
				}
				
				$tab_couples = array();
				$n = 1;
				$max = ciag_nb_max_insert();

				foreach ($insertrubriques as $insertrubrique) {
					if (!in_array($insertrubrique,$interdit)) {
						if (autoriser('voir','rubrique',$insertrubrique)){
							$n++;
							$tab_couples[] = array('id_rubrique' => $insertrubrique, 'id_groupe' => $id_groupe);
							if ($n>$max) {
								// Inserer par groupes de 50 maximun (ou moins)
								sql_insertq_multi('spip_ciag_grpauteurs_rubriques',$tab_couples);
								$tab_couples = array();
								$n = 1;
							}
						}
					}
				}
				// Inserer le reste
				if ($tab_couples)
					sql_insertq_multi('spip_ciag_grpauteurs_rubriques',$tab_couples);
				
				ciag_denormaliser_ajout_rubriques($insertrubriques,$id_groupe);
			}
			
			
			// si on vient d'ajouter une rubrique parente d'une rubrique deja affectee au groupe
			$tab = ciag_liste_rubriques_de_grpauteurs($id_groupe);
			$delete_filles = array_diff($tab,ciag_filtrer_enfants($tab));
			if ($delete_filles)
				$deleterubriques = array_merge($deleterubriques,$delete_filles);
			
			if ($deleterubriques) {
				$in = sql_in('id_rubrique',$deleterubriques);
				sql_delete("spip_ciag_grpauteurs_rubriques", $in." AND id_groupe=$id_groupe");
				ciag_denormaliser_retrait_rubriques($deleterubriques,$id_groupe);
			}
			
		} else {
			$return = false;
		}			
		 
	}
	return $return;
}


function ciag_denormaliser_ajout_rubriques($rubriques,$id_groupe) {

	if ($id_groupe = intval($id_groupe)) {
		$auteurs = ciag_liste_auteurs_de_grpauteurs($id_groupe);

		if ($auteurs) {
			if (is_array($auteurs)) {				
				if (defined('_DIR_PLUGIN_CIAR')){
					include_spip('inc/ciar_commun');
					$rubriques_ec = ciar_ec_direct_parmi_rubriques($rubriques);
					foreach ($rubriques_ec as $id_rubrique_ec)
						ciar_ajouter_auteurs_dans_ec($auteurs,$id_rubrique_ec,true);

					$rubriques_hors_ec = array_diff($rubriques,$rubriques_ec);
					foreach ($auteurs as $id_auteur)
						$insert_auteurs = ciag_ajouter_rubriques_a_auteur($rubriques_hors_ec,$id_auteur);

				} else {
					foreach ($auteurs as $id_auteur)
						$insert_auteurs = ciag_ajouter_rubriques_a_auteur($rubriques,$id_auteur);
				}
			}			
		}
	}
	return true;
}			


function ciag_denormaliser_retrait_rubriques($rubriques,$id_groupe) {

	if ($id_groupe = intval($id_groupe)) {
		$auteurs = ciag_liste_auteurs_de_grpauteurs($id_groupe);

		if (defined('_DIR_PLUGIN_CIAR')){
			include_spip('inc/ciar_commun');
			$rubriques_ec = ciar_ec_direct_parmi_rubriques($rubriques);
		}
		
		if ($auteurs) {
			if (is_array($auteurs)) {		
				foreach ($auteurs as $id_auteur) {
					// ne rien faire si l'auteur est lié à la rubrique via un autre groupe
					$rubriques_auteur = ciag_liste_rubriques_de_auteur_via_grpauteurs($id_auteur);

					// retirer les rubriques a chaque auteur
					$suppr = array();					
					foreach ($rubriques as $id_rubrique) {
						if ($id_rubrique = intval($id_rubrique)) {
							if (!in_array($id_rubrique,$rubriques_auteur))
								$suppr[] = $id_rubrique;
						}
					}
					if ($suppr) {
						ciag_retirer_rubriques_a_auteur($suppr,$id_auteur);
						
						if (defined('_DIR_PLUGIN_CIAR')){
							$suppr_ec = array_intersect($suppr,$rubriques_ec);
							if ($suppr_ec) {
								foreach ($suppr_ec as $id_rubrique_ec)
									ciar_retirer_auteurs_dans_ec(array($id_auteur),$id_rubrique_ec,false);
							}
						}
					}
					
				}
			}
		}
	}
	return true;
}


// Modifier la liste des groupes d'auteurs de l'auteur
function ciag_modifier_grpauteurs_de_auteur($grpauteurs,$id_auteur,$ctrl_concurrent=false,$image_avant='') {
	$return = true;
	$id_auteur = intval($id_auteur);
	$grpauteurs = ciag_securise($grpauteurs,"array_intval");
	
	
	if ($id_auteur>0) {
		$insertgrpauteurs = array();
		$deletegrpauteurs = array();
		$oldgrpauteurs = array();
					
		// groupes de cet auteur memorises dans la base
		$oldgrpauteurs = ciag_liste_grpauteurs_de_auteur($id_auteur);

		// Prévention des accès concurents
		if ($ctrl_concurrent)
			$concurrent = ciag_acces_concurrents($image_avant,$oldgrpauteurs);
		else
			$concurrent = false;

	
		if (!$concurrent) {
	
			$insertgrpauteurs = ciag_a_inserer($grpauteurs,$oldgrpauteurs);
			$deletegrpauteurs = ciag_a_supprimer($grpauteurs,$oldgrpauteurs);

			// verifier le droit de gerer ces groupes
			$insertgrpauteurs = ciag_groupes_autorises($insertgrpauteurs);
			$deletegrpauteurs = ciag_groupes_autorises($deletegrpauteurs);
			
			// si un groupe contient un EC, verifier le droit de gerer cet EC
			$insertgrpauteurs = ciag_groupes_avec_ec_autorises($insertgrpauteurs);
			$deletegrpauteurs = ciag_groupes_avec_ec_autorises($deletegrpauteurs);
			
			
			if ($insertgrpauteurs) {
				foreach ($insertgrpauteurs as $insertgrpauteur) {
					sql_insertq('spip_ciag_grpauteurs_auteurs', array('id_auteur' => $id_auteur, 'id_groupe' => $insertgrpauteur));
				}
				ciag_denormaliser_ajout_grpauteurs_a_auteur($insertgrpauteurs,$id_auteur);
			}
			
			if ($deletegrpauteurs) {
				$in = sql_in('id_groupe',$deletegrpauteurs);
				sql_delete("spip_ciag_grpauteurs_auteurs", $in." AND id_auteur=".$id_auteur);
				ciag_denormaliser_retrait_grpauteurs_a_auteur($deletegrpauteurs,$id_auteur);
			}
			
		} else {
			$return = false;
		}
	}
	return $return;
}


function ciag_denormaliser_ajout_grpauteurs_a_auteur($grpauteurs,$id_auteur) {

	if ($id_auteur = intval($id_auteur)) {
		$rubriques = ciag_liste_rubriques_des_grpauteurs($grpauteurs);

		if ($rubriques) {
			if (defined('_DIR_PLUGIN_CIAR')){
				include_spip('inc/ciar_commun');
				$auteurs = array($id_auteur);
				$rubriques_ec = ciar_ec_direct_parmi_rubriques($rubriques);
				foreach ($rubriques_ec as $id_rubrique_ec)
					ciar_ajouter_auteurs_dans_ec($auteurs,$id_rubrique_ec,true);

				$rubriques_hors_ec = array_diff($rubriques,$rubriques_ec);
				$insert_auteurs = ciag_ajouter_rubriques_a_auteur($rubriques_hors_ec,$id_auteur);
					
			} else {
				$insert_auteurs = ciag_ajouter_rubriques_a_auteur($rubriques,$id_auteur);
			}		
		}
	}
	return true;
}			


function ciag_denormaliser_retrait_grpauteurs_a_auteur($grpauteurs,$id_auteur) {

	if ($id_auteur = intval($id_auteur)) {
		$rubriques = ciag_liste_rubriques_des_grpauteurs($grpauteurs);

		if (defined('_DIR_PLUGIN_CIAR')){
			include_spip('inc/ciar_commun');
			$rubriques_ec = ciar_ec_direct_parmi_rubriques($rubriques);
		}
		
		if ($rubriques) {
			// ne rien faire si l'auteur est lié à la rubrique via un autre groupe
			// (la denormalisation intervient apres la suppression des groupes)
			$rubriques_auteur = ciag_liste_rubriques_de_auteur_via_grpauteurs($id_auteur);

			// retirer les rubriques des groupes a l'auteur
			$suppr = array();					
			foreach ($rubriques as $id_rubrique) {
				if ($id_rubrique = intval($id_rubrique)) // si '0' on ignore
					if (!in_array($id_rubrique,$rubriques_auteur))
						$suppr[]=$id_rubrique;
			}
			if ($suppr) {
				ciag_retirer_rubriques_a_auteur($suppr,$id_auteur);
				
				if (defined('_DIR_PLUGIN_CIAR')){
					$suppr_ec = array_intersect($suppr,$rubriques_ec);
					if ($suppr_ec) {
						foreach ($suppr_ec as $id_rubrique_ec)
							ciar_retirer_auteurs_dans_ec(array($id_auteur),$id_rubrique_ec,false);
					}
				}
			}
		}
	}
	return true;
}			


function ciag_auteur_adminsite($id_auteur) {
	$return = false;

	if ($id_auteur = intval($id_auteur)) {
		$row = sql_fetsel("*", "spip_auteurs","id_auteur=$id_auteur AND statut='0minirezo'","","");	

		if ($row) {
			// est-ce un admin non restreint ?
			$admin_non_restreint = false;
			if (spip_version()>=3)
				$n = sql_countsel("spip_auteurs_liens", "objet='rubrique' AND id_auteur=".$id_auteur);
			else	
				$n = sql_countsel("spip_auteurs_rubriques", "id_auteur=".$id_auteur);
			if (!$n)
				$admin_non_restreint = true;

			if ($row['webmestre']=='oui')
				return true;

			if  (defined('_ID_WEBMESTRES')) {
				if (in_array($id_auteur, explode(':', _ID_WEBMESTRES)))
					return true;
			}

			if (defined('_DIR_PLUGIN_CIAR')){	
				if ($row['cioption']=='ecadminsite')
					return true;
					
				// si admin non restreint et pas encore de cioption
				if ($admin_non_restreint AND $row['cioption']=='')
					return true;
			}

			// ne pas denormaliser un administrateur non restreint
			// s'il en reste un seul (ou si leur nombre est egal a la constante _CIAG_NB_ADMINSITE)
			if ($admin_non_restreint) {
				$nb_adminsite = ciag_nb_admin_sans_rubrique();
				$nb_mini = 1;
				if (defined('_CIAG_NB_ADMINSITE')) {
					if (intval(_CIAG_NB_ADMINSITE)>$nb_mini)
						$nb_mini = intval(_CIAG_NB_ADMINSITE);
				}
				if ($nb_adminsite<=$nb_mini)
					return true;		
			}
			
		}
	}
	return $return;
}

// au pluriel
function ciag_auteurs_adminsite($auteurs,$sans_nb_adminsite=false) {
	$return = array();

	if ($auteurs) {
		if (is_array($auteurs)) {
			$nb_adminsite = ciag_nb_admin_sans_rubrique();
			$nb_mini = 1;
			if (defined('_CIAG_NB_ADMINSITE')) {
				if (intval(_CIAG_NB_ADMINSITE)>$nb_mini)
					$nb_mini = intval(_CIAG_NB_ADMINSITE);
			}
			
			$in = sql_in('id_auteur',$auteurs);
			$result = sql_select("*", "spip_auteurs",$in." AND statut='0minirezo'","","id_auteur DESC");

			while ($row = sql_fetch($result)) {

				// est-ce un admin non restreint ?
				$admin_non_restreint = false;
				if (spip_version()>=3)
					$n = sql_countsel("spip_auteurs_liens", "objet='rubrique' AND id_auteur=".$id_auteur);
				else	
					$n = sql_countsel("spip_auteurs_rubriques", "id_auteur=".$id_auteur);
				if (!$n)
					$admin_non_restreint = true;
					

				if ($row['webmestre']=='oui')
					$return[] = $row['id_auteur'];
				
				if  (defined('_ID_WEBMESTRES')) {
					if (in_array($row['id_auteur'], explode(':', _ID_WEBMESTRES)))
						$return[] = $row['id_auteur'];
				}
		
				if (defined('_DIR_PLUGIN_CIAR')){
					if ($row['cioption']=='ecadminsite')
						$return[] = $row['id_auteur'];
						
					// si admin non restreint et pas encore de cioption
					if ($admin_non_restreint AND $row['cioption']=='')
						$return[] = $row['id_auteur'];
				}
	
				// ne pas denormaliser un administrateur non restreint
				// s'il en reste un seul (ou si leur nombre est egal a la constante _CIAG_NB_ADMINSITE)
				if ($admin_non_restreint AND !$sans_nb_adminsite) {
					if ($nb_adminsite<=$nb_mini){
						$return[] = $row['id_auteur'];
					} else {
						// actualiser nb_adminsite
						if (!in_array($row['id_auteur'],$return))
							$nb_adminsite = $nb_adminsite-1;
					}
				}
				
			}
		}
	}
	return array_unique($return);
}


// Modifier la liste des groupes d'auteurs de la rubrique
function ciag_modifier_grpauteurs_de_rubrique($grpauteurs,$id_rubrique,$ctrl_concurrent=false,$image_avant='') {
	$return = true;
	$id_rubrique = intval($id_rubrique);
	$grpauteurs = ciag_securise($grpauteurs,"array_intval");
	
		
	if ($id_rubrique>0) {

		// si rubrique EC, verifier droit de gerer cet EC
		if (defined('_DIR_PLUGIN_CIAR')){
			include_spip('ciar_fonctions');
			if (ciar_rub_ec($id_rubrique)) {
				if (!autoriser('ecmodifier','rubrique',$id_rubrique))
					return true;
			}
		}
		
		
		$insertgrpauteurs = array();
		$deletegrpauteurs = array();
		$oldgrpauteurs = array();
					
		// groupes de cette rubrique memorises dans la base
		$oldgrpauteurs = ciag_liste_grpauteurs_de_rubrique($id_rubrique);

		// Prévention des accès concurents
		if ($ctrl_concurrent)
			$concurrent = ciag_acces_concurrents($image_avant,$oldgrpauteurs);
		else
			$concurrent = false;
		
	
		if (!$concurrent) {
	
			$insertgrpauteurs = ciag_a_inserer($grpauteurs,$oldgrpauteurs);
			$deletegrpauteurs = ciag_a_supprimer($grpauteurs,$oldgrpauteurs);

			if ($insertgrpauteurs) {
				foreach ($insertgrpauteurs as $insertgrpauteur) {
					sql_insertq('spip_ciag_grpauteurs_rubriques', array('id_rubrique' => $id_rubrique, 'id_groupe' => $insertgrpauteur));
				}
				ciag_denormaliser_ajout_grpauteurs_a_rubrique($insertgrpauteurs,$id_rubrique);
			}
			
			if ($deletegrpauteurs) {
				$in = sql_in('id_groupe',$deletegrpauteurs);
				sql_delete("spip_ciag_grpauteurs_rubriques", $in." AND id_rubrique=".$id_rubrique);
				ciag_denormaliser_retrait_grpauteurs_a_rubrique($deletegrpauteurs,$id_rubrique);
			}
			
			// cas ou une fille de la rubrique etait affectee a un des groupes d'auteurs
			ciag_supprimer_grpauteurs_rubriques_filles($id_rubrique,$insertgrpauteurs);
			
		} else {
			$return = false;
		}			
		 
	}
	return $return;
}

function ciag_denormaliser_ajout_grpauteurs_a_rubrique($grpauteurs,$id_rubrique) {

	if ($id_rubrique = intval($id_rubrique)) {
		$auteurs = ciag_liste_auteurs_des_grpauteurs($grpauteurs);

		if ($auteurs) {
			// administrateurs du site
			$adminsite = ciag_auteurs_adminsite($auteurs);

			// ne rien faire si un auteur est lié à la rubrique via un autre groupe
			$auteurs_rubrique = array();
			if (spip_version()>=3)
				$result = sql_select("id_auteur", "spip_auteurs_liens", "objet='rubrique' AND id_objet=".$id_rubrique,"","id_auteur");
			else
				$result = sql_select("id_auteur", "spip_auteurs_rubriques", "id_rubrique=".$id_rubrique,"","id_auteur");

			while ($row = sql_fetch($result))
				$auteurs_rubrique[] = $row['id_auteur'];
			
			
			// ajouter la rubrique aux auteurs des groupes
			$tab_couples = array();
			$n = 1;
			$max = ciag_nb_max_insert();
			$fait = false;

			// si la rubrique est un EC
			if (defined('_DIR_PLUGIN_CIAR')){
				include_spip('inc/ciar_commun');
				$rubriques = array($id_rubrique);
				$rubriques_ec = ciar_ec_direct_parmi_rubriques($rubriques);
				foreach ($rubriques_ec as $id_rubrique_ec) {
					ciar_ajouter_auteurs_dans_ec($auteurs,$id_rubrique_ec,true);
					$fait = true;
				}
			}
		
			if (!$fait) {			
				if (spip_version()>=3) {
					foreach ($auteurs as $id_auteur) {
						if ($id_auteur = intval($id_auteur)) {
							// ne pas denormaliser les administrateurs du site
							if (!in_array($id_auteur,$adminsite)) {
								// ne rien faire si un auteur est lié à la rubrique via un autre groupe
								if (!in_array($id_auteur,$auteurs_rubrique)) {
									$n++;
									$tab_couples[] = array('id_auteur'=>$id_auteur, 'objet' => 'rubrique', 'id_objet' => $id_rubrique);
								}
							}
						}
						if ($n>$max) {
							// Inserer par groupes de 50 maximun (ou moins)
							sql_insertq_multi('spip_auteurs_liens',$tab_couples);
							$tab_couples = array();
							$n = 1;
						}
					}
					// Inserer le reste
					if ($tab_couples)
						sql_insertq_multi('spip_auteurs_liens',$tab_couples);
	
				} else {
					foreach ($auteurs as $id_auteur) {
						if ($id_auteur = intval($id_auteur)) {
							// ne pas denormaliser les administrateurs du site
							if (!in_array($id_auteur,$adminsite)) {
								// ne rien faire si un auteur est lié à la rubrique via un autre groupe
								if (!in_array($id_auteur,$auteurs_rubrique)) {
									$n++;
									$tab_couples[] = array('id_auteur'=>$id_auteur, 'id_rubrique' => $id_rubrique);
								}
							}
						}
						if ($n>$max) {
							// Inserer par groupes de 50 maximun (ou moins)
							sql_insertq_multi('spip_auteurs_rubriques',$tab_couples);
							$tab_couples = array();
							$n = 1;
						}
					}
					// Inserer le reste
					if ($tab_couples)
						sql_insertq_multi('spip_auteurs_rubriques',$tab_couples);
				
				}
			}		
		}		
	}
	return true;
}			


function ciag_denormaliser_retrait_grpauteurs_a_rubrique($grpauteurs,$id_rubrique) {
	
	if ($id_rubrique = intval($id_rubrique)) {
		$auteurs = ciag_liste_auteurs_des_grpauteurs($grpauteurs);

		if (defined('_DIR_PLUGIN_CIAR')){
			include_spip('inc/ciar_commun');
			$rubriques_ec = ciar_ec_direct_parmi_rubriques(array($id_rubrique));
		}

		if ($auteurs) {
			// ne rien faire si l'auteur est lié à la rubrique via un autre groupe
			// (la denormalisation intervient apres la suppression des groupes)
			$auteurs_rubrique = array();
			$grpauteurs_rubrique = ciag_liste_grpauteurs_de_rubrique($id_rubrique);
			if ($grpauteurs_rubrique) {
				$in = sql_in('id_groupe',$grpauteurs_rubrique);
				$in2 = sql_in('id_auteur',$auteurs);
				$result = sql_select("id_auteur", "spip_ciag_grpauteurs_auteurs", $in." AND ".$in2,"","id_auteur");
				while ($row = sql_fetch($result))
					$auteurs_rubrique[] = $row['id_auteur'];
			}

			// retirer les auteurs des groupes a l'auteur
			$suppr = array();				
			foreach ($auteurs as $id_auteur) {
				if ($id_auteur = intval($id_auteur)) // si '0' on ignore
					if (!in_array($id_auteur,$auteurs_rubrique)) {
						$suppr[] = $id_auteur;
					}
			}
			
			if ($suppr) {
				ciag_prevention_admin($suppr,$id_rubrique);
				$in = sql_in('id_auteur',$suppr);
				if (spip_version()>=3) {
					sql_delete("spip_auteurs_liens", $in." AND objet='rubrique' AND id_objet=".$id_rubrique);
				} else {
					sql_delete("spip_auteurs_rubriques", $in." AND id_rubrique=".$id_rubrique);
				}

				if (defined('_DIR_PLUGIN_CIAR')){
					if (in_array($id_rubrique,$rubriques_ec))
						ciar_retirer_auteurs_dans_ec($suppr,$id_rubrique,false);

				}
			}
		}
	}
	return true;
}


function ciag_nb_max_insert() {

	if (defined('_CIAG_NB_MAX_INSERT') AND intval(_CIAG_NB_MAX_INSERT)>0)
		$max = intval(_CIAG_NB_MAX_INSERT);	
	else
		$max = 50;

	return $max;
}


function ciag_acces_concurrents($ci_image_avant,$ci_image_base) {
	$acces_concurrent = false;

	// Prévention des accès concurents
	if ($ci_image_base)
		if (is_array($ci_image_base))
			$ci_image_base = implode(",", $ci_image_base);

	if ($ci_image_avant) {
		if ($ci_image_base) {
			if (!($ci_image_avant==$ci_image_base))
				$acces_concurrent = true;
		} else {
			$acces_concurrent = true;
		}		
	} elseif ($ci_image_base)
		$acces_concurrent = true;

	return $acces_concurrent;
}


function ciag_a_inserer($objets,$old_objets) {
	$insert_objets = array();
	
	if (isset($objets) AND is_array($objets)) {
		foreach ($objets as $objet) {
			$objet = intval($objet);
			if (in_array($objet,$old_objets)){
				// si le nouvel objet est le même que celui en base, ne rien faire
			} elseif ($objet>0) {
				$insert_objets[] = $objet;
			}		
		}
	}
	return $insert_objets;
}


function ciag_a_supprimer($objets,$old_objets,$prevention=false) {
	$delete_objets = array();
	if (!$objets)
		$objets = array();
	
	if (isset($old_objets) AND is_array($old_objets)) {
		reset($old_objets);
		reset($objets);
		foreach ($old_objets as $old_objet) {
			$old_objet = intval($old_objet);
			if (in_array($old_objet,$objets)){
				// si l'ancien objet est toujours là, ne rien faire
			} else {
				// on ne supprime pas celui qui manipule (prévention)
				if (!$prevention OR $old_objet!=$GLOBALS['visiteur_session']['id_auteur'])
					$delete_objets[] = $old_objet;
			}		
		}
	}
	return $delete_objets;
}


/**
 * Groupes d'une rubrique par heritage
 */
function ciag_liste_grpauteurs_de_rubrique_par_heritage($id_rubrique){
	$grpauteurs = array();
	
	if ($id_rubrique = intval($id_rubrique)) {

		// rubriques ascendantes
		$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_rubrique, "", "");
		for (;;) {
			$id_parent = '';
			while ($row = sql_fetch($result)) {
				$id_parent = $row["id_parent"];
				$grpauteurs = array_merge($grpauteurs, ciag_liste_grpauteurs_de_rubrique($id_parent));	
			}
			if (!$id_parent) break;
			$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_parent, "", "");
		}
	}
	
	return $grpauteurs;
}


/**
 * L'auteur est-il affecte a cette rubrique via un groupe
 * (directement et PAS par heritage)
 * @param id_auteur,id_rubrique
 * @return string
 */
function ciag_auteur_rubrique_via_groupe($id_auteur,$id_rubrique) {
	static $rubauteurs = array();
	static $rubgrpauteurs = array();
	static $auteurs = array();
	static $grpauteurs = array();
	static $grpauteurs_titre = array();
	$return = '';

	if ($id_rubrique = intval($id_rubrique)) {
		if (!isset($rubgrpauteurs[$id_rubrique])) {
			$rubgrpauteurs[$id_rubrique] = ciag_liste_grpauteurs_de_rubrique($id_rubrique);
			$in = sql_in('id_groupe',$rubgrpauteurs[$id_rubrique]);
			$result = sql_select("id_groupe,titre","spip_ciag_grpauteurs",$in,"","id_groupe");
			while ($row = sql_fetch($result))
				$grpauteurs[$row['id_groupe']] = interdire_scripts(typo(extraire_multi($row['titre'])));

		}
		
		if (!isset($rubauteurs[$id_rubrique])) {
			$rubauteurs[$id_rubrique] = ciag_liste_auteurs_des_grpauteurs($rubgrpauteurs[$id_rubrique]);

			if ($rubgrpauteurs) {
				if (is_array($rubgrpauteurs)) {
					if (count($rubgrpauteurs)>=1) {
						$auteurs_grpauteurs = array();
						$in = sql_in('id_groupe',$rubgrpauteurs);
						// auteurs des groupes d'auteurs ?
						$result = sql_select("id_auteur,id_groupe","spip_ciag_grpauteurs_auteurs",$in,"","id_auteur,id_groupe");
						while ($row = sql_fetch($result))
							$auteurs_grpauteurs[$row['id_auteur']][] = $row['id_groupe'];
							
						$auteurs[$id_rubrique] = $auteurs_grpauteurs;
					}
				}
			}
		}
			
		$titres = array();
		if (isset($auteurs[$id_rubrique][$id_auteur])) {
			foreach($auteurs[$id_rubrique][$id_auteur] as $id_groupe) {
				$titres[] = $grpauteurs[$id_groupe];
			}
		}
		$return = implode('<br/>',$titres);
	}
	
	return $return;
}


function ciag_filtrer_enfants($rubriques){
	$return = array();
	
	if ($rubriques) {
		if (is_array($rubriques)) {
			if (defined('_DIR_PLUGIN_CIAR')){
				include_spip('ciar_fonctions');
			}

			foreach ($rubriques as $id_rubrique) {
				$ok = false;
				if (defined('_DIR_PLUGIN_CIAR')){
					// l'heritage s'arrete aux EC
					// il est donc normal d'avoir un EC et sa rubrique parente
					if (ciar_rub_ec_direct($id_rubrique)) {
						$return[] = $id_rubrique;
						$ok = true;
					}
				}

				if (!$ok) {
					// rubriques ascendantes
					$parents = array();
					$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_rubrique, "", "");
					for (;;) {
						$id_parent = '';
						while ($row = sql_fetch($result)) {
							$id_parent = $row["id_parent"];
							$parents[] = $id_parent;
						}
						if (!$id_parent) break;
						$result = sql_select("id_parent", "spip_rubriques", "id_rubrique=".$id_parent, "", "");
					}
					if (!array_intersect($parents,$rubriques))
						$return[] = $id_rubrique;

				}
			}		
		}
	}
	
	return $return;
}


// cas ou une fille de la rubrique etait affectee a un des groupes d'auteurs
function ciag_supprimer_grpauteurs_rubriques_filles($id_rubrique,$insertgrpauteurs) {
	
	if ($id_rubrique AND $insertgrpauteurs) {
		$filles = ciag_descendance($id_rubrique,true);
		$in = sql_in('id_rubrique',$filles);
		$in2 = sql_in('id_groupe',$insertgrpauteurs);
		$couples = array();
		
		$result = sql_select("*","spip_ciag_grpauteurs_rubriques", $in." AND ".$in2);
		while ($row = sql_fetch($result)) {
			$couples[]=array('id_rubrique'=>$row['id_rubrique'],'id_groupe'=>$row['id_groupe']);
		}

		if ($couples) {
			foreach ($couples as $rowdel) {
				sql_delete("spip_ciag_grpauteurs_rubriques", "id_rubrique=".$rowdel['id_rubrique']." AND id_groupe=".$rowdel['id_groupe']);
				ciag_denormaliser_retrait_rubriques(array($rowdel['id_rubrique']),$rowdel['id_groupe']);
			}
		}
	}
}


function ciag_descendance($id,$strict=false) {

	$return = array(0);
	
	if ($id) {
		// normaliser $id qui a pu arriver comme un array, comme un entier, ou comme une chaine NN,NN,NN
		if (!is_array($id)) $id = explode(',',$id);
		$id = join(',', array_map('intval', $id));
	
		if ($strict)
			$branche = '';
		else
			// Notre branche commence par la rubrique de depart
			$branche = $id;
	
		// On ajoute une generation (les filles de la generation precedente)
		// jusqu'a epuisement
		while ($filles = sql_allfetsel('id_rubrique', 'spip_rubriques',
		sql_in('id_parent', $id))) {
			$id = join(',', array_map('array_shift', $filles));
			$branche .= ',' . $id;
		}
		
		$return = explode(',',$branche);
	}

	return $return;
}

function ciag_prevention_admin($auteurs,$rubriques) {

	// Les admin restreint, qui n'administraient que cette rubrique, deviennent redacteurs
	if (!is_array($auteurs))
		$auteurs = explode(',',$auteurs);
		
	foreach ($auteurs as $id_auteur) {
		$id_auteur = intval($id_auteur);
		if ($id_auteur>0) {
			if (spip_version()>=3) {
				$in = sql_in('id_objet',$rubriques);
				$not_in = sql_in('id_objet',$rubriques, 'NOT');
				$nb_in = sql_countsel("spip_auteurs_liens", "objet='rubrique' AND ".$in." AND id_auteur=".$id_auteur);
				$nb_not_in = sql_countsel("spip_auteurs_liens", "objet='rubrique' AND ".$not_in." AND id_auteur=".$id_auteur);
			} else {
				$in = sql_in('id_rubrique',$rubriques);
				$not_in = sql_in('id_rubrique',$rubriques, 'NOT');
				$nb_in = sql_countsel("spip_auteurs_rubriques", $in." AND id_auteur=".$id_auteur);
				$nb_not_in = sql_countsel("spip_auteurs_rubriques", $not_in." AND id_auteur=".$id_auteur);
			}
			if ($nb_in>0 AND !$nb_not_in)
				sql_updateq("spip_auteurs", array("statut" => '1comite'), "id_auteur=$id_auteur AND statut='0minirezo'");
		}
	}

	return true;
}

function ciag_groupes_autorises($groupes) {
	$groupes_ok = array();
	
	// verifier le droit de gerer ces groupes
	foreach ($groupes as $id_groupe) {
		if (autoriser('modifier','groupeauteur',$id_groupe))
			$groupes_ok[] = $id_groupe;
	}
	return $groupes_ok;
}


function ciag_groupes_avec_ec_autorises($groupes) {
	$groupes_ok = array();
	
	// si un groupe contient un EC, verifier le droit de gerer cet EC
	foreach ($groupes as $id_groupe) {
		if (!ciag_grp_contient_ec_pas_gerer($id_groupe))
			$groupes_ok[] = $id_groupe;
	}
	
	return $groupes_ok;
}

function ciag_grp_contient_ec_pas_gerer($id_groupe) {
	$return = false;
	
	// si un groupe contient un EC, verifier le droit de gerer cet EC
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');
		if (ciar_grp_contient_ec_pas_gerer($id_groupe))
			$return = true;
	}
	
	return $return;
}

function ciag_ec_non_gere($id_rubrique){
	$return = false;

	// La rubrique est-elle un EC pour lequel on ne gere pas les droits
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');
		if (ciar_ec_non_gere($id_groupe))
			$return = true;
	}

	return $return;
}

function ciag_rub_ec($id_rubrique){
	$return = false;

	// filtre de test pour savoir si la rubrique est un EC (ou dans un EC)
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');
		if (ciar_rub_ec($id_groupe))
			$return = true;
	}

	return $return;
}

function ciag_rub_ec_direct($id_rubrique){
	$return = false;

	// filtre de test pour savoir si la rubrique est un EC (pas de notion de descendance)
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');
		if (ciar_rub_ec_direct($id_groupe))
			$return = true;
	}

	return $return;
}


// ajouter des rubriques a un auteur
function ciag_ajouter_rubriques_a_auteur($rubriques,$id_auteur) {
	$insert_auteurs = array();
	$ok = true;

	
	// ne pas denormaliser les administrateurs du site
	if (!ciag_auteur_adminsite($id_auteur)) {
	
		// ne rien faire si l'auteur est déjà lié à la rubrique via un autre groupe
		// ou bien en affectation directe
		$rubriques_auteur = ciag_liste_rubriques_de_auteur($id_auteur);
	
		$tab_couples = array();
		$n = 1;
		$max = ciag_nb_max_insert();
		
		if (spip_version()>=3) {
			foreach ($rubriques as $id_rubrique) {
				if ($id_rubrique = intval($id_rubrique)) { // si '0' on ignore
					if (!in_array($id_rubrique,$rubriques_auteur)) {
						$insert_auteurs[] = $id_auteur;
						$n++;
						$tab_couples[] = array('id_auteur'=>$id_auteur, 'objet' => 'rubrique', 'id_objet' => $id_rubrique);
					}
				}
				if ($n>$max) {
					// Inserer par groupes de 50 maximun (ou moins)
					sql_insertq_multi('spip_auteurs_liens',$tab_couples);
					$tab_couples = array();
					$n = 1;
				}
			}
			// Inserer le reste
			if ($tab_couples)
				sql_insertq_multi('spip_auteurs_liens',$tab_couples);
	
		} else {
			foreach ($rubriques as $id_rubrique) {
				if ($id_rubrique = intval($id_rubrique)) { // si '0' on ignore
					if (!in_array($id_rubrique,$rubriques_auteur)) {
						$insert_auteurs[] = $id_auteur;
						$n++;
						$tab_couples[] = array('id_auteur'=>$id_auteur, 'id_rubrique' => $id_rubrique);
					}
				}
				if ($n>$max) {
					// Inserer par groupes de 50 maximun (ou moins)
					sql_insertq_multi('spip_auteurs_rubriques',$tab_couples);
					$tab_couples = array();
					$n = 1;
				}
			}
			// Inserer le reste
			if ($tab_couples)
				sql_insertq_multi('spip_auteurs_rubriques',$tab_couples);
	
		}
	}
	
	return $insert_auteurs;
}


// retirer des rubriques a un auteur
function ciag_retirer_rubriques_a_auteur($rubriques,$id_auteur) {

	ciag_prevention_admin($id_auteur,$rubriques);
	if (spip_version()>=3) {
		$in = sql_in('id_objet',$rubriques);
		sql_delete("spip_auteurs_liens", "id_auteur=".$id_auteur." AND objet='rubrique' AND ".$in);
	} else {
		$in = sql_in('id_rubrique',$rubriques);
		sql_delete("spip_auteurs_rubriques", $in." AND id_auteur=".$id_auteur);
	}

	return true;
}

function ciag_input_hidden($id,$name) {
	return '<input type="hidden" value='.$id.' name="'.$name.'[]" checked="checked" />';
}

// securite
function ciag_securise($tableau,$format) {
	if (!$format)
		return $tableau;
	
	if ($format=="array_intval") {
		if (!$tableau) $tableau = array();
		if (!is_array($tableau)) $tableau = array();
		
		$return = array();
		foreach ($tableau AS $valeur) {
			$return[] = intval($valeur);
		}
		$tableau = $return;
	}
	
	return $tableau;
}


function ciag_titres_rubriques_de_grpauteurs($id_groupe,$pasdelien=false) {
	$return = '';

	if (spip_version()>=3)
		$exec = "rubrique";
	else
		$exec = "naviguer";
	
	if ($id_groupe = intval($id_groupe)) {
		$rubriques = ciag_liste_rubriques_de_grpauteurs($id_groupe);
		
		if ($rubriques) {
			// si plugin ciar, tenir compte des EC
			$rubriques_exclues = array();
			if (defined('_DIR_PLUGIN_CIAR')){
				include_spip('ciar_fonctions');
				$rubriques_exclues = ciar_accessrubec();
				$rubriques = array_diff($rubriques,$rubriques_exclues);
			}
		
			$in = sql_in('id_rubrique',$rubriques);
			$result = sql_select('id_rubrique,titre', "spip_rubriques", $in);
			if ($pasdelien) {
				while ($row = sql_fetch($result))
					$return .= '<div>'.interdire_scripts(typo(extraire_multi($row['titre']))).'</div>';
			} else {
				while ($row = sql_fetch($result))
					$return .= '<div><a href="'.generer_url_ecrire($exec,"id_rubrique=".$row['id_rubrique']).'">'.interdire_scripts(typo(extraire_multi($row['titre']))).'</a></div>';
			}
		}
	}
	return $return;
}


function ciag_icone_verticale($lien, $texte, $fond, $fonction="", $class="", $javascript=""){
	if (spip_version()>=3)
		return icone_base($lien,$texte,$fond,$fonction,"verticale $class",$javascript);
	else
		return '';
}


function ciag_nb_admin_sans_rubrique() {
	$return = 0;

	if (spip_version()>=3)
		$n = sql_countsel("spip_auteurs AS A LEFT JOIN spip_auteurs_liens AS R ON A.id_auteur=R.id_auteur", "A.statut = '0minirezo' AND R.objet='rubrique' AND R.id_objet is NULL");
	else
		$n = sql_countsel("spip_auteurs AS A LEFT JOIN spip_auteurs_rubriques AS R ON A.id_auteur=R.id_auteur", "A.statut = '0minirezo' AND R.id_rubrique is NULL");
	if ($n>0)
		return $n;
							
	return $return;
}

?>