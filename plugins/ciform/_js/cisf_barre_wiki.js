// Barre de raccourcis
// derive du:
// bbCode control by subBlue design : www.subBlue.com

// Startup variables
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);


function barre_raccourci(debut,fin,champ) {
	var txtarea = champ;

	txtarea.focus();
	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection) {

			while (theSelection.substring(theSelection.length-1, theSelection.length) == ' ')
			{
				theSelection = theSelection.substring(0, theSelection.length-1);
				fin = fin + " ";
			}
			if (theSelection.substring(0,1) == '{' && debut.substring(0,1) == '{')
			{
				debut = debut + " ";
			}
			if (theSelection.substring(theSelection.length-1, theSelection.length) == '}' && fin.substring(0,1) == '}')
			{
				fin = " " + fin;
			}

			// Add tags around selection
			document.selection.createRange().text = debut + theSelection + fin;
			txtarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, debut, fin);
		return;
	}
}

function barre_demande(debut,milieu,fin,affich,champ,valeur) {
	var inserer = prompt(affich,valeur);

	if (inserer != null) {
		if (inserer == "") {inserer = "xxx"; }

		barre_raccourci(debut, milieu+inserer+fin, champ);
	}
}

// Emprunt a SPIP 2.0.10
function barre_inserer(text,champ) {
	var txtarea = champ;
	if( document.selection ){
		txtarea.focus();
		var r = document.selection.createRange();
		if (r == null) {
			txtarea.selectionStart = txtarea.value.length;
			txtarea.selectionEnd = txtarea.selectionStart;
		}
		else {
			var re = txtarea.createTextRange();
			var rc = re.duplicate();
			re.moveToBookmark(r.getBookmark());
			rc.setEndPoint('EndToStart', re);
			txtarea.selectionStart = rc.text.length;
			txtarea.selectionEnd = rc.text.length + r.text.length;
		}
	} 
	mozWrap(txtarea, '', text);
}



// D'apres Nicolas Hoizey
function barre_tableau(toolbarfield)
{
	var txtarea = toolbarfield;
	txtarea.focus();
	var cols = prompt("Nombre de colonnes du tableau :", "");
	var rows = prompt("Nombre de lignes du tableau :", "");
	if (cols != null && rows != null) {
		var tbl = '';
		var ligne = '|';
		var entete = '|';
		for(i = 0; i < cols; i++) {
			ligne = ligne + ' valeur |';
			entete = entete + ' {{entete}} |';
		}
		for (i = 0; i < rows; i++) {
			tbl = tbl + ligne + '\n';
		}
		if (confirm('Voulez vous ajouter une ligne d\'en-tete ?')) {
			tbl = entete + '\n' + tbl;
		}
		if ((clientVer >= 4) && is_ie && is_win) {
			var str = document.selection.createRange().text;
			var sel = document.selection.createRange();
			sel.text = str + '\n\n' + tbl + '\n\n';
		} else {
			mozWrap(txtarea, '', "\n\n" + tbl + "\n\n");
		}
	}
	return;
}



// Shows the help messages in the helpline window
function helpline(help, champ) {
	champ.value = help;
}


function setCaretToEnd (input) {
  setSelectionRange(input, input.value.length, input.value.length);
}


function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}

// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;	
	if (selEnd == 1 || selEnd == 2)
		selEnd = selLength;
	var selTop = txtarea.scrollTop;

	// Raccourcir la selection par double-clic si dernier caractere est espace	
	if (selEnd - selStart > 0 && (txtarea.value).substring(selEnd-1,selEnd) == ' ') selEnd = selEnd-1;
	
	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);

	// Eviter melange bold-italic-intertitre
	if ((txtarea.value).substring(selEnd,selEnd+1) == '}' && close.substring(0,1) == "}") close = close + " ";
	if ((txtarea.value).substring(selEnd-1,selEnd) == '}' && close.substring(0,1) == "}") close = " " + close;
	if ((txtarea.value).substring(selStart-1,selStart) == '{' && open.substring(0,1) == "{") open = " " + open;
	if ((txtarea.value).substring(selStart,selStart+1) == '{' && open.substring(0,1) == "{") open = open + " ";

	txtarea.value = s1 + open + s2 + close + s3;
	selDeb = selStart + open.length;
	selFin = selEnd + close.length;
	window.setSelectionRange(txtarea, selDeb, selFin);
	txtarea.scrollTop = selTop;
	txtarea.focus();
	
	return;
}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130

     function storeCaret (textEl) {
       if (textEl.createTextRange) 
         textEl.caretPos = document.selection.createRange().duplicate();

     }
     

//----------------- Debut ajout CI (barre outil) -----
function outil_cisf_document(h) {
	idchamp = h.textarea.id;
	document.getElementById('ins_document').value = idchamp;
	position(idchamp);
	document.forms[0].submit();
}
function outil_cisf_image(h) {
	idchamp = h.textarea.id;
	document.getElementById('ins_image').value = idchamp;
	position(idchamp);
	document.forms[0].submit();
}
function outil_cisf_tableau(h) {
	var txtarea = h.textarea;
	txtarea.focus();
	var cols = prompt("Nombre de colonnes du tableau :", "");
	var rows = prompt("Nombre de lignes du tableau :", "");
	if (cols != null && rows != null) {
		var tbl = '';
		var ligne = '|';
		var entete = '|';
		for(i = 0; i < cols; i++) {
			ligne = ligne + ' valeur |';
			entete = entete + ' {{entete}} |';
		}
		for (i = 0; i < rows; i++) {
			tbl = tbl + ligne + '\n';
		}
		if (confirm('Voulez vous ajouter une ligne d\'en-tete ?')) {
			tbl = entete + '\n' + tbl;
		}

		var tbl_titre = prompt("Titre du tableau (facultatif) :", "");
		var tbl_resume = prompt("Descriptif du tableau (facultatif) :", "");
		
		if (tbl_titre || tbl_resume) {
			tbl = '||' + tbl_titre + '|' + tbl_resume + '||' + '\n' + tbl;
		}

		if ((clientVer >= 4) && is_ie && is_win) {
			var str = document.selection.createRange().text;
			var sel = document.selection.createRange();
			sel.text = str + '\n\n' + tbl + '\n\n';
		} else {
			mozWrap(txtarea, '', "\n\n" + tbl + "\n\n");
		}
	}
	return;
}



function barre_document(idchamp) {
	document.getElementById('ins_document').value = idchamp;
	position(idchamp);
	document.forms[0].submit();
}

function barre_image(idchamp) {
	document.getElementById('ins_image').value = idchamp;
	position(idchamp);
	document.forms[0].submit();
}

  

function getSelectionStart(input) {

	var is_gecko = /gecko/i.test(navigator.userAgent);

	if (is_gecko)
		return input.selectionStart + 1;

	var range = document.selection.createRange();
	var isCollapsed = range.compareEndPoints("StartToEnd", range) == 0;

	if (!isCollapsed)
		range.collapse(true);

	var b = range.getBookmark();

	return b.charCodeAt(2) - 2;
} 



function position(idchamp)
{
	var Obj= document.getElementById(idchamp);
	if (Obj){
		Obj.focus();
		var marque = "~~";
		if(typeof Obj.selectionStart != "undefined") {
			Pos = Obj.selectionStart;
			mozWrap(Obj, marque, "");
		} else { // IE
			var Chaine = Obj.value;
			var range = document.selection.createRange();
			// insere une marque
			range.text = marque;
		}
	}
	
}


var bt = '';
bt += '<table class="spip_barre" width="100%" cellpadding="0" cellspacing="0" border="0">';
bt += '<tr width="100%" class="spip_barre">';
bt += '<td style="text-align: left;" valign="middle">';
bt += '<a href="javascript:barre_raccourci(\'{\',\'}\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Mettre en {italique}"><img src="plugins/cisf/_images/icones_barre/italique.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_raccourci(\'{{\',\'}}\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Mettre en {{gras}}"><img src="plugins/cisf/_images/icones_barre/gras.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_raccourci(\'{{{\',\'}}}\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Transformer en {{{intertitre}}}"><img src="plugins/cisf/_images/icones_barre/intertitre.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_tableau(document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Ins&eacute;rer un tableau"><img src="plugins/cisf/_images/icones_barre/tableau.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<\/td><td>';
bt += '<a href="javascript:barre_demande(\'[\',\'->\',\']\', \'Veuillez indiquer une adresse pour votre lien (vous pouvez indiquer une adresse Web sous la forme http://www.monsite/com).\', document.formulaire.texte, \'http://\')" class="spip_barre" tabindex="1000" title="Transformer en [lien hypertexte->http://...]"><img src="plugins/cisf/_images/icones_barre/lienexterne.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_demande(\'[\',\'->\',\']\', \'Veuillez saisir la r&eacute;f&eacute;rence correspondant &agrave; un &eacute;l&eacute;ment interne de ce site (par exemple : art42, rub33, site3, doc25, img17, etc.).\', document.formulaire.texte, \'\')" class="spip_barre" tabindex="1000" title="Transformer en [lien interne->art...]"><img src="plugins/cisf/_images/icones_barre/lien.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_raccourci(\'[[\',\']]\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Transformer en [[Note de bas de page]]"><img src="plugins/cisf/_images/icones_barre/notes.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<\/td><td style="text-align:left;" valign="middle">';
bt += '<a href="javascript:barre_raccourci(\'&laquo;\',\'&raquo;\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Entourer de &laquo; guillemets &raquo;"><img src="plugins/cisf/_images/icones_barre/guillemets.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_raccourci(\'&ldquo;\',\'&rdquo;\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Entourer de &ldquo;guillemets de second niveau&rdquo;"><img src="plugins/cisf/_images/icones_barre/guillemets-simples.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_inserer(\'&Agrave;\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Ins&eacute;rer un A accent grave majuscule"><img src="plugins/cisf/_images/icones_barre/agrave-maj.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_inserer(\'&Eacute;\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Ins&eacute;rer un E accent aigu majuscule"><img src="plugins/cisf/_images/icones_barre/eacute-maj.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_inserer(\'&oelig;\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Ins&eacute;rer un E dans l\'O"><img src="plugins/cisf/_images/icones_barre/oelig.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_inserer(\'&OElig;\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Ins&eacute;rer un E dans l\'O majuscule"><img src="plugins/cisf/_images/icones_barre/oelig-maj.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '<a href="javascript:barre_inserer(\'&euro;\',document.formulaire.texte)" class="spip_barre" tabindex="1000" title="Ins&eacute;rer le symbole &euro;"><img src="plugins/cisf/_images/icones_barre/euro.png" border="0" height="16" width="16" align="middle" /><\/a>';
bt += '';
bt += '<\/td>';
bt += '<\/tr>';
bt += '<\/table>';


//----------------- Fin ajout CI ---------------------