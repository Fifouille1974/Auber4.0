<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
include_spip('inc/ciparam_inc_commun');

function formulaires_editer_ciparam_forme_article_charger($type,$id){
	
	if (!autoriser('modifier','article',$id))
		return false;

	$valeurs = array();
	$table = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);
	$champ = "ciforme";
	$valeurs[$id_table_objet] = intval($id);

	$valeurs['_choix_forme_article'] = ciparam_choix_forme($table,$id_table_objet,$id,$champ);
	
	if (!$valeurs['_choix_forme_article'])
		$valeurs['masquer'] = "oui";
	else	
		$valeurs['masquer'] = "non";
	
	$valeurs['_hidden'] = "<input type='hidden' name='$id_table_objet' value='$id' />";

	return $valeurs;
}

function formulaires_editer_ciparam_forme_article_traiter($type,$id){
	$valeurs = array();
	$table_objet_sql = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);

	ciparam_maj_forme($type,"spip_articles",$id_table_objet,$id,'ciforme',_request('ciforme'));
	
	return array('message_ok'=>'','editable'=>true);
}

?>