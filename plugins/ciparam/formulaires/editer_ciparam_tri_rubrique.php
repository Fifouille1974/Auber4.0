<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
include_spip('inc/ciparam_inc_commun');

function formulaires_editer_ciparam_tri_rubrique_charger($type,$id){
	$valeurs = array();
	$table = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);
	$champ = "citri";
	$valeurs[$id_table_objet] = intval($id);

	if (!autoriser('modifier', 'rubrique', $id))
		return false;	
	
	$valeurs['_choix_tri_rubrique'] = ciparam_choix_tri($table,$id_table_objet,$id,$champ);

	if (!$valeurs['_choix_tri_rubrique'])
		$valeurs['masquer'] = "oui";
	else	
		$valeurs['masquer'] = "non";
	
	$valeurs['_hidden'] = "<input type='hidden' name='$id_table_objet' value='$id' />";

	return $valeurs;
}

function formulaires_editer_ciparam_tri_rubrique_traiter($type,$id){
	$valeurs = array();
	$table_objet_sql = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);

	ciparam_maj_tri("spip_rubriques",$id_table_objet,$id,'citri',_request('citri'));
	
	return array('message_ok'=>'','editable'=>true);
}

?>