<?php

// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(


//-- Squelette
'retour' => 'Zur&uuml;ck',
'siteclassique' => 'Klassische Standard der Website',
'pageclassique' => 'Klassische Standard',
'infos'=> 'Info',
'inscription' => 'Anmeldung',
'message' => 'Beitrag',
'messages' => 'Beitr&Auml;ge',
'noevent' => 'Keinem Fall',
'evenements' => 'Alle Ereignisse',
'lirearticle' => 'Den Artikel lesen',
'copyright' => '<a href="http://twxdesign.com/">iTwX Handy &copy; 2012</a>',

  // iTwX questions
'smart_question' => 'Wollen Sie mit der Smartphone-Version die Webseite besuchen?',
'mob_question' => 'Wollen Sie mit der Handy-Version die Webseite besuchen?',
'tab_question' => 'Wollen Sie mit der Tablet-Version die Webseite besuchen?',
'smart_squel' => 'Die Smartphone-Version nutzen',
'mob_squel' => 'Die Handy-Version nutzen',
'tab_squel' => 'Die Tablet-Version nutzen',



//-- Prive 

  // Plugin
'itwx_description' => 'Das plugin iTwX bietet 3 Skelett-Spiele an, die an alle Arten von Smartphone und Tablets angepasst sind. F&uuml;r die Installation lesen Sie die <a href="http://twxdesign.com/spip.php?article18">Anweisungen</a>.',  

  // Configuration iTwX
'config_titre' => '&nbsp;iTwX Mobile',
'config_itwx' => 'Konfiguration der Skelette iTwX Handy',
'config_explication' => 'Siehe <a href="http://twxdesign.com/spip.php?article18">Documentation</a>.',
'config_player' => '<b>Flash-Laufwerk-Managment f&uuml;r iPhone / iPod / iPad (experimentell) :</b><br /><br />Kompatibel mit dem Flash-Laufwerk <b>Media Player</b>.<br /><br /><span style="font-size:0.9em">Setzen Sie den Import-Code zwischen die divs:</span><br /><b>&lt;div class="twx-player"&gt;</b>codes<b>&lt;/div&gt;</b><br /><br /><span style="font-size:0.9em">Um das Leseger&auml;t f&uuml;r iPhone, iPod, iPad  der MP4-Dokumente anzuzeigen:</span> <br /><b>&lt;docXX|iplayer&gt;</b>',
'vignette_player' => '<b>Icon iPlayer :</b><br />Um die Video-Icons anzupassen, f&uuml;gen Sie <a href="../plugins/itwxmobile_3_2/prive/img/iPlayer_through.zip">iPlayer_through</a> auf dem Bildformat 300x188 dazu.',
'liens' => '<b>Dirktzugriff</b> /!\ <small>(cookie)</small>:<br />',
'smartphone' => '<a href="../?cimobile=smartphone">Smartphone-Version</a>',
'mobile' => '<a href="../?cimobile=mobile">Handy-Version</a>',
'tablette' => '<a href="../?cimobile=tablette">Tablet-Version</a>',
'web' => '<a href="../?cimobile=web">Zur&uuml;ck zur klassischen Version</a>',

  // Theme
'config_legend' => 'Thema ausw&auml;hlen',
'affiche' => 'Zum Vergr&ouml;ssern auf die Bilder klicken.',
'agrandir' => 'Vergr&ouml;ssern',
'bleu' => 'Blau',
'noirblanc' => 'Schwarz & Weiss',
'vert' => 'Gr&uuml;n',
'mauve' => 'Lila',
'rouge' => 'Rot',
'orange' => 'Orange',

  // Switcher
'skel_titre' => ' Switcher-Konfiguration ',
'redirection' => 'Umleitungsmodus:',
'automatique' => 'Automatik',
'notification' => 'Durch Zustellung',
'nosmart' => ' Alle Smartphones auf die Handy-Version der Webseite umleiten',
'notab' => ' Alle Tablets auf die klassische Version der Webseite umleiten',
'retour_sommaire' => ' Die Links zu den klassischen Seiten anzeigen ',
'switcher_comment' => '&bull; Um Ihre Handys und Tablets von der klassischen Version der Webseite auf die Skelett-Versionen iTwX umzuleiten, f&uuml;gen Sie auf ihrem Skelett-Men&uuml; :<br />&nbsp; &ndash; in sommaire.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}&gt;</b><br />&nbsp; &ndash; in rubrique.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}{id_rubrique}&gt;</b><br />&nbsp; &ndash; in den Seiten article.html, breve.html, site.html, mot.html et auteur.html, durch das entsprechende Objekt <b>id_</b> ersetzen.<br />&bull; Stil mit class <b>.itwx_alerte</b>',


  // Squelette
'itwxnom_titre' => ' Die Skelett-Version benennen (Option)',
'itwxnom_explication' => 'Wenn leer, notfalls zur Startseite zur&uuml;ckgehen.',
'itwxnom_comment' => 'Der Name ihrer auf der Startseite iTwX angegebenen Webseite ist auf  35 Zeichen begrenzt ( ausser Tablets ). Zudem haben Sie, wenn sie es w&uuml;nschen, immer die M&ouml;glichkeit diesen Namen oder die Beschreibungen der Skelette iTwW  zu ver&auml;ndern.',
'auteurs_sommaire' => ' Anzeigen der Links zu der Autorenliste auf der Startseite',
'rubriques_sommaire' => ' Anzeigen Der Links zur Rubrikliste auf der Startseite (ausser Tablets)',
'mots_sommaire' => ' Anzeigen der Links zu der Liste der Schl&uuml;sselworte auf der Startseite',
'css_perso' => '&nbsp;Anzeigen CSS-&Uuml;berladen',
'css_itwx_perso' => '&nbsp;CSS iTwX perso',
'css_perso_explication' => '&nbsp;F&uuml;r nur auf der \'Handy\', Starten Sie mit <b>#mob</b>, auf der \'Smartphone\' Starten Sie mit <b>#smart</b>, auf der \'Tablet\' Starten Sie mit <b>#tab</b>.',
'itwxsocialtags' => '&nbsp;Anzeigen Auswahl <b>#socialtags</b>',
'itwxgoogleplus1' => '&nbsp;Anzeigen Auswahl <b>#googleplus1</b>',
'appletouchactiv' => '&nbsp;Anzeigen das Apple Touch Icon',
'noicon' => '&nbsp;Nein',
'icondefaut' => '&nbsp;Standard',
'iconprecompose' => '&nbsp;Precomposed',

// -- Alerte fin formulaire
'config_alerte' => '<b>Maske leeren</b> (Bilder) nach jeder &Auml;nderung !',

);

?>
