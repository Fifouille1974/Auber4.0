<?php

function ciimport_extraire_nom($email){
	$return = '';
	if ($email) {
		$ci_tableau_email = explode('@',$email);
		if (isset($ci_tableau_email[0]))
			$return = strtolower($ci_tableau_email[0]);
	}
	return $return;	
}

?>