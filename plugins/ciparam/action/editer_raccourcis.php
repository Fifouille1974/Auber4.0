<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/filtres');


// http://doc.spip.org/@action_editer_raccourcis_dist
function action_editer_raccourcis_dist() {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	// exemple : 99,_pied,articles,id_article,article
	$r = explode(",",$arg);

	action_editer_raccourcis_post($r);
}

// http://doc.spip.org/@action_editer_raccourcis_post
function action_editer_raccourcis_post($r) {
	$redirect = _request('redirect');

	if (spip_version()>=3){
		$arg = explode("-",$r);
		//$objet_source,$ids,$objet_lie,$idl
		list($objet, $id_objet, $objet_lie, $raccourci) = $arg;
		$table = table_objet_sql($objet);
		$table_id = id_table_objet($objet);
	} else {
		list($id_objet, $raccourci, $table, $table_id, $objet) = $r;
	}

	if ($raccourci) {
		if ($objet){ 
			// desassocier un/des raccourci d'un objet precis
			sql_delete("spip_ci_raccourcis_$table", "$table_id=$id_objet AND raccourci='$raccourci'");

			// Invalider les caches	
			include_spip('inc/invalideur');
			if (spip_version()>=3)
				suivre_invalideur("id='$objet/$id_objet'");
			else
				suivre_invalideur("id='$table_id/$id_objet'");
		}
	}

	$redirect = rawurldecode($redirect);
	    
	redirige_par_entete($redirect);
}

?>