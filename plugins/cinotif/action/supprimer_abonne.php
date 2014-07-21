<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');
include_spip('inc/texte');
include_spip('inc/cinotif_commun');


function action_supprimer_abonne($id_abonne=NULL){
	if (autoriser('configurer')) {
		if (is_null($id_abonne)){
			$securiser_action = charger_fonction('securiser_action', 'inc');
			$id_abonne = $securiser_action();
		}
		if (intval($id_abonne)>0) {
			$where = "id_abonne=".intval($id_abonne);
			$row = sql_fetsel('email', 'spip_cinotif_abonnes', $where);	
			if ($row)
				$email = $row['email'];

			$evenements = array();
			$result = sql_select("id_evenement", "spip_cinotif_abonnements", $where);
			while ($row = sql_fetch($result)) {
				$evenements[] = $row['id_evenement'];	
			}
				
			$e = sql_delete("spip_cinotif_abonnements", $where);
			sql_delete("spip_cinotif_abonnes", $where);
			
			if ($e>0) {
				foreach ($evenements AS $id_evenement)
					cinotif_sympa_desabonner($email,$id_evenement);
			}

			cinotif_envoi_mail_suppression($email);
			cinotif_suppr_evenements_sans_abonnement();
		}
	} else {
		spip_log("action_supprimer_abonne interdit",_LOG_INFO_IMPORTANTE);
	}
}

function cinotif_envoi_mail_suppression($email) {

	if ($email){
		
		$ci_tableau = explode('@',$GLOBALS['visiteur_session']['email']);
		$ci_nom = strtolower($ci_tableau[0]);
		$ci_nom = str_replace('.', ' ',$ci_nom);
	
		$titre = textebrut(typo($GLOBALS['meta']['nom_site']." : "._T('cinotif:titre_mail_suppression')));
		$texte = _T('cinotif:texte_mail_suppression').' " '.textebrut(typo($GLOBALS['meta']['nom_site'])).' " '
				. _T('cinotif:texte_mail_suppression2').' '.textebrut(typo($ci_nom)).' '._T('cinotif:message_auto').'.';
	
		$envoyer_mail = charger_fonction('envoyer_mail','inc');
		$envoyer_mail($email,$titre, $texte);
	}

	return $return;
}

?>