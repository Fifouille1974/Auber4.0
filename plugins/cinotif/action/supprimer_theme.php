<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');


function action_supprimer_theme($id_evenement=null){

	if (is_null($id_evenement)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$id_evenement = $securiser_action();
	}

	if (autoriser('configurer', 'configuration')) {
		if (!sql_countsel("spip_cinotif_abonnements","id_evenement=" .intval($id_evenement)))
			sql_delete("spip_cinotif_evenements", "id_evenement=" .intval($id_evenement));
	} else {
		spip_log("action_supprimer_theme $id_evenement interdit",_LOG_INFO_IMPORTANTE);
	}
}

?>