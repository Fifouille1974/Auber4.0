<?php
/**
 * Plugin cilien
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/actions');
include_spip('inc/cilien');

function exec_ciliengerer(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		if (spip_version()<3) {
			echo "<br />\n";
			echo gros_titre(_T('cilien:recenser_afficher_liens'),'', false);
		}
		
		echo debut_gauche("",true);
		echo debut_droite("",true);

		if (spip_version()>=3){
			echo '<div class="formulaire_spip formulaire_signature ajax">';
			echo '<h3 class="titrem">'._T('cilien:recenser_afficher_liens').'</h3>';
			
		} else {
			echo debut_cadre_trait_couleur("site-24.gif", true, "", _T('cilien:recenser_afficher_liens'));
		}		
			
		echo cilien_liste_tranches();

		if (spip_version()>=3)
			echo '</div>';
		else
			echo fin_cadre_trait_couleur(true);
		
		
		echo fin_gauche(), fin_page();
	}
}


function cilien_liste_tranches() {
	$traiter_auto = '';
	$traiter_manuel = false;

	// si demande explicite lancer un traitement
	if (_request('tache') AND _request('tache')=='recenser'){
		$traiter_auto = 'grandpas'; // grand pas
	} else {
		// si pas de demande explicite
		// lancer un traitement uniquement si avancement < 100 (et avancement <> 0)
		$avancements = cilien_avancement();
		foreach ($avancements AS $objet=>$avancement)
			if 	($avancement<100 AND $avancement>0)
				$traiter_auto = 'pas'; // un pas normal
	}

	if ($traiter_auto)
		cilien_traite_tranche($traiter_auto);
	
	$avancements = cilien_avancement();

	foreach ($avancements AS $objet=>$avancement){
		$texte .= "<tr><td>".$objet."</td><td>".round($avancement,2)." %</td></tr>\n";
		if 	($avancement<100)
			$traiter_manuel = true;
	}
	
    if ($texte) {
    	if ($traiter_manuel){
			// Avertissement en cas de recensement incomplet
			if (spip_version()>=3){
				$return .= '<div class="notice">';
				$return .= "<p align='center'><b>"._T('avis_attention')."</b></p><P align='justify'>";
				$return .= _T('cilien:texte_recenser_liens_pasok');
				$return .= "</p>";
				$return .= '</div>';
			} else {
				$return .= debut_cadre_trait_couleur("", true, "", "");			
				$return .= "<p align='center'><b>"._T('avis_attention')."</b></p><P align='justify'>"
				. http_img_pack('warning.gif', _T('info_avertissement'), "style='width: 48px; height: 48px; float: right;margin: 10px;'");
				$return .= _T('cilien:texte_recenser_liens_pasok');
				$return .= "</p>";
				$return .= fin_cadre_trait_couleur(true);
			}
    	}

		$return .= debut_cadre_relief("", true, "", _T('cilien:recenser_liens'));
		
		if ($traiter_manuel){
			$return .= "\n<p style='text-align: justify;'>"._T('cilien:texte_avancement_recensement')."</p>";
		} else {		
			if ($traiter_auto)
				$return .= "\n<p style='text-align: justify;'>"._T('cilien:texte_recenser_liens_okauto')."</p>";
			else
				$return .= "\n<p style='text-align: justify;'>"._T('cilien:texte_recenser_liens_ok')."</p>";
		}
		$return .= '<table cellspacing="0" cellpadding="2" style="width: 100%; border: 0px none;" class="arial2"><tbody>
		<tr style="background-color:#eee;">
		<th style="width: 30%;">'._T('cilien:objet').'</th><th style="width: 30%;">'._T('cilien:enregistrements_traites').'</th>
		</tr>'
	    . $texte
		.'</tbody></table>';
			
		if ($traiter_manuel)
			$return .= '<div style="text-align: right"><a href="'.generer_url_ecrire("ciliengerer","tache=recenser").'"><input type="submit" value="'._T('cilien:recenser_liens').'"></a></div>';

		$return .= fin_cadre_relief(true);
		
		$return .= debut_cadre_relief("", true, "", _T('cilien:afficher_liens'));
		if ($traiter_manuel){
			$return .= "\n<p style='text-align: justify;'>"._T('cilien:texte_afficher_liens_pasok')."</p>";
		} else {
			$return .= "\n<p style='text-align: justify;'>"._T('cilien:texte_afficher_liens_ok')."</p>";
			$return .= '<div style="text-align: right"><a href="'.generer_url_public('cilien').'"><input type="submit" value="Afficher les liens"></a></div>';
		}

		$return .= fin_cadre_relief(true);
    }

	return $return;  
}	


?>