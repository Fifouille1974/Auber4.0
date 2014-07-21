<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

function formulaires_editer_ciar_cioption_charger($type,$id){
	$id = intval($id);
	
	if (!autoriser('configurer', 'configuration'))
		return false;

	if (!$id)
		return false;
		
	$row = sql_fetsel("*", "spip_auteurs", "id_auteur=$id","","");	
	$statut = $row['statut'];
	$cioption = $row['cioption'];
	
 	if (count(liste_rubriques_auteur($id))>0)
	 	$auteur_restreint = 'oui';
	else	
	 	$auteur_restreint = 'non';

	if (!$cioption) {
	 	if ($auteur_restreint == 'oui')
			$cioption = 'ecadminrestreint';
	 	if ($auteur_restreint == 'non')
			$cioption = 'ecadminsite';
			
		// mettre une option par defaut car certains ne valident pas le formulaire	
		if ($statut=='0minirezo')
			sql_updateq("spip_auteurs", array('cioption'=>$cioption), "id_auteur=$id");
	}
 	
	if ($statut=='0minirezo') {
		$valeurs = array();
		$valeurs['id_auteur'] = intval($id);
		$valeurs['auteur_restreint'] = $auteur_restreint;
		$valeurs['name'] = $cioption;
		$valeurs['_hidden'] = "<input type='hidden' name='id_auteur' value='$id' />";
	} else {
		$valeurs = false;
	}

	return $valeurs;
}


function formulaires_editer_ciar_cioption_traiter($type,$id){
	
	$cioption = _request('cioption');
	if (in_array($cioption,array('eccma','ecadminsite','ecadminrestreint','non'))) {
		if ($cioption=='non')
			$cioption = '';
		sql_updateq("spip_auteurs", array('cioption'=>$cioption), "id_auteur=$id");
	}
	
	return array('message_ok'=>'');
}

?>