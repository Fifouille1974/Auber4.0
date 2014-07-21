<?php
/**
 * Plugin redacteur valideur
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function cirv_declarer_tables_principales($tables_principales){
	$tables_principales['spip_auteurs']['field']['cistatut'] = "VARCHAR(20) DEFAULT '' NOT NULL";
	return $tables_principales;
}

?>