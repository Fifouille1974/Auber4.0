<?php
/**
 * Plugin Saisie facile
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/ciparam_inc_commun');
include_spip('inc/filtres');


function action_editer_ciparam_cisf_raccourci() {
	
	$err = array();
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	
	// si id_article est un nombre
	if ($ci_id_article = intval($arg)) {
	
		$motscle = _request('motscle');
		$ci_motscle_img_avant = _request('cimodif_img_avant');
		
		// raccourcis de cet article mémorisés dans la base
		$oldmotscle = ciparam_lire_valeurs("spip_ci_raccourcis_articles","id_article",$ci_id_article,"raccourci");
		
			
		// Prévention des accès concurents
		$ci_motscle_img_base = "";
		if ($oldmotscle) $ci_motscle_img_base = implode(",", $oldmotscle);
		$cimotscle_concurrent=false;
		if ($ci_motscle_img_avant) {
			if ($ci_motscle_img_base) {
				if (!($ci_motscle_img_avant==$ci_motscle_img_base)) $cimotscle_concurrent=true;
			} else {
				$cimotscle_concurrent=true;
			}		
		} elseif ($ci_motscle_img_base) $cimotscle_concurrent=true;
	
		if (!$cimotscle_concurrent) {
			
			if (isset($motscle) AND is_array($motscle)) {
				if (isset($oldmotscle) AND is_array($oldmotscle)) {
					foreach ($motscle as $motcle) {
						if (in_array($motcle,$oldmotscle)){
							// si le nouveau motcle est le même que celui en base, ne rien faire
						} else {
							// anti hack
							if (ciparam_autoriser_raccourci("spip_ci_raccourcis_articles",$motcle))
								$insertmotscle[] = $motcle;
						}		
					}
				} else {
					foreach ($motscle as $motcle) {
						if (ciparam_autoriser_raccourci("spip_ci_raccourcis_articles",$motcle))
							$insertmotscle[] = $motcle;
					}
				}
			}

			if (isset($oldmotscle) AND is_array($oldmotscle)) {
				reset($oldmotscle);
				if (isset($motscle) AND is_array($motscle)) {
					reset($motscle);
					foreach ($oldmotscle as $oldmotcle) {
						if (in_array($oldmotcle,$motscle)){
							// si l'ancien mot est toujours là, ne rien faire
						} else {
							$deletemotscle[] = $oldmotcle;
						}		
					}
				} else {
					foreach ($oldmotscle as $oldmotcle) {
						$deletemotscle[] = $oldmotcle;
					}
				}
			}			
			
			if ($insertmotscle) {
				foreach ($insertmotscle as $insertmotcle) {
					sql_insertq('spip_ci_raccourcis_articles', array('raccourci' => $insertmotcle, 'id_article' => $ci_id_article));
				}	
			}
			
			if ($deletemotscle) {
				foreach ($deletemotscle as $deletemotcle) {
					sql_delete("spip_ci_raccourcis_articles", "raccourci='$deletemotcle' AND id_article=$ci_id_article");
				}	
			}
			
			// Invalider les caches
			if ($insertmotscle OR $deletemotscle) {
				include_spip('inc/invalideur');
				if (spip_version()>=3)
					suivre_invalideur("id='article/$ci_id_article'");
				else
					suivre_invalideur("id='id_article/$ci_id_article'");
			}

		}			
	}

	return array($ci_id_article,$err);
}

?>