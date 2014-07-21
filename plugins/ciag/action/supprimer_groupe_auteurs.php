<?php
/**
 * Plugin Groupes d'auteurs
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');


function action_supprimer_groupe_auteurs($id_groupe=null){

	if (is_null($id_groupe)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$id_groupe = $securiser_action();
	}

	if (autoriser('supprimer','groupeauteur',$id_groupe)) {
		sql_delete("spip_ciag_grpauteurs_rubriques", "id_groupe=" .intval($id_groupe));
		sql_delete("spip_ciag_grpauteurs_auteurs", "id_groupe=" .intval($id_groupe));
		sql_delete("spip_ciag_grpauteurs_gestionnaires", "id_groupe=" .intval($id_groupe));
		sql_delete("spip_ciag_grpauteurs", "id_groupe=" .intval($id_groupe));
	} else {
		spip_log("action_supprimer_groupe_auteur $id_groupe interdit",_LOG_INFO_IMPORTANTE);
	}
}

?>