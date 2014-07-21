<?php
/**
 * Plugin redacteur valideur
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function formulaires_editer_cirv_objet_charger($type,$id){
	$id = intval($id);
	$id_table_objet = id_table_objet($type);
	
	if (!autoriser('modifier', 'auteur', $id_auteur))
		return false;
	
	$row = sql_fetsel("*", "spip_auteurs", "id_auteur=$id","","");	
	$statut = $row['statut'];
	$cistatut = $row['cistatut'];
	if (!$cistatut)
		$cistatut = 'non';
	
	if ($statut=='1comite') {
		$valeurs = array();
		$valeurs['editable'] = false;
		$valeurs['cistatut'] = false;
		$valeurs[$id_table_objet] = intval($id);
		$valeurs['name'] = $cistatut;
		$valeurs['_hidden'] = "<input type='hidden' name='id_auteur' value='$id' />";
		$valeurs['_cirvt'] = '';
		if (!defined('_CIRV_PAS_CIREDVALTOUT'))
			$valeurs['_cirvt'] = 'oui';		
	} else {
		$valeurs = false;
	}

	return $valeurs;
}

function formulaires_editer_cirv_objet_verifier($type,$id){
	return $erreurs;
}

function formulaires_editer_cirv_objet_traiter($type,$id){

	$cistatut = _request('cistatut');
	if (in_array($cistatut,array('ciredval','ciredvaltout','non'))) {
		if ($cistatut=='non')
			$cistatut = '';
		sql_updateq("spip_auteurs", array('cistatut'=>$cistatut), "id_auteur=$id");
	}
	
	return array('message_ok'=>'');
}

?>