<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Liste des types de documents ajoutés par ciparam
 *
 */
function ciparam_type_document() {
	return array(
		array('stw','STW','non','application/vnd.sun.xml.writer.template'),
		array('stc','STC','non','application/vnd.sun.xml.calc.template'),
		array('sti','STI','non','application/vnd.sun.xml.impress.template')
	);
}


?>