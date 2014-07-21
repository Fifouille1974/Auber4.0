<?php
/**
 * Plugin cinotif
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('inc/cinotif_commun');


function exec_cinotif_suiviabonnes(){
	
	$interdit = true;
	$id_evenement = intval(_request('id_evenement'));
	$objet = '';
	$id_objet = 0;
	
	
	if ($id_evenement){
		$row = sql_fetsel('*', 'spip_cinotif_evenements', "id_evenement=".$id_evenement);
		if ($row) {
			$objet = $row['objet'];
			$id_objet = intval($row['id_objet']);
			if (autoriser('modifier', $objet, $id_objet))
				$interdit = false;
		}		
	}

	if (autoriser('configurer'))
		$interdit = false;
	
	if ($interdit) {
		include_spip('inc/minipres');
		echo minipres();
	} else {

		$contexte = array();
		$grostitre = _T('cinotif:suivi_abonnes');
		$titre = $grostitre;
		$complement = '';
		if ($id_evenement) {
			$contexte['id_evenement'] = $id_evenement;
			$grostitre = _T('cinotif:abonnes');
			$titre = _T('cinotif:abonnes_a');
			$complement = '<input type="hidden" name="id_evenement" value="'.$id_evenement.'" />';
		}
		if ($ciretour = _request('ciretour')) {
			if ($objet=="rubrique"){
				$complement .= '<input type="hidden" name="ciretour" value="'.$objet.'-'.$id_objet.'" />';
				$row = sql_fetsel("titre", "spip_rubriques", "id_rubrique=".$id_objet);
				if ($row) 
					$grostitre = typo($row['titre']);

				$retour_url = generer_url_ecrire("cinotif_suiviabonnements_rubrique","id_rubrique=".$id_objet);
				$contexte['redirect'] = $retour_url;
				if (spip_version()>=3)
					$contexte['icone_retour'] = icone_verticale(_T('icone_retour'), $retour_url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_left']);
				else
					$contexte['icone_retour'] = icone_inline(_T('icone_retour'), $retour_url, "secteur-24.gif", "rien.gif",$GLOBALS['spip_lang_left']);
			}		
		}

		$contexte['nb_max_abonnes'] = cinotif_nb_max_abonnes();
		$contexte['nb_abonnes'] = sql_countsel("spip_cinotif_abonnes");
		$contexte['email'] = '';
		$contexte['abonnes_non_confirmes'] = cinotif_abonnes_non_confirmes();
		$safe = '';

		if ($cherche = _request('recherche')) {
			$t = trim($cherche);
			// supprimer les accents
			include_spip('inc/charsets');
			$tsa = translitteration($t);
		
			// interdire les caracteres dangereux
			// limiter à a-zA-Z0-9 et aux underscores, tirets, points, arobases
			$tableau = array(".","@","-","_");
			$tscs = str_replace($tableau,'',$tsa);		
			if (!ctype_alnum($tscs)) {
				$verif = false;
	
				// passer le cas echeant en iso pour avoir la meme longueur avec ou sans accent en utf-8	
				if ($GLOBALS['meta']['charset'] != 'iso-8859-1')
					$tiso = iconv(strtoupper($GLOBALS['meta']['charset']), "ISO-8859-1", $t);
				else
					$tiso = $t;
	
				// enlever les caracteres speciaux
				$longueur = strlen($tsa);
			    for ($i = 0; $i < $longueur; $i++){
			    	if (ctype_alnum($tsa[$i]) OR in_array($tsa[$i], $tableau))
			    		$safe .= $tiso[$i];
			    	else
			        	$safe .= ' ';
			    }
				// repasser le cas echeant dans le charset du site
				if ($GLOBALS['meta']['charset'] != 'iso-8859-1')
					$safe = iconv("ISO-8859-1", strtoupper($GLOBALS['meta']['charset']), $safe);
			    
			} else {
				$safe = $t;
			}
	
		    // supprimer les espaces doubles
		    $safe = trim(preg_replace ( '~\s{2,}~' , ' ' , $safe )); 
		    
			$contexte['email'] = $safe;	    
		}		
		
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page(_T('titre_page_configuration'), "configuration", "configuration");
		
		if (spip_version()<3) {
			echo "<br />\n";
			echo gros_titre($grostitre,'', false);
		}
		
		echo debut_gauche('', true);
		if (autoriser('configurer') AND !_request('ciretour')){
			$cinotif_navigation = charger_fonction('cinotif_navigation', 'configuration');
		  	echo $cinotif_navigation();
		}
		echo creer_colonne_droite('', true);
		echo debut_droite('', true);

		echo formulaire_recherche("cinotif_suiviabonnes",$complement);

		echo "<br />\n";
		if (spip_version()>=3)
			echo gros_titre($grostitre,'', false);

		if ($safe) {
			echo "<br />\n";
			echo "<h3>". _T('info_resultat_recherche')." &laquo;$safe&raquo;</h3>";
		}

		echo debut_cadre_relief('',true,'',$titre);
		if ($id_evenement)
			echo recuperer_fond("prive/objets/liste/cinotif_suiviabonnes_evenement", $contexte);
		else
			echo recuperer_fond("prive/objets/liste/cinotif_suiviabonnes", $contexte);

		echo fin_cadre_relief(true);
		
		echo  "<br />\n";		
		echo fin_gauche(), fin_page();
	}
}

function cinotif_abonnes_non_confirmes(){
	$abonnes = array();
	
	$result = sql_select("id_abonne", "spip_cinotif_abonnements", "statut='prop'","id_abonne");
	while ($row = sql_fetch($result))
		$abonnes[] = $row['id_abonne'];
		
	return $abonnes;
}

?>