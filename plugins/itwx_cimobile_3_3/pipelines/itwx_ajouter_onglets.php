<?php

/* --------------------------------- */
/*  Squelettes mobiles iTwX Mobile	 */
/*  (c) 2011 iTwX Design			 */
/*  Licence GPL 3					 */
/* --------------------------------- */
 

function itwx_ajouter_onglets ($flux) {

	include_spip('inc/urls');
	include_spip('inc/utils');

	global $connect_statut
		, $connect_toutes_rubriques
		;

	if($connect_statut 	&& $connect_toutes_rubriques) {
		if ($flux['args'] == 'configuration') {
			$flux['data']['itwx'] = new Bouton(_DIR_PLUGIN_ITWX."prive/img/itwx_25.png", _T("itwx:config_titre"), generer_url_ecrire('cfg&cfg=itwx'));
		}
	}

	return ($flux);
}

?>
