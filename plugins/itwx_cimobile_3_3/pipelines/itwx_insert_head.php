<?php

/* --------------------------------- */
/*  Squelettes mobiles iTwX Mobile	 */
/*  (c) 2011 iTwX Design			 */
/*  Licence GPL 3 ©2011 Seds 		 */
/* --------------------------------- */
 

function itwx_insert_head($flux) {
	$appletouchactiv = lire_config('itwx/appletouchactiv');
	if ($appletouchactiv == 'icondefaut') {
			$flux .= "\n"
			. "<!-- iTwX Icone WebClip -->\n"
			. '<link rel="apple-touch-icon" href="' ._DIR_PLUGIN_ITWX.'apple-touch-icon.png" />'."\n"
			. "<!-- Fin iTwX Icone WebClip -->\n"
			;
	}
	if ($appletouchactiv == 'iconprecompose') {
			$flux .= "\n"
			. "<!-- iTwX Icone WebClip -->\n"
			. '<link rel="apple-touch-icon-precomposed" href="' ._DIR_PLUGIN_ITWX.'apple-touch-icon.png" />'."\n"
			. "<!-- Fin iTwX Icone WebClip -->\n"
			;
	}
	return $flux;
}

?>