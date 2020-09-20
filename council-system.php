<?php
/**
 * Plugin Name: Council Management System
 * Plugin URI: http://www.akkaapps.com
 * Description: plugin to manage DOCM Council System and its members
 * Version: 1.0
 * Author: Vijayalakshmi Satish
 * Author URI: http://www.akkaapps.com
 * License: GPL
 */
 
 
/*  Copyright 2017  Vijayalakshmi Satish  (email : intelviji@gmail.com) */

//To perform action while activating pulgin
define('DOC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
include('admin_functions.php');
function council_activate()
{
	global $wpdb;
	$table_name = $wpdb->prefix . "council"; 
              $table_name1 = $wpdb->prefix . "council_members"; 
$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
	  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}
	
	if ( ! empty( $wpdb->collate ) ) {
	  $charset_collate .= " COLLATE {$wpdb->collate}";
	}
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `council_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
	) $charset_collate;";
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

dbDelta( $sql);

$sql2 = "CREATE TABLE IF NOT EXISTS $table_name1 (
  `id` int(11) NOT NULL AUTO_INCREMENT,
 `council_id` int(11) NOT NULL,
`user_id` int(11) NULL,
 `role` VARCHAR(2) NOT NULL DEFAULT 'M',
`email` VARCHAR(50) NULL,
  PRIMARY KEY (`id`)
	) $charset_collate;";


dbDelta( $sql2);
}
register_activation_hook( __FILE__, 'council_activate' );
add_action('admin_menu', 'council_admin_menu'); 
function council_admin_menu() {  
    // Add a new top-level menu (ill-advised):
	if(is_admin())
	{
    add_menu_page(__('Council Management System','menu-council'), __('Council Management System','menu-council'), 'publish_posts', 'cms-slug', 'cms_page_func' );

 // Add a submenu to the custom top-level menu:
    add_submenu_page('cms-slug', __('Add Councils','menu-council'), __('Add Councils','menu-council'), 'manage_options', 'cms-add-council', 'cms_add_council_func');

    // Add a second submenu to the custom top-level menu:
    add_submenu_page('cms-slug', __('Manage Heads','menu-council'), __('Manage Heads','menu-council'), 'manage_options', 'cms-manage-heads', 'cms_manage_func'); }

add_submenu_page('cms-slug', __('Manage Members','menu-council'), __('Manage Members','menu-council'), 'publish_posts', 'cms-manage-members', 'cms_manage_mem_func');


}
function cms_page_func()
{
	echo '<h1 align="center">COUNCIL MANAGEMENT SYSTEM </h1>';
	echo '<h2 align="center"> Admin can manage council heads and members <br /> Council heads can manage their members.</h2>';
	if(is_admin())
	{
	echo '<a href="'.get_admin_url().'/admin.php?page=cms-add-council">Add Council </a><br />';
	echo '<a href="'.get_admin_url().'/admin.php?page=cms-manage-heads">Manage Council Heads </a><br />';
	echo '<a href="'.get_admin_url().'/admin.php?page=cms-manage-members">Manage Council Members </a><br />';
	}
	else
	{
		echo '<a href="'.get_admin_url().'/admin.php?page=cms-manage-members">Manage Council Members </a><br />';
	}


}
function cms_add_council_func()
{
	
    	echo add_council();
	
}
function cms_manage_func()
{
	echo "<h1 align='center'> Manage Council Heads </h1>";
	
	echo manage_heads();
}
function cms_manage_mem_func()
{
	echo "<h1 align='center'> Manage Council Members </h1>";
	
	echo manage_members();
}
function council_form_func()
{
	global $wpdb;
	$council_table = $wpdb->prefix."council";
	$member_table = $wpdb->prefix."council_members";
$uid = -1;
	$already_mem = "n";
	$form_output='<div class="add_council_form">';
	$coun_id = member_already_exist();
	//echo "councilid=".$coun_id;
	if($coun_id > 0)
	{ 
		$already_mem = "y";		
		$form_output .= do_shortcode('[list_member_council]');
		
	}
	if(isset($_POST['join_council']) && $already_mem == "n")
	{
		$cid = $_POST['council_name'];
		$uid = $_POST['userid'];
		$role = 'M';
$fullname = "Council Member";
		$join_link = "";
		
		if($uid == -1)
		{
			$email = $_POST['council_email'];
			$join_link = "Please <a href='".get_page_link(964)."' >Join in our community </a> to proceed further with social cause.";
		}
		else
		{
			$email = $_POST['email'];
			$userobj = get_user_by('ID',$uid);
			$fullname = $userobj->display_name; 
		}
			
		$res = $wpdb->insert($member_table,array('council_id' => $cid, 'user_id' => $uid, 'role' => $role,'email' => $email),array('%d','%d','%s','%s'));
		
		if($res)
		{
			$status_msg = "Thanks for joining with our 
council. Our council head will contact you asap.";	
			$sub = "Welcome to DOCM Council Team";
			
			$msg = "Dear ".$fullname;
			$msg .= "<br> Thanks for joining with our 
council team.<br />";
			$msg .= $join_link;
			$msg .= "Our Council head will get in touch with you ASAP";
			$msg .= "<br>Your Council Name:".get_councilname_byid($cid);		
			$headers[] = "From: intelviji@gmail.com";
			$headers[] = "Cc:support@akkaapps.com";
			$headers[] = "Content-Type: text/html";
			wp_mail($email,$sub,$msg,$headers);
			
		}
		else
			$status_msg = "Error in Joining the Council. Please initiate the process once again";

	} // join council post check condition
$coun_id = member_already_exist();
	//echo "councilid=".$coun_id;
	if($coun_id > 0)
	{ 
		$already_mem = "y";		
				
	}
$form_output .= '<h2>'.$status_msg.'</h2>';
if($already_mem=='n')
{
	$form_output .= '<form name="addme_in_council" method="post">';
	

	if(is_user_logged_in())
	{
		$current_user = wp_get_current_user();
		$uid = $current_user->ID;
		$email = $current_user->user_email;
		$form_output .= '<input type="hidden" name="email" value="'.$email.'" />';
	}
	else
	{	 
	$form_output .= '<label>Enter Email: </label> <input type="text" size=22 name="council_email" />';
	}
	
$form_output .= '<input type="hidden" name="userid" value="'.$uid.'" />';
$form_output .= display_councils_dropdown();
$form_output .= '<input type="submit" value="Join Council" name="join_council" />';
$form_output .= '</form>';
}
	
$form_output .= '</div>';


return $form_output;
} 
add_shortcode('council_form','council_form_func');
function curPageURL() {
 $pageURL = 'http';
if(isset($_SERVER["HTTPS"] )){
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}
function attach_council_scripts()
{
	wp_enqueue_script( 'council-script', plugins_url('js/council.js', __FILE__), array(), '1.0', true );
}
add_action('admin_enqueue_scripts','attach_council_scripts');