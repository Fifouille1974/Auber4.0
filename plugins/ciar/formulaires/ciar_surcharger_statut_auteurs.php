<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciar_commun');

function formulaires_ciar_surcharger_statut_auteurs_charger_dist($id_rubrique,$pagination, $debut_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{		
	$id_rubrique = intval($id_rubrique);
	$valeurs['id_rubrique'] = $id_rubrique;
	$valeurs['tableau_auteurs'] = array();
	$valeurs['liste_auteurs'] = "";
	$valeurs['ci_pagination'] = $pagination;

	if (!autoriser('ecmodifier','rubrique',$id_rubrique))
		return false;
	
	// auteurs ayant actuellement acces a cette rubrique
	if ($id_rubrique>0) {
		$oldsurcharges = array();
		$oldsurcharges = ciar_liste_surcharge_auteurs_acces_rubrique($id_rubrique,$pagination,$debut_auteur);	
		if ($oldsurcharges)
			$valeurs['_liste_surcharges'] = md5(ciar_implode_with_key($oldsurcharges));


		$valeurs['_pascirv'] = ' ';
		$valeurs['_pascirvt'] = ' ';
		if (defined('_DIR_PLUGIN_CIRV')) {
			$valeurs['_pascirv'] = '';
			if (!defined('_CIRV_PAS_CIREDVALTOUT'))
				$valeurs['_pascirvt'] = '';		
		}
	}

	return $valeurs;
}

function formulaires_ciar_surcharger_statut_auteurs_verifier_dist($id_rubrique,$pagination, $debut_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	return $erreurs;
}

function formulaires_ciar_surcharger_statut_auteurs_traiter_dist($id_rubrique,$pagination, $debut_auteur,$retour='', $config_fonc='', $row=array(), $hidden='')
{
	
	// si id_rubrique est un nombre
	if ($id_rubrique = intval($id_rubrique)) {
	
	// Appliquer les status d'auteurs specifiques a cette rubrique
		$new_surcharges = _request('st');
		if ($new_surcharges) {
			if ($new_surcharges AND is_array($new_surcharges)) {
				$connect_id_auteur = $GLOBALS['visiteur_session']['id_auteur'];

				// surcharges de statuts pour cette rubrique memorises dans la base
				$delete_surcharges = array();				
				$oldsurcharges = ciar_liste_surcharge_auteurs_acces_rubrique($id_rubrique,$pagination,$debut_auteur);	
	
				// Prévention des accès concurents
				$ci_statut_img_avant = _request('cimodif_img_avant');
				$ci_statut_img_base = md5(ciar_implode_with_key($oldsurcharges));
				$cistatut_auteur_rub_concurrent=false;
				if ($ci_statut_img_avant) {
					if ($ci_statut_img_base) {
						if (!($ci_statut_img_avant==$ci_statut_img_base)) $cistatut_auteur_rub_concurrent=true;
					} else {
						$cistatut_auteur_rub_concurrent=true;
					}		
				} elseif ($ci_statut_img_base) $cistatut_auteur_rub_concurrent=true;
	
				
				if (!$cistatut_auteur_rub_concurrent) {
					// auteurs affectés a la rubrique sauf celui qui manipule (prévention)
					if (isset($new_surcharges[$connect_id_auteur])) unset($new_surcharges[$connect_id_auteur]);								
					$result = sql_select('auteurs.*', "spip_auteurs AS auteurs LEFT JOIN spip_ciar_auteurs_acces_rubriques AS l ON l.id_auteur=auteurs.id_auteur", "l.id_rubrique=$id_rubrique AND l.id_auteur<>".$connect_id_auteur);
					while ($row = sql_fetch($result)) { 
						$id_auteur = $row['id_auteur'];
						$defaultstatut = $row['statut'];
						$cioption = $row['cioption'];

						if (isset($new_surcharges[$id_auteur])){
							
							// ne pas changer le statut d'un visiteur
							if ($defaultstatut=='6forum'){
								unset($new_surcharges[$id_auteur]);

							} else {				
								// Cas particulier de certains statuts
//								if ($defaultstatut=='0minirezo' AND (($cioption=='eccma') OR ($cioption=='ecadminsite')))
//									$defaultstatut='eccma';

								// si une surcharge de statut existait pour cet auteur sur cette rubrique
								if ($oldsurcharges[$id_auteur]){
									if ($new_surcharges[$id_auteur]==$oldsurcharges[$id_auteur]) {
										// si le nouveau statut est le meme que celui en base, ne pas faire de requete sql
										unset($new_surcharges[$id_auteur]);
									} else {
										if ($new_surcharges[$id_auteur]==$defaultstatut) {
											// si le nouveau statut est different de celui en base
											// mais est égal au statut par défaut, effacer la surcharge
											$delete_surcharges[]=$id_auteur;
											unset($new_surcharges[$id_auteur]);
										}
									}		
								} else {
									// si le nouveau statut est egal au statut par défaut (sauf 1comite en raison de ciredval)
									// et qu'il n'y a pas de surcharge existante pour cet auteur sur cette rubrique
									// ne pas faire de requete sql
									if ($new_surcharges[$id_auteur]==$defaultstatut){
										if ($defaultstatut!='1comite')
											unset($new_surcharges[$id_auteur]);
									}
								}	
							}
						}		
					}	
					
					if ($new_surcharges) {
						foreach ($new_surcharges as $idaut => $idstatut) {
							if (ereg("^(eccma|0minirezo|1comite|ciredval|ciredvaltout|6forum)$",$idstatut))
								sql_updateq("spip_ciar_auteurs_acces_rubriques", array("cistatut_auteur_rub" => $idstatut), "id_rubrique=".$id_rubrique." AND id_auteur=".$idaut);
						}	
					}
					
					if ($delete_surcharges) {
						sql_updateq("spip_ciar_auteurs_acces_rubriques", array("cistatut_auteur_rub" => ""), "id_rubrique=".$id_rubrique." AND id_auteur IN (".implode(",",$delete_surcharges).",0) AND id_auteur<>".$connect_id_auteur);
					}
								
				}			
			}	
		}	
	}

	if ($cistatut_auteur_rub_concurrent) {
		$res['message_erreur'] = _T("alerte_modif_info_concourante");
	} else {
		$res['message_ok'] = "";
		$res['redirect'] = generer_url_ecrire("ciar_rubrique_protection","id_rubrique=$id_rubrique");
	}

	return $res;	
}

function ciar_code_statut_auteur_rubrique($statut, $cistatut_auteur_rub, $cistatut='') {

	if ($cistatut) $statut = $cistatut;
	if ($cistatut_auteur_rub) $statut = $cistatut_auteur_rub;

	return $statut;
}

function ciar_statut_auteur($statut, $cistatut='') {

	static $titre_des_statuts ='';

	// eviter de retraduire a chaque appel
	if (!$titre_des_statuts) {
		
		$titre_des_statuts = array(
		"1comite" => _T('ciar:titre_1comite'),
		"ciredval" => _T('ciar:titre_ciredval'),
		"ciredvaltout" => _T('ciar:titre_ciredvaltout'),
		"0minirezo" => _T('item_administrateur_2'),
		"6forum" => _T('ciar:titre_6forum')
		);
		
	}
	  
	if ($cistatut) $statut = $cistatut;

	return $titre_des_statuts[$statut];
}

function ciar_implode_with_key($assoc, $inglue = '=', $outglue = '&')
{
   $return = "";
   if (isset($assoc)) {
	   foreach ($assoc as $tk => $tv) $return .= $outglue.$tk.$inglue.$tv;
	   if($return) $return=substr($return,1);
   }	   
   return $return;
}

?>