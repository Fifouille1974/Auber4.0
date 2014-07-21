<?php

// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

if (!defined("_ECRIRE_INC_VERSION")) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(


//-- Squelette
'retour' => 'Ritorno',
'siteclassique' => 'Versione classica del sito',
'pageclassique' => 'Versione classica',
'infos'=> 'Info',
'inscription' => 'Registrazione',
'message' => 'Messaggio',
'messages' => 'Messaggi',
'noevent' => 'Nessun evento',
'evenements' => 'Tutti gli enventi',
'lirearticle' => 'Leggi l\'articolo',
'copyright' =>'<a href="http://twxdesign.com/">iTwX Mobile &copy; 2012</a>',

  // iTwX questions
'smart_question' => 'Vuoi vedere la versione Smarphone del sito?',
'mob_question' => 'Vuoi vedere la versione Cellulare del sito?',
'tab_question' => 'Vuoi vedere la versione Tablet del sito?',
'smart_squel' => 'Vedere la versione Smarphone del sito',
'mob_squel' => 'Vedere la versione Cellulare del sito',
'tab_squel' => 'Vedere la versione Tablet del sito',



//-- Prive 

  // Plugin
'itwx_description' => 'Il plugin iTwX offre 3 giochi di modelli per qualsiasi tipo di cellulare e tablet. Per procedere con l\installazione, vedi <a href="http://twxdesign.com/spip.php?article5">instruioni</a>.',  

  // CFG iTwX
'config_titre' => '&nbsp;iTwX Cellulare',
'config_itwx' => 'Configurazione iTwX Mobile modello',
'config_explication' => 'Vedi la <a href="#">Documenta-<br/>zione</a>.',
'config_player' => '<b>Gestione di lettori flash per iPhone / iPod / iPad (sperimentale) :</b><br /><br />Compatibile con i lettori flash dei plugin <b>Player Multimediale</b>.<br /><br /><span style="font-size:0.9em">Mettere codes de importazione tra le div:</span><br /><b>&lt;div class="twx-player"&gt;</b>codes<b>&lt;/div&gt;</b><br /><br /><span style="font-size:0.9em">Per visualizzare il lettore iPhone, iPod, iPad per documenti mp4 :</span> <br /><b>&lt;docXX|iplayer&gt;</b>',
'vignette_player' => '<b>Logos iPlayer :</b><br />Per personalizzare il logos video, incollare <a href="../plugins/itwxmobile_3_2/prive/img/iPlayer_through.zip">iPlayer_through</a> su un\'immagine alle demensioni 300x188.',
'liens' => '<b>Accesso diretto</b> /!\ <small>(cookie)</small>:<br />',
'smartphone' => '<a href="../?cimobile=smartphone">Versione Smarphone</a>',
'mobile' => '<a href="../?cimobile=mobile">Versione Cellulare</a>',
'tablette' => '<a href="../?cimobile=tablette">Versione Tablet</a>',
'web' => '<a href="../?cimobile=web">Ritorno versione classica</a>',

  // Theme
'config_legend' => 'Scegli il colore',
'affiche' => 'Clicca sulle immagini per ingrandirle.',
'agrandir' => 'Ingrandire',
'bleu' => 'Blu',
'noirblanc' => 'Bianco &amp; Nero',
'vert' => 'Verde',
'mauve' => 'Viola',
'rouge' => 'Rosso',
'Orange' => 'Arancione;',

  // Squelette
'skel_titre' => 'Configurare lo switch',
'redirection' => 'Modalit&agrave; di reindirizzamento :',
'automatique' => 'Automatica',
'notification' => 'Con notifica',
'nosmart' => '&nbsp;Reindirizzare tutti gli Smartphone alla versione Cellulare del sito',
'notab' => '&nbsp;Reindirizzare tutti gli Tablets alla versione classica del sito',
'retour_sommaire' => '&nbsp;Attivare linki per restutuire alle pagine classici',
'switcher_comment' => '&bull; Per reindirizzare tutti i cellulari e tablet per i modelli iTwX, incollare nel vostro modello classico :<br />&nbsp; &ndash; in sommaire.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}&gt;</b><br />&nbsp; &ndash; in rubrique.html :<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>&lt;INCLURE{fond=itwx/redirection}{id_rubrique}&gt;</b><br />&nbsp; &ndash; nelle pagine article.html, breve.html, site.html, mot.html e auteur.html, sostituire con l\'oggetto <b>id_</b> corrispondente.<br />&bull; Stile con la class <b>.itwx_alerte</b>',


  // CFG redirection
'itwxnom_titre' => 'Personalizzare il sitio cellulare (opzionale)',
'itwxnom_explication' => 'Se vuoto, tornare al default.',
'itwxnom_comment' => 'Il nome predefinito del sitio visualizzate iTwX Home page &egrave; limita a 35 caratteri. Si desidera o per qualsiasi altro motivo, &egrave; possibile modificare il nome e la descrizione per il iTwX modello.',
'auteurs_sommaire' => '&nbsp;Attivare il link all\'elenco degli autori in homepage',
'rubriques_sommaire' => '&nbsp;Attivare l\'elenco di argomenti in homepage (tranne Tablet)',
'mots_sommaire' => '&nbsp;Attivare il link all\'elenco degli parole chiave in homepage',
'css_perso' => '&nbsp;Attivare il sovraccarico di CSS',
'css_itwx_perso' => '&nbsp;CSS iTwX perso',
'css_perso_explication' => '&nbsp;Solo sul \'Cellulare\', iniziano con <b>#mob</b>, sul \'Smartphone\' iniziano con <b>#smart</b>, sul \'Tablette\' iniziano con <b>#tab</b>.',
'itwxsocialtags' => '&nbsp;Attivare il sellettore <b>#socialtags</b>',
'itwxgoogleplus1' => '&nbsp;Attivare il sellettore <b>#googleplus1</b>',
'appletouchactiv' => '&nbsp;Attivare il Apple-Touch-Icon',
'noicon' => '&nbsp;No',
'icondefaut' => '&nbsp;Predefinita',
'iconprecompose' => '&nbsp;Precomposto',

// -- Alerte fin formulaire
'config_alerte' => '<b>Svuota la cache</b> (immagini) dopo ogni modifica !',

);

?>
