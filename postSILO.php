<?php

function output_silo(){
	global $wpdb;
	
	//The final string that should be returned
	$return_html = "<table class='silo-style'><tbody><tr><th class='silo-style'>Related Posts</th></tr>";
	
	//Get the ID of the post
	$ID = get_the_ID();
	
	//Get the post title
	$post_title = $wpdb->get_results("SELECT post_title FROM `wp_posts` WHERE post_status='publish' AND post_type='post' AND ID={$ID}")[0]->post_title;
	
	//Get the list of taxonomy IDs associated with this post ID
	$tax_ID_results = $wpdb->get_results("SELECT term_taxonomy_id FROM `wp_term_relationships` WHERE object_id={$ID}");
	
	//Get the number of entries in this list
	$number_tax_ID_results = count($tax_ID_results);
	
	//If there are no tax_ID_results then return a smiley
	if($number_tax_ID_results == 0) {
		return ":)";
	}
	
	//Check if each taxonomy ID is associated with 'category'
	for ($i = 0; $i < $number_tax_ID_results; $i++) {
		$taxonomy = $wpdb->get_results("SELECT taxonomy FROM `wp_term_taxonomy` WHERE term_taxonomy_id={$tax_ID_results[$i]->term_taxonomy_id}");
		
		if($taxonomy[0]->taxonomy == "category") {
			$taxonomy_id = $tax_ID_results[$i]->term_taxonomy_id;
		}
	}
	unset($i);
	
	//Get number of posts in this category
	$posts_in_category = $wpdb->get_results("SELECT count FROM `wp_term_taxonomy` WHERE term_taxonomy_id={$taxonomy_id}")[0]->count;
	
	//If there is just this post in this category then return "nothing"
	if($posts_in_category<=1) {
		return " ";
	}
	
	//Get the slug of the category
	$category_slug = $wpdb->get_results("SELECT slug FROM `wp_terms` WHERE term_id={$taxonomy_id}")[0]->slug;
	
	//Get all post IDs in this category
	$post_ids_in_category = $wpdb->get_results("SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id={$taxonomy_id}");
	
	//Cycle through every post in this category and get its link and title
	for ($i = 0; $i < $posts_in_category; $i++) {
		
		//Get the name of the post from posts
		$postname = $wpdb->get_results("SELECT post_title FROM `wp_posts` WHERE post_status='publish' AND post_type='post' AND ID={$post_ids_in_category[$i]->object_id}")[0]->post_title;
		//Get slug of the post uri from yoast 
		$permalink = $wpdb->get_results("SELECT permalink FROM `wp_yoast_indexable` WHERE object_type='post' AND object_id={$post_ids_in_category[$i]->object_id}")[0]->permalink;
		
		//Add on to the final html that will be published
		$return_html .= "<tr><td><ul><li class='silo-style'><a href='{$permalink}'>{$postname}</a></li></ul></td></tr>";
	}
	
	
	$return_html .= '</tbody></table>';
	
	//return $return_html;
	return $return_html;
}

?>
