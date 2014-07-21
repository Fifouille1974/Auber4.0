<?php
/**
 * Plugin citrace : tracer certaines actions
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
 
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/citrace_commun');


function inc_citrace_dist($objet, $id_objet, $action, $commentaire, $id_rubrique=0) {

	$trace = citrace_contenu($objet, $id_objet, $action, $commentaire, $id_rubrique);
	
	return citrace_log($trace);
}

?>