<?php 
/*
Plugin Name: WooCommerce Stars Shortcode
Plugin URI: http://forteweb.us
Description: Creates a shortcode, [woocommerce_rating id="n"],  that displays the rating, in stars, of any WooCommerce product.  [woocommerce_rating] will show the star rating of the current product if applicable.  This plugin requires WooCommerce.  
Version: 0.5
Author: R Thompson
Requires: PHP5, WooCommerce Plugin
License: GPL

Copyright 2012  R Thompson  (email : ruth@wildseapress.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
	add_shortcode('woocommerce_rating', 'roo_stars_shortcode');
}

function roo_stars_shortcode($atts){ 
	global $post;
	
    extract(shortcode_atts(array(  
        "id" => $post->ID, 
		"link" => 'true', 
		"newwindow" => 'true', 
		"alttext" => ""
    ), $atts)); 
		
	$newwindow = !($newwindow === 'false'); // open in a new window unless newwindow is equal to false
	
	if($link==='true'||$link==='false'){//if it isn't true or false, we want to leave it as a string
		$link = ($link === 'true');
	}
	
	if(get_post_type($id)=='product'){
		ob_start();
		roo_print_stars($id, $link, $newwindow, $alttext);
		return ob_get_clean();
	}else{
		return "";
	}

}

function roo_print_stars($id="", $permalink=false, $newwindow=true, $alttext = "" ){
    global $wpdb;
    global $post;
	
	if(empty($id)){
		$id=$post->ID;
	}
	
	if(empty($alttext)){
		$alttext="Be the first to rate ". get_the_title( $id );
	}
		
	if(is_bool($permalink)){
		if($permalink){
			$link = get_permalink( $id );
		}		
	}else{
		$link = $permalink;
		$permalink = true;
	}
	
	$target = "";		 
	if($newwindow){
		$target="target='_blank' ";
	}
	
	
	if(get_post_type( $id )=='product'){	
		$count = $wpdb->get_var("
			SELECT COUNT(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = $id
			AND comment_approved = '1'
			AND meta_value > 0
		");

		$rating = $wpdb->get_var("
			SELECT SUM(meta_value) FROM $wpdb->commentmeta
			LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE meta_key = 'rating'
			AND comment_post_ID = $id
			AND comment_approved = '1'
		");
		
		if($permalink){
			echo "<a href='{$link}'  {$target} >";
		}
		
		echo '<span style="display:inline-block;float:none;" class="starwrapper" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">';
		
		if ( $count > 0 ) {
			$average = number_format($rating / $count, 2);

			echo '<span class="star-rating" title="'.sprintf(__('Rated %s out of 5', 'woocommerce'), $average).'"><span style="width:'.($average*16).'px"><span itemprop="ratingValue" class="rating">'.$average.'</span> </span></span>';

		}else{
			echo '<span class="star-rating-alt-text">'.$alttext.'</span>';
		}
		
		echo '</span>';
		
		if($permalink){
			echo "</a>";
		}
		
	}

}

?>