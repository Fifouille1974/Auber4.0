<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('ciar_fonctions');
include_spip('inc/ciar_commun');


function formulaires_editer_ciar_rubrique_protection_charger_dist($id_rubrique,$retour='', $config_fonc='', $row=array(), $hidden=''){
	$valeurs = array();
	$id_rubrique = intval($id_rubrique);

	if ((!autoriser('modifier','rubrique',$id_rubrique)) AND (!autoriser('ecmodifier','rubrique',$id_rubrique)))
		return false;

	$ciar_protection_par_heritage = ciar_protection_par_heritage($id_rubrique);	
	if ($ciar_protection_par_heritage=='_acces_indiv')
		return false;

	if ($ciar_protection_par_heritage=='_acces_restreint')
		$valeurs['protection'] = '_acces_restreint';
	else
		$valeurs['protection'] = '_acces_libre';
		
	$valeurs['id_rubrique'] = $id_rubrique;
	$row = sql_fetsel("*", "spip_ciar_rubriques_protection", "id_rubrique=".$id_rubrique, "", "");
	if ($row)
		if ($row['acces_restreint'])
			$valeurs['protection'] = $row['acces_restreint'];
			
	$valeurs['_hidden'] = "<input type='hidden' name='id_rubrique' value='".$id_rubrique."' />";

	if ($valeurs['protection'] == '_acces_indiv') {
		if (!autoriser('ecmodifier','rubrique',$id_rubrique))
			return false;
	}
	
	return $valeurs;
}


function formulaires_editer_ciar_rubrique_protection_verifier_dist($id_rubrique,$retour='', $config_fonc='', $row=array(), $hidden=''){

	// Ne pas mettre en EC une rubrique qui contient un EC dans son arborescence
	$protection = _request('ci_protection');
	if ($protection=="_acces_indiv") { 
		if (ciar_ec_dans_descendance($id_rubrique))	{
			$erreurs['message_erreur'] = _T('ciar:pas_ec_dans_ec');
		}
	}

	return $erreurs;
}

// http://doc.spip.org/@inc_editer_ciar_rubrique_protection_dist
function formulaires_editer_ciar_rubrique_protection_traiter_dist($id_rubrique,$retour='', $config_fonc='', $row=array(), $hidden=''){

	$id_rubrique = intval($id_rubrique);
	$protection = _request('ci_protection');
	$msg_protection = '';

	if (spip_version()>=3)
		$exec = "rubrique";
	else
		$exec = "naviguer";	
	
	// ne pas memoriser '_acces_restreint' si la rubrique en herite
	$ciar_protection_par_heritage = ciar_protection_par_heritage($id_rubrique);	
	if ($ciar_protection_par_heritage=='_acces_restreint')
		if ($protection == '_acces_restreint')
			$protection = '_acces_libre';
	
	$redirect = generer_url_ecrire($exec,"id_rubrique=$id_rubrique");
	$old_protection ="";
	
	// peut on modifier la rubrique voire creer un EC ?
	if ($protection=='_acces_indiv')
		$ciautorise = autoriser('eccreer','rubrique',$id_rubrique);
	else
		$ciautorise = autoriser('modifier','rubrique',$id_rubrique);

	if ($ciautorise) {	

		if (in_array($protection,array('_acces_libre','_acces_restreint','_acces_indiv'))) {
			
			if ($protection=='_acces_libre')
				$protection = '';
			
			$row = sql_fetsel("*", "spip_ciar_rubriques_protection", "id_rubrique=".$id_rubrique, "", "");
			if ($row) {
				$old_protection = $row['acces_restreint'];
				if ($protection!=$old_protection) {
					if ($protection){	
						sql_updateq("spip_ciar_rubriques_protection", array('acces_restreint'=>$protection), "id_rubrique=$id_rubrique");
						$msg_protection = $protection;
					} else {
						sql_delete("spip_ciar_rubriques_protection", "id_rubrique=".$id_rubrique);
						$msg_protection = '_acces_libre';
					}

					// vider les anciens droits d'acces
					$auteurs_acces = array();
					$q = sql_select("id_auteur", "spip_ciar_auteurs_acces_rubriques", "id_rubrique=$id_rubrique");
					while ($row = sql_fetch($q))
						$auteurs_acces[] = $row['id_auteur'];
					
					if ($auteurs_acces) {
						ciar_retirer_auteurs_dans_ec($auteurs_acces,$id_rubrique);
						// enlever aussi celui qui manipule
						sql_delete("spip_ciar_auteurs_acces_rubriques", "id_auteur=".$GLOBALS['visiteur_session']['id_auteur']." AND id_rubrique=".$id_rubrique);
					}

					// invalider les caches de rubriques 
					effacer_meta("date_calcul_rubriques");	
				}

			} else {
				if ($protection) {
					sql_insertq("spip_ciar_rubriques_protection", array('id_rubrique'=>$id_rubrique, 'acces_restreint'=>$protection));
					$msg_protection = $protection;
					
					// invalider les caches de rubriques 
					effacer_meta("date_calcul_rubriques");	
				}
			}	
		}
	
		if ($protection=="_acces_indiv") {
			if ($protection!=$old_protection)	
				$redirect = generer_url_ecrire("ciar_rubrique_protection","id_rubrique=$id_rubrique");
	
			$result = sql_countsel("spip_ciar_auteurs_acces_rubriques", "id_rubrique=$id_rubrique","","");
			if (!$result) {

				// si aucun auteur a le droit d'acces
				// affecte le droit d'acces a la rubrique aux auteurs restreints de cette rubrique
				// et au createur de l'EC
				$insertauteurs = array();
				if (spip_version()>=3)
					$result = sql_select("id_auteur", "spip_auteurs_liens", "objet='rubrique' AND id_objet=".$id_rubrique, "", "id_auteur");
				else
					$result = sql_select("id_auteur", "spip_auteurs_rubriques", "id_rubrique=".$id_rubrique, "", "id_auteur");
				while ($row = sql_fetch($result))	{
					$insertauteurs[] = $row['id_auteur'];
				}
	
				if (!in_array($GLOBALS['visiteur_session']['id_auteur'],$insertauteurs))
					$insertauteurs[] = $GLOBALS['visiteur_session']['id_auteur'];
				
				if ($insertauteurs)
					ciar_ajouter_auteurs_dans_ec($insertauteurs,$id_rubrique); 
					
				
				// donner tous les droits au createur de l'EC
				sql_updateq("spip_ciar_auteurs_acces_rubriques", array("cistatut_auteur_rub" => "eccma"), "id_rubrique=".$id_rubrique." AND id_auteur=".$GLOBALS['visiteur_session']['id_auteur']);
				
			} else {
				// si dans des donnees migrees on a des auteurs dans une rubrique ex EC devenu acces libre et qui redevient EC
				// verifier qu'on a au moins un eccma
				$result = sql_countsel("spip_ciar_auteurs_acces_rubriques", "id_rubrique=$id_rubrique AND cistatut_auteur_rub='eccma'","","");
				if (!$result) {
					// ajouter le cas echeant le createur de l'EC
					ciar_ajouter_auteurs_dans_ec(array($GLOBALS['visiteur_session']['id_auteur']),$id_rubrique);
					
					// donner tous les droits au createur de l'EC
					sql_updateq("spip_ciar_auteurs_acces_rubriques", array("cistatut_auteur_rub" => "eccma"), "id_rubrique=".$id_rubrique." AND id_auteur=".$GLOBALS['visiteur_session']['id_auteur']);
					
				}
			}
		}		

	}
	
	if ($msg_protection){
		if (defined('_DIR_PLUGIN_CITRACE')){
			if ($citrace = charger_fonction('citrace', 'inc')) {
				include_spip('inc/texte');
				$titre = '';
				$row = sql_fetsel("titre", "spip_rubriques", "id_rubrique=$id_rubrique");
				if ($row)
					$titre = interdire_scripts(supprimer_numero($row['titre']));
				
				if (!$old_protection)
					$old_protection = '_acces_libre';
	
				$message = '('.$titre.') - protection_new:'.$msg_protection." - protection_old:".$old_protection;				
				$citrace('rubrique', $id_rubrique, 'changement de protection de la rubrique', $message, $id_rubrique);
			}
		}
	}

	$res['message_ok'] = "";
	$res['redirect'] = $redirect;

	return $res;
}

?>