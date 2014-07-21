<?php
/**
 * Plugin CINOTIF
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');


function exec_cinotif_config_aide(){

	if (!autoriser('configurer', 'configuration')) {
		include_spip('inc/minipres');
		echo minipres();
	} else {
	
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		if (spip_version()<3) {
			echo "<br />\n";
			echo gros_titre(_T('cinotif:titre'),'', false);
		}
		
		echo debut_gauche('', true);
		$cinotif_navigation = charger_fonction('cinotif_navigation', 'configuration');
	  	echo $cinotif_navigation();
	
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);
		
		if (spip_version()>=3) {
			echo "<br />\n";
			echo gros_titre(_T('cinotif:titre'),'', false);
		}
		
		if (spip_version()>=3) {
			echo '<div class="formulaire_spip">';
			echo '<h3 class="titrem">'._T('cinotif:config_titre_aide').'</h3>';
			echo '<fieldset><legend>'._T('cinotif:config_aide').'</legend>';
		} else {
			echo '<div class="cadre cadre-formulaire-editer"><div class="entete-formulaire"><h1>';
			echo _T('cinotif:config_titre_aide');
			echo '</h1></div>';
			
			echo '<div class="formulaire_spip"><div class="cadre cadre-r"><div class="cadre_padding">';
			echo _T('cinotif:config_aide').'<br/>';
		}
		
		$tableau = '<table summary="'._T('cinotif:config_aide').'" border=1 cellspacing=0 cellpadding=2 width="100%">
					<thead style="line-height: normal;">
					<tr class="titrem">
					<th id="col1" width="70%">'._T('cinotif:config_aide_gh').'</th>
					<th id="col2" width="30%">'._T('cinotif:config_aide_dh').'</th>
					</tr>
					</thead>
					<tbody>';
		
		for ($i=2;$i<=8; $i++)
			$tableau .= '<tr><td headers="col1">'._T('cinotif:config_aide_g'.$i).'</td>
							<td headers="col2">'._T('cinotif:config_aide_d'.$i).'</td></tr>';
					
		$tableau .= '</tbody></table>';
		
		echo $tableau.'<br/>';
		echo _T('cinotif:config_aide_legend');
		
		if (spip_version()>=3)
			echo '</fieldset>';
		else
			echo '</div></div></div>';

		echo '</div>';
		
		echo fin_gauche();
		echo fin_page();
	}
}

?>