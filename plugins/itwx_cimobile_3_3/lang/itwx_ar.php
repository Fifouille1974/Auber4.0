<?php

// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(


//-- Squelette
'retour' => '&#1575;&#1604;&#1593;&#1608;&#1583;&#1577;',
'siteclassique' => '&#1575;&#1604;&#1606;&#1587;&#1582;&#1577; &#1575;&#1604;&#1571;&#1589;&#1604;&#1610;&#1577; &#1604;&#1604;&#1605;&#1608;&#1602;&#1593;',
'pageclassique' => '&#1575;&#1604;&#1606;&#1587;&#1582;&#1577; &#1575;&#1604;&#1571;&#1589;&#1604;&#1610;&#1577; ',
'infos'=> '&#1605;&#1593;&#1604;&#1608;&#1605;&#1575;&#1578;',
'inscription' => '&#1575;&#1604;&#1573;&#1606;&#1582;&#1585;&#1575;&#1591;',
'message' => '&#1585;&#1587;&#1575;&#1604;&#1577;',
'messages' => '&#1585;&#1587;&#1575;&#1574;&#1604;',
'noevent' => '&#1604;&#1575;&#32;&#1571;&#1581;&#1583;&#1575;&#1579;',
'evenements' => '&#1580;&#1605;&#1610;&#1593;&#32;&#1575;&#1604;&#1571;&#1581;&#1583;&#1575;&#1579;',
'lirearticle' => '&#1575;&#1602;&#1585;&#1571;&#1575;&#1604;&#1605;&#1602;&#1575;&#1604;',
'copyright' =>'<a href="http://twxdesign.com/">iTwX Mobile &copy; 2012</a>',

  // iTwX questions
'smart_question' => '\u061f\u064a\u0643\u0630\u0644\u0627 \u0641\u062a\u0627\u0647\u0644\u0627 \u0649\u0644\u0639 \u0629\u0631\u0627\u064a\u0632',
'mob_question' => '\u061f\u0644\u0648\u0645\u062d\u0645\u0644\u0627 \u0649\u0644\u0639 \u0629\u0631\u0627\u064a\u0632',
'tab_question' =>  '\u061f\u0627\u0644\u0644\u0648\u062d\u064a \u0629\u0631\u0627\u064a\u0632',
'smart_squel' => '&#1585;&#1572;&#1610;&#1577; &#1573;&#1589;&#1583;&#1575;&#1585; &#1604;&#1604;&#1607;&#1575;&#1578;&#1601; &#1575;&#1604;&#1584;&#1603;&#1610;',
'mob_squel' => '&#1586;&#1610;&#1575;&#1585;&#1577; &#1593;&#1604;&#1609; &#1575;&#1604;&#1605;&#1581;&#1605;&#1608;&#1604;',
'tab_squel' => '&#1586;&#1610;&#1575;&#1585;&#1577; &#1575;&#1604;&#1604;&#1608;&#1581;&#1610;',



//-- Prive 

  // Plugin
'itwx_description' => 'iTwX Mobile plugin offers 3 games of templates for any mobile and for tablet. To proceed to the installation, see <a href="http://twxdesign.com/spip.php?article18">the instructions</a>.',  

  // Configuration iTwX
'config_titre' => 'iTwX Mobile',
'config_itwx' => 'Configuration of iTwX Mobile Templates',
'config_explication' => '<span style="font-size:0.9em">See the <a href="http://twxdesign.com/spip.php?aticle18">Documentation</a></span>.',
'config_player' => '<b>Management of flash player for iPhone / iPod / iPad (experimental) :</b><br /><br />Compatible with flash players of <b>Multimedia Player</b> plugin.<br /><br /><span style="font-size:0.9em">Put importation codes between div:</span><br /><b>&lt;div class="twx-player"&gt;</b>codes<b>&lt;/div&gt;</b><br /><br /><span style="font-size:0.9em">To see player for iPhone, iPod or iPad with mp4 documents, use :</span> <br /><b>&lt;docXX|iplayer&gt;</b>',
'vignette_player' => '<b>iPlayer Thumbnails :</b><br />To customize video thumbnails, past <a href="../plugins/itwxmobile_3_2/prive/img/iPlayer_through.zip">iPlayer_through</a> on a picture with dimensions 300x188.',
'liens' => '<b>Direct access </b> /!\ <small>(cookie)</small>:<br />',
'smartphone' => '<a href="../?cimobile=smartphone">Smartphone version</a>',
'mobile' => '<a href="../?cimobile=mobile">Mobile version</a>',
'tablette' => '<a href="../?cimobile=tablette">Tab version</a>',
'web' => '<a href="../?cimobile=web">Back classical version</a>',

  // Theme
'config_legend' => 'Select the color',
'affiche' => 'Click images to blow up.',
'agrandir' => 'Blow up',
'bleu' => 'Blue',
'noirblanc' => 'Black &amp; White',
'vert' => 'Green',
'mauve' => 'Purple',
'rouge' => 'Red',
'orange' => 'Orange',

  // Switcher
'skel_titre' => 'Configure the switch',
'redirection' => 'Redirection mode :',
'automatique' => 'Automatic',
'notification' => 'With notification',
'nosmart' => '&nbsp;Redirect all Smartphones to Mobile version of website',
'notab' => '&nbsp;Redirect all Tablettes to classical version of website',
'retour_sommaire' => '&nbsp;Enable backs links to classical pages',
'switcher_comment' => '&bull; To redirect mobiles and tabs from classical version of website to iTwX templates, past in your template folder :<br />&nbsp; &ndash; into sommaire.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}&gt;</b><br />&nbsp; &ndash; into rubrique.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}{id_rubrique}&gt;</b><br />&nbsp; &ndash; into article.html, breve.html, site.html, mot.html and auteur.html, replace with the correct object <b>id_</b>.<br />&bull; Style with <b>.itwx_alerte</b> class',


  // Squelette
'itwxnom_titre' => 'Customize the mobile site (optional)',
'itwxnom_explication' => 'If empty, revert to default.',
'itwxnom_comment' => 'Default, the name of site display on iTwX Home page is limited to 35 characters. If you want or for any other reason, you can change the name and description for iTwX templates.',
'auteurs_sommaire' => '&nbsp;Enable link to authors list into the summary',
'rubriques_sommaire' => '&nbsp;Enable sections list into the summary (except tablets)',
'mots_sommaire' => '&nbsp;Enable link to keywords list into the summary',
'css_perso' => '&nbsp;Enable CSS Overload',
'css_itwx_perso' => '&nbsp;CSS iTwX perso',
'css_perso_explication' => '&nbsp;To overload only on \'Mobile\' template, begin with <b>#mob</b>, on \'Smartphone\' begin with <b>#smart</b>, and on \'Tablette\' begin with <b>#tab</b>.',
'itwxsocialtags' => '&nbsp;Enable <b>#socialtags</b> selector',
'itwxgoogleplus1' => '&nbsp;Enable <b>#googleplus1</b> selector',
'appletouchactiv' => '&nbsp;Enable Apple Touch Icon',
'noicon' => '&nbsp;No',
'icondefaut' => '&nbsp;Default',
'iconprecompose' => '&nbsp;Precomposed',

// -- Alerte fin formulaire
'config_alerte' => '<b>Empty the cache</b> (images) after change !',

);

?>
