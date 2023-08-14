<?php

function output_silo(){
	global $wpdb;
	
	//The final string that should be returned
	$return_html = "<table class='silo-style'><tbody><tr><th class='silo-style'>Related Posts</th></tr>";
	
	//Get all categories on site
	$all_categories = $wpdb->get_results("SELECT * FROM `wp_term_taxonomy` WHERE taxonomy='category'");
	
	//Get the number of categories on site
	$number_of_categories = count($all_categories);
	
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
	
	//////////////////////////////////////////////////////////////////////
	//Category
	//////////////////////////////////////////////////////////////////////
	//Get the index of the category
	for($category_index=0; $all_categories[$category_index]->term_taxonomy_id != $taxonomy_id; $category_index++);
	
	//Get the slug of the category
	$category_slug = $wpdb->get_results("SELECT slug FROM `wp_terms` WHERE term_id={$taxonomy_id}")[0]->slug;
	
	//////////////////////////////////////////////////////////////////////
	//Before Category
	//////////////////////////////////////////////////////////////////////
	//Get the index of the category immediately before this category
	$before_category_index;
	if($number_of_categories<=1) //If there's one or less category then set it to the current index
	{
	    $before_category_index = $category_index;
	}
	else if($category_index==0) //If the current category index is 0 then get the last category index
	{
	    $before_category_index = $number_of_categories-1;
	}
	else //Otherwise just set the previous category index to the current - 1
	{
	    $before_category_index = $category_index-1;
	}
	
	//Get the before category taxonomy
	$before_taxonomy_id = $all_categories[$before_category_index]->term_taxonomy_id;
	
	//Get the slug of the category immediately before this category
	$before_category_slug = $wpdb->get_results("SELECT slug FROM `wp_terms` WHERE term_id={$before_taxonomy_id}")[0]->slug;
	
	
	//////////////////////////////////////////////////////////////////////
	//After Category
	//////////////////////////////////////////////////////////////////////
	//Get the index of the category immediately after this category
	$after_category_index;
	if($number_of_categories<=2) //If there's two or less category then set it to the current index
	{
	    $after_category_index = $category_index;
	}
	else if($category_index==($number_of_categories-1)) //If the current category index is the max then get the first category index
	{
	    $after_category_index = 0;
	}
	else //Otherwise just set the previous category index to the current + 1
	{
	    $after_category_index = $category_index+1;
	}
	
	//Get the after category taxonomy
	$after_taxonomy_id = $all_categories[$after_category_index]->term_taxonomy_id;
	
	//Get the slug of the category immediately after this category
	$after_category_slug = $wpdb->get_results("SELECT slug FROM `wp_terms` WHERE term_id={$after_taxonomy_id}")[0]->slug;
	
	//Get all post IDs in this category
	$post_ids_in_category = $wpdb->get_results("SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id={$taxonomy_id}");
	
	//Get the post ID in the before category
	$post_ids_in_before_category = $wpdb->get_results("SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id={$before_taxonomy_id}");
	
	//Get the post ID in the after category
	$post_ids_in_after_category = $wpdb->get_results("SELECT object_id FROM `wp_term_relationships` WHERE term_taxonomy_id={$after_taxonomy_id}");
	
	//Get number of posts in this category
	$posts_in_category = count($post_ids_in_category);
	
	//Get number of posts in the before category
	$posts_in_before_category = count($post_ids_in_before_category);
	
	//Get number of posts in after category
	$posts_in_after_category = count($post_ids_in_after_category);
	
	$first_post_ID_before_post;
	$first_post_ID_before_link;
	if($posts_in_before_category<0 || $before_taxonomy_id==$taxonomy_id)
	{
	    $first_post_ID_before_post=null;
	    $first_post_ID_before_link=null;
	}
	else
	{
	    $first_post_ID_before_post = $wpdb->get_results("SELECT post_title FROM `wp_posts` WHERE post_status='publish' AND post_type='post' AND ID={$post_ids_in_before_category[0]->object_id}")[0]->post_title;
	    $first_post_ID_before_link = $wpdb->get_results("SELECT permalink FROM `wp_yoast_indexable` WHERE object_type='post' AND object_id={$post_ids_in_before_category[0]->object_id}")[0]->permalink;
	}
	
	$first_post_ID_after_post;
	$first_post_ID_after_link;
	if($posts_in_after_category<0 || $after_taxonomy_id==$taxonomy_id)
	{
	    $first_post_ID_after_post=null;
	    $first_post_ID_after_link=null;
	}
	else
	{
	    $first_post_ID_after_post = $wpdb->get_results("SELECT post_title FROM `wp_posts` WHERE post_status='publish' AND post_type='post' AND ID={$post_ids_in_after_category[0]->object_id}")[0]->post_title;
	    $first_post_ID_after_link = $wpdb->get_results("SELECT permalink FROM `wp_yoast_indexable` WHERE object_type='post' AND object_id={$post_ids_in_after_category[0]->object_id}")[0]->permalink;
	}
	
	
	//If there is just this post in this category then return "nothing"
	if($posts_in_category<=1) {
		return " ";
	}
	
	//Cycle through every post in this category and get its link and title
	for ($i = 0; $i < $posts_in_category; $i++) {
		
		//Get the name of the post from posts
		$postname = $wpdb->get_results("SELECT post_title FROM `wp_posts` WHERE post_status='publish' AND post_type='post' AND ID={$post_ids_in_category[$i]->object_id}")[0]->post_title;
		
		
		//Get slug of the post uri from yoast 
		$permalink = $wpdb->get_results("SELECT permalink FROM `wp_yoast_indexable` WHERE object_type='post' AND object_id={$post_ids_in_category[$i]->object_id}")[0]->permalink;
		
		//Add on to the final html that will be published
		if($postname!="" && $permalink!="") {
			$return_html .= "<tr><td><ul><li class='silo-style'><a href='{$permalink}'>{$postname}</a></li></ul></td></tr>";
		}
	}
	
	
	if($first_post_ID_before_post != null)
	{
	    $return_html .= "<tr><td><ul><li class='silo-style'><a href='{$first_post_ID_before_link}'>{$first_post_ID_before_post}</a></li></ul></td></tr>";
	}
	
	if($first_post_ID_after_post != null)
	{
	    $return_html .= "<tr><td><ul><li class='silo-style'><a href='{$first_post_ID_after_link}'>{$first_post_ID_after_post}</a></li></ul></td></tr>";
	}
	
	
	$return_html .= '</tbody></table>';
	
	return $return_html;
}

?>
