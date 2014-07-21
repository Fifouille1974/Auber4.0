<?php
/**
 * Plugin ciparam
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function formulaires_editer_ciparam_rang_rubrique_charger($id_rubrique){
	
	$id_rubrique = intval($id_rubrique);

	if (!autoriser('modifier','rubrique',$id_rubrique))
		return false;

	$valeurs = array();
	$valeurs['id_rubrique'] = $id_rubrique;

	$row = sql_fetsel("cirangse", "spip_rubriques", "id_rubrique=".$id_rubrique,"","");	
	if ($row) 
		$valeurs['_cirangse'] = $row['cirangse'];
	
	$valeurs['masquer'] = "non";				
	$valeurs['_hidden'] = "<input type='hidden' name='id_rubrique' value='$id_rubrique' />";

	return $valeurs;
}

function formulaires_editer_ciparam_rang_rubrique_verifier($id_rubrique){
	$erreurs = array();

	return $erreurs;	
}

function formulaires_editer_ciparam_rang_rubrique_traiter($id_rubrique){

	sql_updateq("spip_rubriques", array("cirangse" => intval(_request('cirangse'))), "id_rubrique=".intval($id_rubrique));
		
	return array('message_ok'=>'','editable'=>true);
}

?>