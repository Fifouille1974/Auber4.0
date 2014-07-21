<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */
include_spip('inc/ciparam_inc_commun');

function formulaires_editer_ciparam_raccourci_syndic_charger($type,$id){
	
	if (!autoriser('modifier','site',$id))
		return false;
	
	$valeurs = array();
	$table = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);
	$champ = "raccourci";
	$valeurs[$id_table_objet] = intval($id);

	$valeurs['_liste_raccourci_syndic'] = ciparam_liste_raccourci($table,$id_table_objet,$id,$champ);
	$valeurs['_choix_raccourci_syndic'] = ciparam_choix_raccourci($table,$id_table_objet,$id,$champ);

	if (!$valeurs['_choix_raccourci_syndic'])
		$valeurs['masquer'] = "oui";
	else	
		$valeurs['masquer'] = "non";

	$valeurs['_hidden'] = "<input type='hidden' name='$id_table_objet' value='$id' />";

	return $valeurs;
}

function formulaires_editer_ciparam_raccourci_syndic_traiter($type,$id){
	$valeurs = array();
	$table_objet_sql = table_objet_sql($type);
	$id_table_objet = id_table_objet($type);

	if (spip_version()>=3) {
		$supprimer = _request('supprimer_lien');
		if ($supprimer){
			foreach($supprimer as $k=>$v) {
				if (preg_match(",^\w+-[\w*]+-[\w*]+-[\w*]+,",$k)){
					$lien = explode("-",$k);
					list($objet_source,$ids,$objet_lie,$idl) = $lien;
					if ($type AND $id AND $objet_lie=='raccourci' AND $raccourci=$idl) {
					  // desassocier un des raccourcis
						sql_delete("spip_ci_raccourcis_syndic", "$id_table_objet=$id AND raccourci='$raccourci'");
					}
				}
			}
			// Invalider les caches	
			include_spip('inc/invalideur');
			suivre_invalideur("id='$type/$id'");			
		}
	}	

	ciparam_ajout_raccourci($type,"spip_ci_raccourcis_syndic",$id_table_objet,$id,'raccourci',_request('raccourci'));
	
	return array('message_ok'=>'','editable'=>true);
}

?>