<?php
/**
 * Plugin cilien
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');
include_spip('inc/cinotif_commun');


function action_supprimer_abonnement($id_evenement=NULL){
	if (is_null($id_evenement)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$id_evenement = $securiser_action();
	}
	
	if (isset($GLOBALS['visiteur_session']['id_auteur']) && $GLOBALS['visiteur_session']['id_auteur'])
		$id_auteur = $GLOBALS['visiteur_session']['id_auteur'];
	else
		$id_auteur = 0;
	
	if ($id_auteur AND $id_evenement) {
		$id_abonne = cinotif_id_abonne($id_auteur,'');
		$row = sql_fetsel('email', 'spip_cinotif_abonnes', "id_abonne=".intval($id_abonne));	
		if ($row)
			$email = $row['email'];
			
		sql_delete("spip_cinotif_abonnements", "id_abonne=".$id_abonne." AND id_evenement=".intval($id_evenement));
		cinotif_sympa_desabonner($email,$id_evenement);		
		cinotif_suppr_evenements_sans_abonnement();		
	}
}

?>