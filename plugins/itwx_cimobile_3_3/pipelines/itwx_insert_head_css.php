<?php

/* --------------------------------- */
/*  Squelettes mobiles iTwX Mobile	 */
/*  (c) 2011 iTwX Design			 */
/*  Licence GPL 3 ©2011 Seds 		 */
/* --------------------------------- */
 

function itwx_insert_head_css($flux) {
	$flux .= "\n"
		. "<!-- Player iTwX -->\n"
		. '<script type="text/javascript">//<![CDATA[' ."\n"
		. 'if ((navigator.userAgent.match(/(iPhone|iPod|iPad)/i) )) {
 document.write("<link rel=\'stylesheet\' href=\'' ._DIR_PLUGIN_ITWX.'itwx/itwx_iplayer.css\' type=\'text/css\' media=\'projection, screen, tv\' />" );' ."\n"
 		. '}' ."\n"
		. 'if (!(navigator.userAgent.match(/(iPhone|iPod|iPad)/i) )) {
 document.write("<link rel=\'stylesheet\' href=\'' ._DIR_PLUGIN_ITWX.'itwx/itwx_player.css\' type=\'text/css\' media=\'projection, screen, tv\' />" );' ."\n"
 		. '}'."\n"
 		.'//]]></script>'."\n"
		. "<!-- Fin Player iTwX -->\n"
		;
	return $flux;
}

?>