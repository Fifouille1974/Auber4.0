<?php

/*-----------------------------------------------------------------
// Filtre pour paginer les sujets
------------------------------------------------------------------*/

function cisquel_paginer_sujethierarchique($num_items,$id_article,$id_forum,$debut_forum) {
	
	if (!$debut_forum) $debut_forum =0;
	          	$affiche = "";			                  	
	$per_page = 5;
	$start_item = intval($debut_forum);
	$total_page = ceil($num_items/$per_page);
							
	$on_page = floor($start_item / $per_page) + 1;
	
	$page_string = '';
	if ( $total_page > 10 ) {
		$init_page_max = ( $total_page > 3 ) ? 3 : $total_page;
	
		for($i = 1; $i < $init_page_max + 1; $i++) {
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="'.interdire_scripts(parametre_url(parametre_url(parametre_url(generer_url_public('sujethierarchique'),'id_article',$id_article),'id_forum',$id_forum),'debut_forum',(( $i - 1 ) * $per_page ))).'"   >' . $i . '</a>';
			if ( $i <  $init_page_max ) {
				$page_string .= ", ";
			}
		}
		
		if ( $total_page > 3 ) {
			if ( $on_page > 1  && $on_page < $total_page )	{
				$page_string .= ( $on_page > 5 ) ? ' ... ' : ', ';
	
				$init_page_min = ( $on_page > 4 ) ? $on_page : 5;
				$init_page_max = ( $on_page < $total_page - 4 ) ? $on_page : $total_page - 4;
	
				for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++) {
					$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="'.interdire_scripts(parametre_url(parametre_url(parametre_url(generer_url_public('sujethierarchique'),'id_article',$id_article),'id_forum',$id_forum),'debut_forum',(( $i - 1 ) * $per_page ))).'"   >' . $i . '</a>';
					if ( $i <  $init_page_max + 1 ) {
						$page_string .= ', ';
					}
				}
	
				$page_string .= ( $on_page < $total_page - 4 ) ? ' ... ' : ', ';
			} else {
				$page_string .= ' ... ';
			}
	
			for($i = $total_page - 2; $i < $total_page + 1; $i++) {
				$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>'  : '<a href="'.interdire_scripts(parametre_url(parametre_url(parametre_url(generer_url_public('sujethierarchique'),'id_article',$id_article),'id_forum',$id_forum),'debut_forum',(( $i - 1 ) * $per_page ))).'"   >' . $i . '</a>';
				if( $i <  $total_page ) {
					$page_string .= ", ";
				}
			}
		}
	} else {
		for($i = 1; $i < $total_page + 1; $i++) {
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="'.interdire_scripts(parametre_url(parametre_url(parametre_url(generer_url_public('sujethierarchique'),'id_article',$id_article),'id_forum',$id_forum),'debut_forum',(( $i - 1 ) * $per_page ))).'"   >' . $i . '</a>';
			if ( $i <  $total_page ) {
				$page_string .= ', ';
			}
		}
	}
	
	if ( true ) {
		if ( $on_page > 1 ) {
			$page_string = ' <a href="'.interdire_scripts(parametre_url(parametre_url(parametre_url(generer_url_public('sujethierarchique'),'id_article',$id_article),'id_forum',$id_forum),'debut_forum',(( $on_page - 2 ) * $per_page ))).'"   >'._T('cisquel:page_precedente').'</a>&nbsp;&nbsp;' . $page_string;
		}
	
		if ( $on_page < $total_page ) {
			$page_string .= '&nbsp;&nbsp;<a href="'.interdire_scripts(parametre_url(parametre_url(parametre_url(generer_url_public('sujethierarchique'),'id_article',$id_article),'id_forum',$id_forum),'debut_forum',( $on_page * $per_page ))).'"   >'._T('cisquel:page_suivante').'</a>';
		}
	
	}
	
	$affiche = _T('cisquel:eq_aller_page') . $page_string;
	if ( $total_page == 1 ) {
		$affiche='';
	}
	
	return $affiche;
      	
}	            	
?>