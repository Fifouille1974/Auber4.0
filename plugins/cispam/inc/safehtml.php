<?php
/**
 * Plugin CISPAM
 * Copyright (c) Christophe IMBERTI
 * Licence Creative commons by-nc-sa
 */

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip("inc/cispam_commun");

function inc_safehtml($t) {
	static $process, $test;

	if (!$test) {
		$process = false;
		if ($f = find_in_path('lib/safehtml/classes')) {
			define('XML_HTMLSAX3', $f.'/');
			require_once XML_HTMLSAX3.'safehtml.php';
			$process = new safehtml();
			$process->deleteTags[] = 'param'; // sinon bug Firefox
		}
		if ($process)
			$test = 1; # ok
		else
			$test = -1; # se rabattre sur une fonction de securite basique
	}

	if ($test > 0) {
		# reset ($process->clear() ne vide que _xhtml...),
		# on doit pouvoir programmer ca plus propremement
		$process->_counter = array();
		$process->_stack = array();
		$process->_dcCounter = array();
		$process->_dcStack = array();
		$process->_listScope = 0;
		$process->_liStack = array();
#		$process->parse(''); # cas particulier ?
		$process->clear();
		$t = $process->parse($t);
	}
	else
		$t = entites_html($t); // tres laid, en cas d'erreur

		
	// Pour enlever le code HTML dans les forums publics lors de l'affichage de commentaires
	// ainsi que pour toute balise utilisant safehtml
	if ($action = cispam_config('cispam_forum_html_aval'))
			$t = cispam_traiter_tags($t,$action);

	return $t;
}


function cispam_traiter_tags($texte, $action=false) {

	global $class_spip_plus, $ligne_horizontale, $debut_intertitre, $fin_intertitre, $debut_gras, $fin_gras, $debut_italique, $fin_italique;

	// pour ceux qui ne respectent pas les consignes dans le fichier de parametrage
	if ($action=='supprimer' OR $action=='echapper') {

		// proteger certaines balises et certaines traductions de raccourcis SPIP
		$ci_debut_intertitre = substr($debut_intertitre,2);
		$ci_ligne_horizontale = substr($ligne_horizontale,2);
		
		$search = array('<br>','<br/>','<br />','<q>','</q>','<ins>','</ins>','<del>','</del>',
		'<p>','</p>',$debut_italique,$fin_italique,$debut_gras,$fin_gras,
		"<div style='text-align: left;' class='spip_code' dir='ltr'><code>",'</code></div>',
		'<div style="text-align: left;" class="spip_code" dir="ltr"><code>',
		"<code class='spip_code' dir='ltr'>",'</code>',
		'<blockquote'.$class_spip_plus.'>','</blockquote>','<blockquote class="spip_poesie">',$ci_debut_intertitre,$fin_intertitre,
		'</table>','<tbody>','</tbody>','<tr class="row_even">','<tr class="row_odd">','</tr>','<td>','</td>',
		'<caption>','</caption>','<thead>','</thead>','<tr class="row_first">','<th scope="col">','</th>',$ci_ligne_horizontale,
		'<ul class="spip">','</ul>','<li>','</li>','<ol class="spip">','</ol>');
		
		$replace = array('uuuu0','uuuu1','uuuu2','uuuu3','uuuu4','uuuu5','uuuu6','uuuu7','uuuu8',
		'tttt0','tttt1','tttt2','tttt3','tttt4','tttt5',
		'tttt6','tttt7',
		'tttt8','tttt9',
		'pppp1',
		'vvvv0','vvvv1','vvvv2','vvvv3','vvvv4',
		'yyyy0','yyyy1','yyyy2','yyyy3','yyyy4','yyyy5','yyyy6','yyyy7',
		'zzzz0','zzzz1','zzzz2','zzzz3','zzzz4','zzzz5','zzzz6','zzzz7',
		'rrrr0','rrrr1','rrrr2','rrrr3','rrrr4','rrrr5');

		$texte = str_ireplace($search,$replace,$texte);
		
		// proteger le debut de traduction des tableaux SPIP qui peut contenir un summary
		$c_matches = array();
		if (preg_match_all('#<table class=\"spip\"(.*?)>#', $texte, $c_matches, PREG_SET_ORDER)) {
			$c = 1;
			foreach ($c_matches as $code_match)
				$texte = str_replace($code_match[0],'cccc'.$c++.'_',$texte);
		}
	
		// remplacer les images de puce proprement <img src="extensions/cisquel/puce.gif" width="8" height="11" class="puce" alt="-" />
		if (preg_match_all(",<img(.*?)class=\"puce\"(.*?)>,i", $texte, $reg1s, PREG_SET_ORDER)) {
			foreach ($reg1s as $k => $reg)
				$texte = str_replace($reg[0], '-', $texte);
		}
		
		// remplacer les href proprement
		if (preg_match_all(",<a(.*?)href=\"(.*?)\"(.*?)>(.*?)</a>,i", $texte, $reg2s, PREG_SET_ORDER)) {
			foreach ($reg2s as $k => $reg) {
				if (substr($reg[count($reg)-1],0,35)==substr($reg[2],0,35))
					$texte = str_replace($reg[0], $reg[2], $texte);
				else			
					$texte = str_replace($reg[0], $reg[count($reg)-1].' ('.$reg[2].')', $texte);
			}
		}
		
		// traiter les tags
		if ($action=='supprimer')
			$texte = supprimer_tags($texte);
		elseif ($action=='echapper')
			$texte = echapper_tags($texte);
			
	
		// remettre le debut de traduction des tableaux SPIP
		if ($c_matches) {
			reset($c_matches);
			$c = 1;
			foreach ($c_matches as $code_match)
				$texte = str_replace('cccc'.$c++.'_',$code_match[0],$texte);
		}
	
		// deproteger certaines balises et certaines traductions de raccourcis SPIP	
		$texte = str_replace($replace,$search,$texte);
	
	}

	return $texte;
}

?>