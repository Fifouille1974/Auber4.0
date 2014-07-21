<?php
/**
 * Plugin Configurateur de squelettes
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

include_spip('inc/ciparam_inc_meta');


/*-----------------------------------------------------------------
// Balises supplémentaires
// SPIP permet de les placer dans le fichier ..._fonctions.php
------------------------------------------------------------------*/

function balise_CIPARAM_MOTCLE_ACCUEIL_dist($p) {
	$p->code = "ciparam_lire_meta('ci_motcle_accueil')";
	$p->statut = 'html';
	return $p;
}

function balise_CIPARAM_NOM_BANDEAU_dist($p) {
	$p->code = "ciparam_lire_meta('ci_nom_bandeau')";
	$p->statut = 'html';
	return $p;
}

function balise_CIPARAM_ABONNEMENT_XITI_dist($p) {
	$p->code = "ciparam_lire_meta('ci_abonnement_xiti')";
	$p->statut = 'html';
	return $p;
}

function balise_CIPARAM_MAINTENANT_dist($p) {
	$p->code = 'date("Y-m-d")';
	$p->statut = 'php';
	return $p;
}

function balise_CIPARAM_AFFICHER_dist($p) {
	$arg = interprete_argument_balise(1,$p);
	if (!$arg) {
		$p->code = "'oui'";
	} else 
	  $p->code = 'ciparam_afficher(' . $arg .')';
	  
	$p->statut = 'html';
	return $p;
}

function balise_CIPARAM_IMAGE_BANDEAU_dist($p) {
	$p->code = "ciparam_image_bandeau()";
	$p->statut = 'html';
	return $p;
}

function balise_CIPARAM_XITI_EMAIL_dist($p) {
	$p->code = "ciparam_xtor_email()";
	$p->statut = 'html';
	return $p;
}

function balise_CIPARAM_XITI_RSS_dist($p) {
	$p->code = "ciparam_xtor_rss()";
	$p->statut = 'html';
	return $p;
}

/*-----------------------------------------------------------------
// Critères propres à ciparam
------------------------------------------------------------------*/

function critere_ciparam_nombreactu($idb, &$boucles, $crit) {
	$boucle = &$boucles[$idb];
	// respecter la syntaxe des cotes et double cotes
 	$boucle->limit = '"0," . intval(ciparam_lire_meta("ci_nbactu"))';	  
}


/*-----------------------------------------------------------------
// Filtres propres à ciparam
------------------------------------------------------------------*/

// Ne pas mettre des accents dans un mailto, etc.
function ciparam_filtre_mailto($texte,$charset='utf-8'){

	$texte = str_replace(array("&#8217;","&#8220;","&#8221;"),array(" ","",""),$texte);
	$texte = str_replace(array("&mdash;", "&endash;"),array("--","-"),$texte);

	$texte = htmlentities($texte, ENT_NOQUOTES, $charset);
	$texte = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml)\;#', '\1', $texte);
	$texte = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $texte);
	$texte = preg_replace('#\&[^;]+\;#', ' ', $texte);
	$texte = str_replace('nbsp;', ' ', $texte);
	$texte = str_replace("'", " ", $texte);
	$texte = str_replace('"', '', $texte);

	return $texte;
}

// Sommaire automatique d'un article
// Source : http://www.uzine.net/spip_contrib/article.php3?id_article=76
function ciparam_sommaire_article($texte) {
	
	preg_match_all("|<h3 class=\"spip\">(.*)</h3>|U", $texte, $regs);
	
	$texte='';
	$texte.='<form name="accestitre" action="Javascript:goToSelectURL(\'accestitre\',\'nom_titre\');" method="get" >';
	$texte.='<select name="nom_titre" id="nom_titre">';
	$texte.='<option value="" selected="selected">'._T('ciparam:acces_direct').'</option>';
	
	$nb=1;
	
	for($j=0;$j<count($regs[1]);$j++)
	{
	        $item = $regs[1][$j];
	        // met des espaces insécables
	        $item = str_replace(" ","&nbsp;",$item);
	    	$texte=$texte.'<option value="#sommaire_'.$nb.'">'.$item.'</option>';
			$nb++;
	}
	$texte.='</select>';
	$texte.='<input type="submit" name="masquable" value="OK"></input>';
	$texte.='</form>';

	    
    // Ajout CI pour éviter la duplication des notes de bas de page
	$GLOBALS["les_notes"] = "";
	$GLOBALS["compt_note"] = 0;
	$GLOBALS["marqueur_notes"] = 0;

    return $texte;
}

function ciparam_sommaire_article2($texte){
	preg_match_all("|<h3 class=\"spip\">(.*)</h3>|U", $texte, $regs);
	
	$texte='';
	$texte.='<form name="accestitre2" action="Javascript:goToSelectURL(\'accestitre2\',\'nom_titre2\');" method="get" >';
	$texte.='<select name="nom_titre2" id="nom_titre2">';
	$texte.='<option value="" selected="selected">'._T('ciparam:acces_direct').'</option>';
	
	$nb=1;
	
	for($j=0;$j<count($regs[1]);$j++)	{
	        $item = $regs[1][$j];
	        // met des espaces insécables
	        $item = str_replace(" ","&nbsp;",$item);
	    	$texte=$texte.'<option value="#sommaire_'.$nb.'">'.$item.'</option>';
			$nb++;
	}
	$texte.='</select>';
	$texte.='<input type="submit" value="OK"></input>';
	$texte.='</form>';

	    
    // Ajout CI pour éviter la duplication des notes de bas de page
	$GLOBALS["les_notes"] = "";
	$GLOBALS["compt_note"] = 0;
	$GLOBALS["marqueur_notes"] = 0;

    return $texte;
}

function ciparam_sommaire_ancre($texte){
	$texte = preg_replace("|<h3 class=\"spip\">(.*)</h3>|U","<a name=\"sommaire_#NB_TITRE_DE_MON_ARTICLE#\" href=\"#top\"></a>$0", $texte);
	$texte = str_replace("<h3 class=\"spip\">","",$texte);
	$texte = str_replace("<a name=\"sommaire_","<h3 class=\"spip\"><a name=\"sommaire_",$texte);
	$array = explode("#NB_TITRE_DE_MON_ARTICLE#" , $texte);
	$res =count($array);
	$i =1;
	$texte=$array[0];
	while($i<$res)
	{
		$texte=$texte.$i.$array[$i];
		$i++;
	}
	
	return $texte;
}

function ciparam_texte_sans_http($texte) {
    // supprimer les &nbsp;
    $texte=substr($texte,7);
	return $texte;
}

function ciparam_texte_sans_nbsp($texte) {
    // supprimer les &nbsp;
    $texte=str_replace("&nbsp;?","?",$texte);
	return $texte;
}

function ciparam_texte_export($texte,$charset='') {
    // supprimer les &nbsp;
    $texte=str_replace("&nbsp;"," ",$texte);

    // supprimer les retour à la ligne d'un texte
    $texte=str_replace(CHR(13),"",$texte);
    $texte=str_replace(CHR(10),"",$texte);

    // mettre des URL entières
//    $monurl = $GLOBALS['meta']['adresse_site'];
//    $texte = str_replace("IMG/", $monurl."/IMG/", $texte);

    // charset site cible different
    if ($charset) {
    	if ($charset!=$GLOBALS['meta']['charset']) {
    		if (in_array($charset,array('iso-8859-1','utf-8','utf8'))) {
				include_spip('inc/charsets');
				$texte = unicode2charset(charset2unicode($texte), $charset);
    		}
    	}
    }
    
	return $texte;
}

function ciparam_xtTraiter($nompage) {
	
	if (time() < strtotime("1 January 2013")) {

		// ancien code issu de www.spip-contrib.net/Mesurez-l-audience-de-votre-site
		$nompage = strtolower($nompage);
		$nompage = preg_replace("[àâä]","a",$nompage);
		$nompage = preg_replace("[îï]","i",$nompage);
		$nompage = preg_replace("[ôö]","o",$nompage);
		$nompage = preg_replace("[ùûü]","u",$nompage);
		$nompage = preg_replace("[éèêë]","e",$nompage);
		$nompage = str_replace("Â","a",$nompage);
		$nompage = str_replace("Î","i",$nompage);
		$nompage = str_replace("Ô","o",$nompage);
		$nompage = str_replace("Û","u",$nompage);
		$nompage = str_replace("Ê","e",$nompage);
		$nompage = str_replace("ç","c",$nompage);
		$nompage = preg_replace("[^a-z^A-Z^0-9^\-_:~\\\/]","_",$nompage);

	} else {

		$safe = '';
		// passer en minuscule
		$t = strtolower(trim($nompage));
		include_spip('inc/filtres');	
		$t = filtrer_entites($t);
	
		if ($t) {
			// supprimer les accents
			include_spip('inc/charsets');
			$tsa = translitteration($t);
		
			// Les caracteres autorises par XITI sont les suivants :
			// - les lettres abcdefghijklmnopqrstuvwxyz en majuscules ou minuscules
	    	// - les chiffres 123467890
			// - les points, les slashs, les moins, les underscores, les tildes
			// ajout des deux points pour compatibilite ascendante
			$tableau = array('.','/','-','_','~',':');
			$tscs = str_replace($tableau,'',$tsa);		
			if (!ctype_alnum($tscs)) {
				// enlever les caracteres speciaux (sauf ceux qui sont autorises)
				$longueur = strlen($tsa);
			    for ($i = 0; $i < $longueur; $i++){
			    	if (ctype_alnum($tsa[$i]) OR in_array($tsa[$i], $tableau))
			    		$safe .= $tsa[$i];
			    	else
			        	$safe .= ' ';
			    }
			} else {
				$safe = $tsa;
			}
			
		    // remplacer les espaces par des underscores
		    $nompage = str_replace(' ','_',$safe);		
		}
	}
     
    return($nompage);
}

// Pour les flux RSS
function ciparam_mime_type($type_document) {

	$retour="";
	if ($type_document) {
		$result = sql_select("*", "spip_types_documents", "titre='$type_document'","","");		
		if ($type = sql_fetch($result))	{
			$retour = $type['mime_type'];
		}
	}
	    
	return $retour;
}

function ciparam_antislash($texte) {
    // met des antislash
	$texte=str_replace('/', '\/',$texte);
	return $texte;
}

function ciparam_utf8($texte) {
    // conversion en utf8
	$texte = ciparam_iso_to_utf8($texte); 
	   
	return $texte;
}

// From http://svn.ilias.uni-koeln.de/wsvn/ILIAS3/trunk/include/inc.convertcharset.php
function ciparam_iso_to_utf8($str) {
//    if (extension_loaded("mbstring"))
//        return mb_convert_encoding($str, "UTF-8", "auto");;
    
    for($x=0;$x<strlen($str);$x++) {
        $num=ord(substr($str,$x,1));
      if($num<128)
          $xstr.=chr($num); 
      else if($num<1024)
          $xstr.=chr(($num>>6)+192).chr(($num&63)+128);
      else if($num<32768)
          $xstr.=chr(($num>>12)+240).chr((($num>>6)&63)+128).chr(($num&63)+128);
      else if($num<2097152)
          $xstr.=chr($num>>18+240).chr((($num>>12)&63)+128).chr(($num>>6)&63+128).chr($num&63+128);
    }
    return $xstr;
}

function ciparam_remet_balise($texte) {
	$texte=str_replace('&lt;', '<',$texte);	   
	$texte=str_replace('&gt;', '>',$texte);	   
	return $texte;
}

function ciparam_select_annees($premiere_annee,$derniere_annee) {
	$return = "";

	$premiere_annee = intval($premiere_annee);
	$derniere_annee = intval($derniere_annee);

	if ($premiere_annee AND $derniere_annee AND $derniere_annee >= $premiere_annee) {
		$annee = $derniere_annee;	
		while($annee >= $premiere_annee) {
			$return .= '<option value="'.$annee.'-01-01">'.$annee.'</option>';
			$annee = $annee - 1;
		}
	}
	
	return $return;
}

function ciparam_volumetrie_site($site) {
	$return = "";
	
	// nombre d'auteurs du site (hors visiteurs)
	$return = sql_countsel("spip_auteurs","statut<>'5poubelle' AND statut<>'6forum'","","");
	
	// nombre de rubriques du site
	$return .= "_".sql_countsel("spip_rubriques","","","");
	
	// nombre d'articles du site
	$return .= "_".sql_countsel("spip_articles", "statut<>'poubelle'","","");

	// nombre d'auteurs du site avec au moins un article
	$return .= "_".sql_countsel("spip_auteurs_articles AS art LEFT JOIN spip_auteurs AS lien ON art.id_auteur=lien.id_auteur","statut<>'5poubelle' AND statut<>'6forum'","art.id_auteur","");

	// nombre de documents du site
	$where = "distant='non'";
	if (_request('extension')) {
		$extension = _request('extension');
		$tscs = str_replace('_','',$extension);
		if (ctype_alnum($tscs)) {
			$safe = array();
			$typesdoc = explode('_',$extension);
			if (count($typesdoc)<11) {
				foreach ($typesdoc as $typedoc) {
					if (ctype_alnum($typedoc) AND strlen($typedoc)<8)
						$safe[] = $typedoc;
				}
				if ($safe)
					$where = "distant='non' AND ".sql_in('extension', $safe);
			}
		}
	}
	$return .= "_".sql_countsel("spip_documents", $where,"","");

	// nombre de commentaires publies sur le site public
	$return .= "_".sql_countsel("spip_forum", "statut='publie'","","");

	// nombre de commentaires dans l'expace prive
	$return .= "_".sql_countsel("spip_forum", "statut='prive'","","");

	// nombre d'articles archive
	$return .= "_".sql_countsel("spip_articles", "statut='archive'","","");
	
	return $return;
}

/*-----------------------------------------------------------------
// Filtre pour le plan du site
------------------------------------------------------------------*/

// Construit le plan du site (volumetrie rubrique)
// Renvoi le plan à partir d'un tableau des rubriques concernées
// Chaque ligne du tableau passé en paramètre de la fonction doit comprendre
// #ARRAY{id_parent,#ID_PARENT,id_rubrique,#ID_RUBRIQUE,titre,#TITRE}
function ciparam_plan($citableau){
	global $citexteplan;
	global $ciarboplan;
	
	$ciarboplan = ciparam_arbo_rubriques($citableau);
	$citexteplan = '<ul class="plansite">';
	ciparam_enfantplan(0);
	$citexteplan .= '</ul>';
	
	return $citexteplan;  
}

// Renvoi un  tableau associant chaque rubrique parente à un tableau de ses rubriques filles
// Chaque ligne du tableau passé en paramètre de la fonction doit comprendre
// #ARRAY{id_parent,#ID_PARENT,id_rubrique,#ID_RUBRIQUE,titre,#TITRE}
function ciparam_arbo_rubriques($citableau) {
	$ciarbo = array();
	$cienfants = array();
	$ciparent = 0;
	$row = array();
	
	foreach ($citableau as $row){
		
		if ($ciparent) {
			if ($ciparent!=$row['id_parent']+1) {
					$ciarbo["p".strval($ciparent-1)] = $cienfants;

				$ciparent = $row['id_parent']+1;
				$cienfants = array();
			}
		} else {
			$ciparent = $row['id_parent']+1;
		}
		
		$cienfants[] = array($row['id_rubrique'],$row['titre']);
	}
	// Traiter le dernier lot 
	if ($ciparent) {
		$ciarbo["p".strval($ciparent-1)] = $cienfants;
		$ciparent = $row['id_parent']+1;
		$cienfants = array();
	}

	return $ciarbo;
}

function ciparam_enfantplan($leparent){
	global $id_parent;
	global $id_rubrique;
	static $i = 0;
	global $ciarboplan;
	global $citexteplan;
	
	$i++;
 	$citableau = $ciarboplan["p".strval($leparent)];	
	 	
 	if ($citableau) {
		if ($i > 1)
	 		$citexteplan .= '<ul>';
		
	 	foreach($citableau as $row) {
			$my_rubrique=$row[0];
			$titre=$row[1];
			$ciclass = "";
	
			switch ($i) {
			case 1:
				$ciclass = "plansecteur";
				break;
			case 2:
				$ciclass = "planrubniv1";
				break;
			default:
				$ciclass = "planrub";
				break;
			}
	
 			$citexteplan .= '<li>';
 			
			$titre = interdire_scripts(supprimer_numero(typo(extraire_multi($titre))));
			$url = ciparam_generer_url($my_rubrique,"rubrique");
			$citexteplan .= '<a href="'.$url.'" class="'.$ciclass.'">'.$titre.'</a>';
	 			
			ciparam_enfantplan($my_rubrique);
 			$citexteplan .= '</li>';
		}

		if ($i > 1)
			$citexteplan .= '</ul>';
	
 	}	
	$i=$i-1;
	
	return true;
}


/*-----------------------------------------------------------------
// Filtre pour le menu deroulant
------------------------------------------------------------------*/

// Construit le menu déroulant (volumetrie rubrique)
// Renvoi le  menu déroulant à partir de 3 tableaux des rubriques concernées
// Chaque ligne des tableaux passés en paramètre de la fonction doit comprendre
// #ARRAY{id_parent,#ID_PARENT,id_rubrique,#ID_RUBRIQUE,titre,#TITRE}

function ciparam_menu($cirub_niv1,$cirub_niv2,$cirub_niv3,$variante=''){
	$citextemenu = "";

	// Nombre de niveau paramétrés
   	$ci_menu_niveau = ciparam_lire_meta("ci_menu_niveau");
	if (!$ci_menu_niveau) $ci_menu_niveau = "3";

   	if ($ci_menu_niveau=="2")
		$citextemenu = ciparam_contenu_menu($ci_menu_niveau,$cirub_niv1,$cirub_niv2,'',$variante);
   		
   	if ($ci_menu_niveau=="3")
		$citextemenu = ciparam_contenu_menu($ci_menu_niveau,$cirub_niv1,$cirub_niv2,$cirub_niv3,$variante);

	return $citextemenu;  
}

function ciparam_contenu_menu($ci_menu_niveau,$cirub_niv1,$cirub_niv2,$cirub_niv3,$variante=''){
	$citexte ="";
	$secteur=0;
	$niv3existe = false;
	$cin2n3 = array();


	if ($cirub_niv3 AND is_array($cirub_niv3)) {
		$niv3existe = true;
	 	foreach($cirub_niv3 as $row_niv3) {
	 		$cin2n3[$row_niv3['id_rubrique']] = $row_niv3['id_parent'];
	 	}
	 	reset($cirub_niv3);
	}

	if ($variante=='menu2') {
		// Menu utilisant jquery
		foreach ($cirub_niv2 as $row2) {			
			$rubn1 = $row2['id_parent'];
			$rubn2 = $row2['id_rubrique'];
			$cin2titre = $row2['id_titre'];

			if ($rubn1!=$secteur) {
				if ($secteur!=0) $citexte .='<\/div>';
				$citexte .='<div id="menuRub'.$rubn1.'" class="menu" ">';//onmouseover="menuMouseover(event)
				$secteur=$rubn1;
			}
			if ($niv3existe AND in_array($rubn2,$cin2n3)) {
				$citexte .='<span class="unItem" id="sRub'.$rubn2.'"><a class="menuItem plus" href="'.ciparam_generer_url($rubn2,"rubrique").'" id="Rub'.$rubn2.'">'.texte_script(supprimer_numero(interdire_scripts(typo(trim($cin2titre))))).'<\/a><\/span>';//onmouseover="menuItemMouseover(this,event,\\\'menuRub'.$rubn2.'\\\');"
			} else {
				$citexte .='<span class="unItem"><a class="menuItem" href="'.ciparam_generer_url($rubn2,"rubrique").'">'.texte_script(supprimer_numero(interdire_scripts(typo(trim($cin2titre))))).'<\/a><\/span>';
			}
		}
	} else {					
		// Menu standard
		foreach ($cirub_niv2 as $row2) {
			$rubn1 = $row2['id_parent'];
			$rubn2 = $row2['id_rubrique'];
			$cin2titre = $row2['titre'];
						
			if ($rubn1!=$secteur) {
				if ($secteur!=0) $citexte .='<\/div>';
				$citexte .='<div id="menuRub'.$rubn1.'" class="menu" onmouseover="menuMouseover(event)">';
				$secteur=$rubn1;
			}
			if ($niv3existe AND in_array($rubn2,$cin2n3)) {
				$citexte .='<a class="menuItem" href="'.ciparam_generer_url($rubn2,"rubrique").'"   onmouseover="menuItemMouseover(event,\\\'menuRub'.$rubn2.'\\\');"><span class="menuItemText">'.texte_script(supprimer_numero(interdire_scripts(typo(trim($cin2titre))))).'<\/span><span class="menuItemArrow">&#9654;<\/span><\/a>';
			} else {
				$citexte .='<a class="menuItem" href="'.ciparam_generer_url($rubn2,"rubrique").'">'.texte_script(supprimer_numero(interdire_scripts(typo(trim($cin2titre))))).'<\/a>';
			}
		}
	}		
	if ($citexte) $citexte .='<\/div>';
	
	if ($niv3existe AND is_array($cin2n3)) {
		$citexten3="";
		$rubn1=0;
		reset($cin2n3);					
		foreach ($cirub_niv3 as $row3) {
			$rubn2 = $row3['id_parent'];
			$rubn3 = $row3['id_rubrique'];
			$cin3titre = $row3['titre'];
			
			if ($rubn2!=$rubn1) {
				if ($rubn1!=0) $citexten3 .='<\/div>';
				$citexten3 .='<div id="menuRub'.$rubn2.'" class="menu">';
				$rubn1=$rubn2;
			}
			$citexten3 .='<a class="menuItem" href="'.ciparam_generer_url($rubn3,"rubrique").'">'.texte_script(supprimer_numero(interdire_scripts(typo(trim($cin3titre))))).'<\/a>';			
		}				
		if ($citexten3) $citexten3 .='<\/div>';
		
		$citexte .=$citexten3;
	}		
	
	return $citexte;
}

/*-----------------------------------------------------------------
// Fonctions pour etre compatible avec la navigation en previsualisation
------------------------------------------------------------------*/

function ciparam_generer_url($id,$objet) {
	if (ciparam_exist_cipr()=="oui")
		return vider_url(cipr_preview(generer_url_entite($id,$objet)));
	else
		return vider_url(generer_url_entite($id,$objet));
}

function ciparam_exist_cipr() {
	static $ciExistCipr;
	
	if (!$ciExistCipr) {
		if (function_exists('cipr_preview'))
			$ciExistCipr = "oui";
		else
			$ciExistCipr = "non";
	}

	return $ciExistCipr;
}

/*-----------------------------------------------------------------
// Fonctions relatives aux balises supplémentaires
------------------------------------------------------------------*/

function ciparam_afficher($arg) {
	$return = "oui";
	if ($arg) {
		$valeurs = ciparam_lire_meta("ci_masquer");
		if ($valeurs)
			if (in_array($arg,$valeurs))
				$return = "non";
	}
	return $return;
}

// Reservation pour le multisites
function ciparam_image_bandeau() {
	$nom = "siteon0";
	$formats_logos =  array ('gif', 'jpg', 'png');
	foreach ($formats_logos as $format) {
		if (@file_exists($d = (_DIR_LOGOS . $nom . '.' . $format))) {
			$return = $d;
		}
	}
	
	if (function_exists('cims_image_bandeau'))
		$return = cims_image_bandeau();

	return $return;
}


/*-----------------------------------------------------------------
// Balise pour le doctype
------------------------------------------------------------------*/

function balise_CIPARAM_DOCTYPE_dist($p) {
	$p->code = "ciparam_doctype()";
	$p->statut = 'html';
	return $p;
}

function ciparam_doctype() {
	$lang = $GLOBALS['spip_lang'];
	$lang_dir = lang_dir(changer_typo($lang));
	
	if (defined('_CIPARAM_DOCTYPE') AND _CIPARAM_DOCTYPE=='xhtml')
		$return = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
				.'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$lang.'" lang="'.$lang.'" dir="'.$lang_dir.'">';
	else
		$return = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
				.'<html lang="'.$lang.'" dir="'.$lang_dir.'">';

	return $return;
}


/*-----------------------------------------------------------------
// Fonctions pour Xiti
------------------------------------------------------------------*/
function ciparam_xtor_rss($var=''){
	$return = '';

	// si abonnement xiti
	if ($ci_abonnement_xiti = ciparam_lire_meta('ci_abonnement_xiti')) {
		if (strpos($ci_abonnement_xiti,'_images/void.gif')===false){
			$xiti_rss = intval(ciparam_lire_meta('ci_xiti_rss'));
			
			if (defined('_CI_XITI_RSS') AND intval(_CI_XITI_RSS)>0)
				$xiti_rss = intval(_CI_XITI_RSS);
			
			if ($xiti_rss>0)
				$return = '#xtor=RSS-'.$xiti_rss;
		}
	}

	return $return;
}

function ciparam_xtor_email($var=''){
	$return = '';

	// si abonnement xiti
	if ($ci_abonnement_xiti = ciparam_lire_meta('ci_abonnement_xiti')) {
		if (strpos($ci_abonnement_xiti,'_images/void.gif')===false){
			$xiti_email = intval(ciparam_lire_meta('ci_xiti_email'));
			
			if (defined('_CI_XITI_EMAIL') AND intval(_CI_XITI_EMAIL)>0)
				$xiti_email = intval(_CI_XITI_EMAIL);
			
			if ($xiti_email>0)
				$return = '&xtor=EPR-'.$xiti_email;
		}
	}

	return $return;
}

function ciparam_xtsd($var=''){
	$return = '';
	
	if ($ci_abonnement_xiti = ciparam_lire_meta('ci_abonnement_xiti')) {
		if (strpos($ci_abonnement_xiti,'_images/void.gif')===false){
			$pos = strpos($ci_abonnement_xiti,'.');
			if ($pos!==false)
				$return = substr($ci_abonnement_xiti,0,$pos);
		}
	}

	return $return;
}

function ciparam_xtsite($var=''){
	$return = '';
	
	if ($ci_abonnement_xiti = ciparam_lire_meta('ci_abonnement_xiti')) {
		if (strpos($ci_abonnement_xiti,'_images/void.gif')===false){
			$pos = strpos($ci_abonnement_xiti,'s=');
			if ($pos!==false){
				$return = substr($ci_abonnement_xiti,$pos+2);
				$pos2 = strpos($return,'&');
				if ($pos2!==false)
					$return = substr($return,0,$pos2);
				
			}
		}	
	}

	return $return;
}

function ciparam_xtn2($var=''){
	$return = '';
	
	if ($ci_abonnement_xiti = ciparam_lire_meta('ci_abonnement_xiti')) {
		if (strpos($ci_abonnement_xiti,'_images/void.gif')===false){
			$pos = strpos($ci_abonnement_xiti,'s2=');
			if ($pos!==false){
				$return = substr($ci_abonnement_xiti,$pos+3);
				$pos2 = strpos($texte,'&');
				if ($pos2!==false)
					$return = substr($return,0,$pos2-1);
				
			}
		}
	}

	return $return;
}

function ciparam_xt1($var=''){
	$url = '';
	
	if ($ci_abonnement_xiti = ciparam_lire_meta('ci_abonnement_xiti')) {
		if (strpos($ci_abonnement_xiti,'_images/void.gif')===false){
			$url = strtolower(trim($GLOBALS['meta']['adresse_site']));
			$url = str_replace(array("http://","https://"),"",$url);

			$pos = strpos($url,'.');
			if ($pos!==false)
				$url = substr($url,$pos);
				
			$pos = strpos($url,'/');
			if ($pos!==false)
				$url = substr($url,0,$pos);
				
		}
	}

	return $url;
}

/*-----------------------------------------------------------------
// Sommaire a plat d'un article
// Pour pouvoir utiliser sous SPIP 2.1 le plugin sommaire automatique
------------------------------------------------------------------*/

// Deux filtres
function ciparam_sommaireplat_article($texte,$select=false) {
//	return sommaire_empile_note().ciparam_affiche_sommaire($texte,$select).sommaire_depile_note();
	if (!defined('_DIR_PLUGIN_SOMMAIRE'))
		include_spip('inc/inc_plugin_sommaire');

    // Ajout CI pour éviter la duplication des notes de bas de page
	$GLOBALS["les_notes"] = "";
	$GLOBALS["compt_note"] = 0;
	$GLOBALS["marqueur_notes"] = 0;
		
	return ciparam_affiche_sommaire($texte,$select);
}

function ciparam_sommaireplat_ancre($texte){
	if (!defined('_DIR_PLUGIN_SOMMAIRE'))
		include_spip('inc/inc_plugin_sommaire');
	
	return ciparam_sommaire_post_propre(retire_sommaire($texte), false);
}

function ciparam_affiche_sommaire($texte,$select=false){return ciparam_sommaire_post_propre(retire_sommaire($texte), $ajoute=true, $sommaire_seul=true,$select);}

function ciparam_sommaire_post_propre($texte, $ajoute=true, $sommaire_seul=false,$select=false){
	if (strpos($texte, '<h')!==false)
		$texte = sommaire_filtre_texte_echappe($texte,'ciparam_sommaire_filtre'.($select ? '_select' : ''),'html|code|cadre|frame|script|acronym|cite',array($ajoute,$sommaire_seul));
	elseif($sommaire_seul)
            return "";

	return $texte;
}

function ciparam_sommaire_filtre($texte, $ajoute=true, $sommaire_seul=false){
	$sommaire = sommaire_recenser($texte);

	if ($ajoute OR $sommaire_seul){
		include_spip('inc/texte');
		
		//---- Debut ajout CI pour compatibilite SPIP 2.1 -----
		$debut = '<a name="nav-sommaire"></a><div class="well nav-sommaire nav-sommaire-'.count($sommaire).'">'	
				.'<h2>'._T('ciparam:titre_cadre_sommaire').'</h2>';
		$fin = '</div>';
		
		// le retour a la ligne est indispensable
		foreach ($sommaire AS $ligne){
			$res .= '
-'.str_pad('*',$ligne['niveau'],'*').' ['.$ligne['id'].'<-]['.$ligne['titre'].'->'.$ligne['href'].']';
		}

		$sommaire = "<!--sommaire-->" . $debut . propre($res) .$fin . "<!--/sommaire-->"; 

		if ($sommaire_seul)
			return $sommaire;
		if ($p = strpos($texte,"<sommaire>") OR $p = strpos($texte,"[sommaire]")){
			$texte = substr_replace($texte,$sommaire,$p,strlen("<sommaire>"));
		}
		else
			$texte = $sommaire . $texte;
	}
	return $texte;
}

function ciparam_sommaire_filtre_select($texte, $ajoute=true, $sommaire_seul=false){
	static $nb = 0;
	$nb++;
	$sommaire = sommaire_recenser($texte);

	if ($ajoute OR $sommaire_seul){
		include_spip('inc/texte');
		
		//---- Debut ajout CI pour compatibilite SPIP 2.1 -----
		if ($nb<2)
			$ancre = '<a name="nav-sommaire"></a>';
		else
			$ancre = '';

		$debut = $ancre.'<form name="accestitre'.$nb.'" action="Javascript:goToSelectURL(\'accestitre'.$nb.'\',\'nom_titre'.$nb.'\');" '
				.'method="get" class="nav-sommaire-'.count($sommaire).'">'
				.'<select name="nom_titre'.$nb.'" id="nom_titre'.$nb.'">'
				.'<option value="" selected="selected">'._T('ciparam:acces_direct').'</option>';
		$fin = '</select><input type="submit" name="masquable" value="OK"></input></form>';
		
		
		foreach ($sommaire AS $ligne){
	        // met au passage des espaces insecables
	        if ($ligne['niveau']>=1)
		        $identation = 2*($ligne['niveau']-1);
		    else
		        $identation = 0;

			$res .= '<option value="'.$ligne['href'].'">'.str_pad('',$identation,'-').str_replace(' ','&nbsp;',$ligne['titre']).'</option>';
		}
		
		return "<!--sommaire-->" . $debut . $res .$fin . "<!--/sommaire-->"; 
	}
	return '';
}


/*-----------------------------------------------------------------
// Fonction pour les retours et les sauts de ligne multiples
------------------------------------------------------------------*/

// ciparam_post_autobr : transforme les retour a la ligne en _
// en tenant compte de certains cas particuliers
// et permet les sauts de ligne multiples
function ciparam_post_autobr($texte, $delim="\n_ ") {
	
	if (spip_version()>=3) {
		$texte = str_replace("\r\n", "\r", $texte);
		$texte = str_replace("\r", "\n", $texte);

		// sauts de ligne consecutifs
		$texte = ciparam_sauts_lignes_consecutifs($texte);
		
		return $texte;

	} else {
		include_spip('inc/filtres');
		
		// cas des tableaux HTML saisis manuellement
	    $preg_tableaux = ',</?(table|tr|td|th|thead|tbody)(.*?)>,iS';    
		$recherches = array();
		$items = explode('>',$texte);	
		foreach ($items as $item) {
			$posdebut = strrpos($item, "<");
			if ($posdebut !== false) {
				$replace = substr($item,$posdebut).">";
				$search = $replace."\r\n";
				if (!in_array($search,$recherches)) {
					$recherches[] = $search;
					if (preg_match($preg_tableaux, $replace)) {
						$texte = str_replace($search,$replace,$texte);
					}
				}
			}
		}
	
	
		$texte = str_replace("\r\n", "\r", $texte);
		$texte = str_replace("\r", "\n", $texte);
		
		// sauts de ligne consecutifs
		$texte = ciparam_sauts_lignes_consecutifs($texte);
	
		if (preg_match(",\n+$,", $texte, $fin))
			$texte = substr($texte, 0, -strlen($fin = $fin[0]));
		else
			$fin = '';
	
		$texte = echappe_html($texte, '', true);
	
	
		$debut = '';
		$suite = $texte;
		while ($t = strpos('-'.$suite, "\n", 1)) {
			$debut .= substr($suite, 0, $t-1);
			$suite = substr($suite, $t);
			$car = substr($suite, 0, 1);
			if (($car<>'-') AND ($car<>'_') AND ($car<>"\n") AND ($car<>"|") AND ($car<>"}")
			AND !preg_match(',^\s*(\n|</?(quote|div)|$),S',($suite))
			AND !preg_match(',</?(quote|div)> *$,iS', $debut)) {
				$debut .= $delim;
			} else
				$debut .= "\n";
			if (preg_match(",^\n+,", $suite, $regs)) {
				$debut.=$regs[0];
				$suite = substr($suite, strlen($regs[0]));
			}
		}
		$texte = $debut.$suite;
	
		$texte = echappe_retour($texte);
		return $texte.$fin;
	}
}

// sauts de ligne consecutifs (jusqu'à 5)
function ciparam_sauts_lignes_consecutifs($texte) {

	$ci_sauts_lignes = ciparam_lire_meta("ci_sauts_lignes");		
	
	if ($ci_sauts_lignes=='oui' AND strpos($texte,"\n\n\n")!==false){
		$prefixe = "\n\n";
		$a = "\n";
		// l'espace au debut est indispensable
		$b = " <br class='autobr' />";
		// 5 sauts de ligne
		$texte = str_replace($prefixe.$a.$a.$a.$a,$prefixe.$b.$b.$b.$b, $texte);
		// 4 sauts de ligne
		$texte = str_replace($prefixe.$a.$a.$a,$prefixe.$b.$b.$b, $texte);
		// 3 sauts de ligne
		$texte = str_replace($prefixe.$a.$a,$prefixe.$b.$b, $texte);
		// 2 sauts de ligne
		$texte = str_replace($prefixe.$a,$prefixe.$b, $texte);
		
		// eviter un effet de bord avec les tableaux, les listes a puces, les listes numerotees, les lignes de separation
		$texte = str_replace(array($b.'|',$b.'-*',$b.'-#',$b.'----'),array($a.'|',$a.'-*',$a.'-#',$a.'----'), $texte);
	}
		
	return $texte;
}

?>