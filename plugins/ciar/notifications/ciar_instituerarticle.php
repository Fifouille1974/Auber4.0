<?php
/**
 * Plugin Acces restreints Giseh 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined('_ECRIRE_INC_VERSION')) return;


// Fonction appelee par divers pipelines
// http://doc.spip.org/@notifications_instituerarticle_dist
function notifications_instituerarticle($quoi, $id_article, $options) {

	if (defined('_DIR_PLUGIN_CIMS')){
		include_spip('cims_fonctions');
		$row = sql_fetsel("*", "spip_articles", "id_article=".intval($id_article)." AND id_rubrique IN (".implode(",",cims_tableau_rubriques_du_site()).")");
		if (!$row)
			return;
	}
	
	// ne devrait jamais se produire
	if ($options['statut'] == $options['statut_ancien']) {
		spip_log("statut inchange",'notifications');
		return;
	}
	
	$article_ec = false;
	$mails_membres_ec = array();
	if (defined('_DIR_PLUGIN_CIAR')){
		include_spip('ciar_fonctions');
		$article_ec = ciar_article_ec($id_article);
		if ($article_ec) {
			$mails_membres_ec = ciar_article_mails_membres_ec($id_article);	
			$mails_membres_ec = array_map('strtolower', $mails_membres_ec);
		}
	}

	include_spip('inc/texte');

	$modele = "";
	if ($options['statut'] == 'publie') {
		if ($GLOBALS['meta']["post_dates"]=='non'
			AND strtotime($options['date'])>time())
			$modele = "notifications/article_valide";
		else
			$modele = "notifications/article_publie";
	}

	if ($options['statut'] == 'prop' AND $options['statut_ancien'] != 'publie')
		$modele = "notifications/article_propose";

	if ($modele){
		$destinataires = array();
		if ($GLOBALS['meta']["suivi_edito"] == "oui")
			$destinataires = explode(',',$GLOBALS['meta']["adresse_suivi"]);


		$destinataires = pipeline('notifications_destinataires',
			array(
				'args'=>array('quoi'=>$quoi,'id'=>$id_article,'options'=>$options)
			,
				'data'=>$destinataires)
		);

		// en cas d'ec, ne prendre que les destinataires qui sont membres de l'EC
		if ($article_ec)
			$destinataires = array_intersect(array_map('strtolower', $destinataires),array_map('strtolower', $mails_membres_ec));

		$texte = email_notification_article($id_article, $modele);
		notifications_envoyer_mails($destinataires, $texte);
	}
}

?>