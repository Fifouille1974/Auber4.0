<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/filtres'); 

// Ajouter des auteurs a un EC
function ciar_ajouter_auteurs_dans_ec($auteurs,$id_rubrique,$denormaliser=true) {

	if ($id_rubrique = intval($id_rubrique)) {
		if ($auteurs) {
			if (is_array($auteurs)) {
				// verifier qu'un des auteurs n'est pas a la poubelle				
				$auteurs_actif = array();
				$result = sql_select("id_auteur", "spip_auteurs", "id_auteur IN (".implode(",",$auteurs).") AND statut<>'poubelle'","","");	
				while ($row = sql_fetch($result))
					$auteurs_actif[]=$row['id_auteur'];

				
				// auteurs restreints a cette rubrique
				$rubrestreints = array();
				if (spip_version()>=3)
					$result = sql_select("id_auteur", "spip_auteurs_liens", "objet='rubrique' AND id_objet=".$id_rubrique,"","");
				else
					$result = sql_select("id_auteur", "spip_auteurs_rubriques", "id_rubrique=".$id_rubrique,"","");	
				while ($row = sql_fetch($result))
					$rubrestreints[]=$row['id_auteur'];


				// cas des admin restreint ou des ecadminrestreint qui ne sont pas restreints a cette rubrique
				$ecadminrestreints = array();
				$result = sql_select("*", "spip_auteurs","id_auteur IN (".implode(",",$auteurs).")","","");	
				while ($row = sql_fetch($result)) {
					$id_auteur = $row['id_auteur'];
					if (!in_array($id_auteur,$rubrestreints)) {
						// cas des ecadminrestreint ou eccma qui ne sont pas restreints a cette rubrique
						if (in_array($row['cioption'],array('ecadminrestreint','eccma'))) {
							$ecadminrestreints[]=$id_auteur;

						// cas des admin restreint qui ne sont pas restreints a cette rubrique
						} elseif ($row['statut']=='0minirezo') {
							if (spip_version()>=3) {
								if (sql_countsel("spip_auteurs_liens", "objet='rubrique' AND id_auteur=$id_auteur")>0)
									$ecadminrestreints[]=$id_auteur;
							} else {
									if (sql_countsel("spip_auteurs_rubriques", "id_auteur=$id_auteur")>0)
										$ecadminrestreints[]=$id_auteur;
							}
						// compatibilite avec le plugin cirr
						} elseif (defined('_DIR_PLUGIN_CIRR')) {
							if ($row['statut']=='1comite')
								$ecadminrestreints[]=$id_auteur;
						}
					}
				}

				// auteurs deja dans cet ec
				$auteurs_ec = array();
				$result = sql_select("id_auteur", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=".$id_rubrique, "", "id_auteur");
				while ($row = sql_fetch($result))
					$auteurs_ec[] = $row['id_auteur'];


				foreach ($auteurs as $auteur) {
					if ($auteur = intval($auteur)) {
						if (in_array($auteur,$auteurs_actif)) {
							// ajouter les auteurs a cet EC
							if (!in_array($auteur,$auteurs_ec))
								sql_insertq('spip_ciar_auteurs_acces_rubriques', array('id_auteur' => $auteur, 'id_rubrique' => $id_rubrique));
	
	
							// cas des ecadminrestreint qui ne sont pas restreints a cette rubrique
							// les restreindre a cette rubrique
							if ($denormaliser) {
								if (in_array($auteur,$ecadminrestreints)) {
									if (spip_version()>=3)
										sql_insertq('spip_auteurs_liens', array('id_auteur' => $auteur, 'objet' => 'rubrique', 'id_objet' => $id_rubrique));
									else						
										sql_insertq('spip_auteurs_rubriques', array('id_auteur' => $auteur, 'id_rubrique' => $id_rubrique));
								}
							}
						}
					}
				}	
			}
		}
	}	
	
	return true;
}


// Retirer des auteurs a un EC
function ciar_retirer_auteurs_dans_ec($auteurs,$id_rubrique,$denormaliser=true) {

	if ($id_rubrique = intval($id_rubrique)) {
		if ($auteurs) {
			if (is_array($auteurs)) {
				// cas des ecadminrestreint
				
				// auteurs restreints a cette rubrique
				$rubrestreints = array();
				if (spip_version()>=3)
					$result = sql_select("id_auteur", "spip_auteurs_liens", "objet='rubrique' AND id_objet=".$id_rubrique,"","");	
				else
					$result = sql_select("id_auteur", "spip_auteurs_rubriques", "id_rubrique=".$id_rubrique,"","");	
				while ($row = sql_fetch($result)) {
					$rubrestreints[]=$row['id_auteur'];
				}

				// cas des admin restreint ou des ecadminrestreint qui sont restreints a cette rubrique
				$ecadminrestreints = array();
				$result = sql_select("*", "spip_auteurs","id_auteur IN (".implode(",",$auteurs).")","","");	
				while ($row = sql_fetch($result)) {
					$id_auteur = $row['id_auteur'];
					if (in_array($id_auteur,$rubrestreints)) {
						// cas des ecadminrestreint qui sont restreints a cette rubrique
						if ($row['cioption']=='ecadminrestreint') {
							$ecadminrestreints[]=$id_auteur;

						// cas des admin restreint qui sont restreints a cette rubrique
						} elseif ($row['statut']=='0minirezo') {
							$ecadminrestreints[]=$id_auteur;

						// compatibilite avec le plugin cirr
						// et tenir compte du cas d'un changement de statut de l'auteur (dans SPIP) depuis qu'il est dans l'EC.
						} elseif ($row['statut']=='1comite' OR $row['statut']=='6forum') {
							$ecadminrestreints[]=$id_auteur;
						}
					}
				}
	
				
				foreach ($auteurs as $auteur) {
					if ($auteur = intval($auteur)) {
						// on ne supprime pas celui qui manipule (prévention)
						if ($auteur!=$GLOBALS['visiteur_session']['id_auteur']) {
							sql_delete("spip_ciar_auteurs_acces_rubriques", "id_auteur=$auteur AND id_rubrique=$id_rubrique");
							// cas des admin restreint ou ecadminrestreint qui sont restreints a cette rubrique
							// ne plus les restreindre a cette rubrique
							if ($denormaliser) {
								if (in_array($auteur,$ecadminrestreints)) {
									if (spip_version()>=3)
										sql_delete("spip_auteurs_liens", "id_auteur=$auteur AND objet='rubrique' AND id_objet=$id_rubrique");
									else
										sql_delete("spip_auteurs_rubriques", "id_auteur=$auteur AND id_rubrique=$id_rubrique");
									
									// Les admin restreint, qui n'administraient que cette rubrique, deviennent redacteurs
									if (spip_version()>=3)
										$n = sql_countsel("spip_auteurs_liens", "objet='rubrique' AND id_auteur=$auteur");
									else
										$n = sql_countsel("spip_auteurs_rubriques", "id_auteur=$auteur");
									if (!$n)
										sql_updateq("spip_auteurs", array("statut" => '1comite'), "id_auteur=$auteur AND statut='0minirezo'");
								}
							}
						}
					}		
				}
			}
		}
	}	
	
	return true;
}


// Utile pour le plugin CIAG
// Parmi les rubriques, quels sont les EC (sans tenir compte des descendances)
function ciar_ec_direct_parmi_rubriques($rubriques){
	$return = array();
	
	if ($rubriques){
		$in = sql_in('id_rubrique',$rubriques);
		$result = sql_select("id_rubrique", "spip_ciar_rubriques_protection", $in." AND acces_restreint='_acces_indiv'", "","");
		while ($row = @sql_fetch($result))
			$return[] = $row['id_rubrique'];
	}
	
	return $return;
}

function ciar_liste_auteurs_tranche($pagination=0,$debut=0,$where='') {
	
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

function ciar_liste_auteurs_acces_rubrique($id_rubrique,$pagination=0,$debut_auteur=0) {
 	$auteurs = array();
	
	if ($id_rubrique = intval($id_rubrique)) {
		$in = '';
		// si pagination
		if ($pagination>0) {
			// limiter aux statuts qui correspondent
			$where = "statut<>'5poubelle'";
			
			// quels sont les id_auteur de cette tranche de pagination ?
			$auteurs_tranche = ciar_liste_auteurs_tranche($pagination,$debut_auteur,$where);
			$in = ' AND '.sql_in('id_auteur',$auteurs_tranche);
		}				
		$result = sql_select("id_auteur", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=$id_rubrique.$in","","id_auteur");
		while ($row = sql_fetch($result))
			$auteurs[] = $row['id_auteur'];
	}
	return $auteurs;
} 

function ciar_liste_retirer_auteurs_acces_rubrique($id_rubrique,$pagination=0,$debut_auteur=0) {
	$auteurs = array();	
		
	if ($id_rubrique = intval($id_rubrique)) {
		// tri par nom
		$result = sql_select("ciar.id_auteur", "spip_ciar_auteurs_acces_rubriques AS ciar LEFT JOIN spip_auteurs AS lien ON ciar.id_auteur=lien.id_auteur", "ciar.id_rubrique=$id_rubrique","","lien.nom");
		
		if ($pagination>0) {
			$debut = $debut_auteur;
			$fin = $debut + $pagination;
			$compteur = 0;
			while ($row = sql_fetch($result)){
				if ($compteur>=$fin)
					break;
				if ($compteur>=$debut)
					$auteurs[] = $row['id_auteur'];
					
				$compteur++;
			}
			
		} else {
			while ($row = sql_fetch($result))
				$auteurs[] = $row['id_auteur'];
		}
	}
	return $auteurs;
} 


function ciar_liste_surcharge_auteurs_acces_rubrique($id_rubrique,$pagination=0,$debut_auteur=0) {
	$surcharges = array();	
		
	if ($id_rubrique = intval($id_rubrique)) {
		// tri par nom
		$result = sql_select("ciar.id_auteur, ciar.cistatut_auteur_rub", "spip_ciar_auteurs_acces_rubriques AS ciar LEFT JOIN spip_auteurs AS lien ON ciar.id_auteur=lien.id_auteur", "ciar.id_rubrique=$id_rubrique","","lien.nom");
		if ($pagination>0) {
			$debut = $debut_auteur;
			$fin = $debut + $pagination;
			$compteur = 0;
			while ($row = sql_fetch($result)){
				if ($compteur>=$fin)
					break;
				if ($compteur>=$debut)
					$surcharges[$row['id_auteur']] = $row['cistatut_auteur_rub'];
	
				$compteur++;
			}
			
		} else {
			while ($row = sql_fetch($result))
				$surcharges[$row['id_auteur']] = $row['cistatut_auteur_rub'];
		}
	}
	return $surcharges;
} 

?>