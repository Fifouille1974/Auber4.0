<?php
/**
 * Plugin Groupes d'auteurs 
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


function exec_ciag_groupes_auteurs(){
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		
		echo debut_gauche('', true);
		$ciag_navigation = charger_fonction('ciag_navigation', 'configuration');
	  	echo $ciag_navigation();
		
		if (autoriser('creer','groupeauteur')) {
			if (spip_version()>=3) {
				$res = icone_horizontale(_T('ciag:titre_creer_groupe'), generer_url_ecrire("ciag_groupe_auteurs","new=oui"), "groupe_mots-24.png", "creer.gif", $spip_lang_left);
			} else {
				$res = icone_horizontale(_T('ciag:titre_creer_groupe_auteurs'), generer_url_ecrire("ciag_groupe_auteurs","new=oui"), "groupe-mot-24.gif", "creer.gif",false);
			}			
			echo bloc_des_raccourcis($res);
		}
	
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		
		echo gros_titre(_T('ciag:titre_groupes_auteurs_tous'),'', false);
		echo '<div class="cadre">'.typo(_T('ciag:info_groupes_auteurs')).'</div>';
		
		echo debut_cadre_relief('',true,'',_T('ciag:titre_groupes_auteurs'));
		
		if (spip_version()>=3)
			$icone_retour = icone_verticale(_T('icone_retour'), generer_url_ecrire("ciag_groupes_auteurs","id_groupe=$id_groupe"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
		else
			$icone_retour = icone_inline(_T('icone_retour'), generer_url_ecrire("ciag_groupes_auteurs","id_groupe=$id_groupe"), "redacteurs-48.png", "rien.gif",$GLOBALS['spip_lang_left']);
		
		$contexte = array(
			'icone_retour'=>$icone_retour,
			'titre'=>$type,
			'redirect'=>generer_url_ecrire("ciag_groupes_auteurs"),
			'new'=> '',
			'config_fonc'=>''
		);
	
		echo recuperer_fond("prive/objets/liste/ciag_groupes_auteurs", $contexte);

		echo fin_cadre_relief(true);
		
		if (autoriser('creer','groupeauteur')){
			echo "\n<table cellpadding='0' cellspacing='0' border='0' width='100%'>";
			echo "<tr>";
			echo "<td width='10%'>";
			if (spip_version()>=3) {
				echo icone_verticale(_T('ciag:titre_creer_groupe'), generer_url_ecrire("ciag_groupe_auteurs","new=oui"), "groupe_mots-24.png", "creer.gif", $spip_lang_left);
			} else {
				echo icone_inline(_T('ciag:titre_creer_groupe'), generer_url_ecrire("ciag_groupe_auteurs","new=oui"), "groupe-mot-24.gif", "creer.gif", $spip_lang_left);
			}
			echo "</td><td width='90%'>";
			echo "</td></tr></table>";
		}
		
		echo '<br /><br /><br /><div style="background-color: #FFFFFF; border: 1px solid #999999; font-size: 11px; font-family: Arial,Sans,sans-serif; margin: 20px 0 10px; position: relative; padding: 10px;">';
		echo http_img_pack("fiche-perso-24.gif", "", " class='cadre-icone'");
		echo '<div>'.typo(_T('ciag:info_groupes_auteurs')).'</div>'; 
		echo '<div>'.typo(_T('ciag:info2_groupes_auteurs')).'</div>'; 
		if (defined('_DIR_PLUGIN_CIAR')) {
			echo '<div>'.typo(_T('ciag:info_ciar_groupes_auteurs')).'</div>'; 
			echo '<div>'.typo(_T('ciag:info2_ciar_groupes_auteurs')).'</div>'; 
		}
		echo '<div>'.typo(_T('ciag:info3_groupes_auteurs')).'</div>'; 
		echo '<div>'.typo(_T('ciag:info4_groupes_auteurs')).'</div>'; 
		echo '<div>'.typo(_T('ciag:info5_groupes_auteurs')).'</div>'; 
		echo '</div>';
	
		echo fin_gauche(), fin_page();
}

?>