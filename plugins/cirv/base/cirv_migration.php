<?php
/**
 * Plugin Acces restreints
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;



/**
 * Fonction de migration du statut de redacteur valideur
 */
function cirv_migration_info_auteurs() {
	
	$result = sql_select("id_auteur", "spip_auteurs", "statut='ciredval'","","");		

	while ($row = sql_fetch($result))	{
		$id_auteur = $row['id_auteur'];
		sql_updateq("spip_auteurs", array("statut" => "1comite", "cistatut" => "ciredval"), "id_auteur=$id_auteur");
	}
	return true;	
}

?>