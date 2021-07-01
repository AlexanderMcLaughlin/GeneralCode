<?php

// Add the following to functions.php
// include('upgradedCategories.php');
// add_shortcode('categories', 'output_table');

function output_table(){
	global $wpdb;
	
	$return_html = "<table style='width: 100%;'>";
	
	$all_category_query = $wpdb->get_results("SELECT term_taxonomy_id, count FROM `wp_term_taxonomy` WHERE taxonomy='category'");
	$all_category_query_num = count($all_category_query);
	
	//For loop to sort which categories will be on the left side and which will be on the right side
	for($i=0; $i<$all_category_query_num; $i++) {
		$cat_list[$wpdb->get_results("SELECT name FROM `wp_terms` WHERE term_id={$all_category_query[$i]->term_taxonomy_id}")[0]->name] = $all_category_query[$i]->count;
	}
	
	ksort($cat_list);
	$i=0;
	
	foreach($cat_list as $x => $x_value) {
		if($x_value>0) {
			//Every other column create a new row
			if($i%2==0) {
				$return_html .= "<tr class='custom_cat_row'>";
			}
			
			$link_slug = $wpdb->get_results("SELECT slug FROM `wp_terms` WHERE name='{$x}'")[0]->slug;
			$return_html .= "<td class='custom_cat_col'>";
			$return_html .= "<p><a href='https://inflationhedging.com/category/{$link_slug}/'>{$x} ({$x_value})</a></p>";
			$return_html .= "</td>";
			
			$i++;
			//Every other column end this row and when going back to the top it will create a new one
			if($i%2==0) {
				$return_html .= "</tr>";
			}
		}
	}
	
	if($i%2==1) {
		$return_html .= "</tr>";
	}
	
	$return_html .= "</table>";
	
	return $return_html;
}

?>