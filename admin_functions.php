<?php
$council_table = $wpdb->prefix . "council"; 
$member_table = $wpdb->prefix."council_members";
/* ----------------------------------------------- */
function display_councils_dropdown($args)
{
	global $wpdb;
	$output = "";
	$rows = $wpdb->get_results("SELECT * FROM ".	$GLOBALS['council_table']);
	if(!isset($args))
		$args = "council_name";		
	$output .= '<select name="'.$args.'">';
	foreach($rows as $row)
	{ 
	$output .= '<option value="'.$row->id.'">'.$row->council_name.'</option>';
	} 
$output .= '</select>';
return $output;
}
/* ----------------------------------------------- */
function get_councilname_byid($id)
{
	global $wpdb;
	$res = $wpdb->get_row("SELECT council_name FROM ".	$GLOBALS['council_table']." WHERE id=".$id);
	$cname= $res->council_name;
	return $cname;

}
/* ----------------------------------------------- */
/* checks if the member is already exist in the council_member table and returns the council id */
function member_already_exist()
{
	global $wpdb;
$c_id=0;
	$current_user = wp_get_current_user();
	$uid = $current_user->ID;
	$sql = "SELECT id,council_id FROM ".$GLOBALS['member_table']." WHERE user_id=".$uid;
	//echo "sql =".$sql;
	$res = $wpdb->get_results($sql);
	if($wpdb->num_rows >= 1)
	{
		foreach($res as $r)
		$c_id = $r->council_id;
	}
			
return $c_id;
 
}
/* ----------------------------------------------- */
function list_mymember_func()
{
	global $wpdb;
	/*$atts = shortcode_atts( array(
		'uid' => -1
	
	), $atts, 'list_member_council' ); */
	$current_user = wp_get_current_user();
	$uid = $current_user->ID;

	$form_output = "";
	$url = curPageURL();
	$arr_url = explode("?",$url);
	
if(isset($_GET['act']))
{
	$action = $_GET['act'];
 if($action == "remove")
	{
		$userid = $_GET['uid'];
		$result = $wpdb->delete($GLOBALS['member_table'],array('user_id' => $userid),array('%d'));
		if(false === $res)
			$form_output .= "Error in Server. Initiate the delete process again. Thanks";
	
		else
		$form_output .= "You have been removed from Council Team. We welcome you once again with our team as we dont want to miss any conscious soul in the World. <a href='".$arr_url[0]."' > Click here </a>to go back";
exit;
	
	} 	
} // End of GET ISSET if condition
if(isset($_POST['change_council']))
{
	$cname = $_POST['council_name'];
	$res = $wpdb->update($GLOBALS['member_table'],array('council_id' => $cname),array('user_id' => $uid));
	if($res)
		$form_output .= "Council Updated Successfully";
	else $form_output.="Update Failed. Please try again";


} // end of POST Check condition

	$form_output .= '<h2 align="center">Council Dashboard</h2><table cellpadding="15" border="1"><tr><th>Chosen Council Name</th><th>Operations</th></tr>';
	$sql = "SELECT * FROM ".$GLOBALS['member_table']." WHERE user_id = ".$uid;
	
	$res = $wpdb->get_results($sql);
	foreach($res as $row)
	{
		$form_output .= "<tr>";
		if($action == "change")
		{	$form_output .= '<td><form name="council_list" method="post" action="'.$arr_url[0].'">';
			$form_output .= "Select Council To change";
			$form_output .= display_councils_dropdown("council_name");
			$form_output .= '<input type="submit" name="change_council" value="Update" />';
			$form_output .= '</form></td>';
		}else
		$form_output .= "<td>".get_councilname_byid($row->council_id)."</td>";
		$form_output .= '<td><a href="'.$arr_url[0].'?act=change&uid='.$uid.'" >Change Council</a> | <a href="#"> Message Head</a> | <a href="'.$arr_url[0].'?act=remove&uid='.$uid.'">Remove Me</a></td>';

		$form_output .= "</tr>";
	}
	$form_output .= "</table> </form>";
return $form_output;


}
/* ----------------------------------------------- */
add_shortcode("list_member_council","list_mymember_func");
/* ----------------------------------------------- */
function add_council()
{
global $wpdb;
$errmsg ="";
$table_name = $wpdb->prefix . "council"; 
if(isset($_POST['update']))
{
	$name = $_POST['council_name'];
	$cid = $_POST['cid'];
	$result = $wpdb->update($table_name,array("council_name" => $name),array('id' => $cid), array("%s"), array("%d"));
	if(false === $result)
	{
		$errmsg = "Error in Update. Initiate update again";
	}
	else
	{
		$errmsg = "Council name updated successfully.";
	}

}
if(isset($_POST['council_submit']))
{
$table_name = $wpdb->prefix . "council"; 
$cname = $_POST['council_name'];	
if($cname == "")
    { $errmsg = "Please enter council name"; }
$wpdb->insert($table_name,array("council_name" => $cname),array('%s'));
if($wpdb->insert_id)
{ $errmsg = "Council Name is added successfully";  }
else $errmsg = "Error in data insertion"; 

} // end of council_submit isset check condition.

$act = $_GET['act'];
if($act == "edit")
{
	$cid = $_GET['cid'];
	$sql = "SELECT * FROM ".$table_name." WHERE id=".$cid;
	$res = $wpdb->get_row($sql);
$cname = $res->council_name;
}
if($act == "del")
{	
	$cid = $_GET['cid'];
	$res = $wpdb->delete($table_name,array('id'=>$cid),array('%d'));
	if(false === $res)
	{
		$errmsg = "Error in Delete. Initiate delete again";
	}
	else
	{
		$errmsg = "Council name deleted successfully.";
	}


}

?>

<p style="color:maroon;font-size:18px;"><?php echo $errmsg; ?></p>
<button type="button" value="Add Council" id="addcouncil" >Click to Add Council</button>
<br />
<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" id="addform">
<br />
<label>Enter Council Name:	</label><input type="text" size=22 name="council_name" />
<br />
	<input type="submit" value="Add Council" name="council_submit"  />
</form>
<br />
<br />
<h2>List of Councils Added so far</h2>
<table cellpadding="10" cellspacing="0" border="1">
<tr>
<th>Num ID</th>
<th>Council Name </th>
<th>Operations</th>
</tr>
<?php 
$sql = "SELECT * FROM ".$table_name;
$rows = $wpdb->get_results($sql);
if($wpdb->num_rows > 0)
{
	foreach($rows as $row)
	{?>
		<tr>
	  	<td> <?php echo $row->id; ?> </td>
		<td> <?php 
if($act == "edit" && $cid == $row->id)
{ ?>
	<form name="updateform" method="post" action="<?php echo admin_url('admin.php?page=cms-add-council'); ?>" >
	<input type="text" size=25 name="council_name" value="<?php echo $cname; ?>" />
	<input type="hidden" name="cid" value="<?php echo $row->id;  ?>" />
	<input type="submit" name="update" value="Update" />
	</form>
<?php
}
else
echo $row->council_name; ?> </td>
		<td>  <a id="council_edit" href="<?php 
echo admin_url('admin.php?page=cms-add-council&cid='.$row->id.'&act=edit');
?>">Edit</a> | <a id="council_edit" href="<?php 
echo admin_url('admin.php?page=cms-add-council&cid='.$row->id.'&act=del');
?>">Delete</a></td>     
<?php
	} ?>
</tr>
<?php

}
else 
{
?>
<td colspan="2">No councils added </td>	
<?php
}
?>

</table>
<?php
} ?>
<?php 
/* managing heads starts here */
/* ----------------------------------------------- */
function  manage_heads()
{
	global $wpdb;
	$table = $wpdb->prefix . "council_members"; 
$council_table = $wpdb->prefix . "council";
	$arr = "";
	$status_msg = "";
	$action = "";
	if(isset($_GET['act']))
	{
		$action = $_GET['act'];
		$mid = $_GET['mid'];	
		$uid = $_GET['uid'];
		if($action == "delete")
		{
			$mem_query = $wpdb->delete($table,array('id' => $mid),array('%d'));
			$obj = new WP_User($uid);
			$obj->set_role('subscriber');
			if($mem_query)
				$status_msg = "Deleted successfully.";
			else
				$status_msg = "Error. Please initiate the process again";

		}
		
	}
	if(isset($_POST['change_council']))
	{
		$mid = $_POST['mid'];
		$council = $_POST['council_name'];
		$res = $wpdb->update($table,array('council_id' => $council),array('id' => $mid));
		if(false === $res)
		   $status_msg = "Error in Change. Please initiate them";	
		else	
			$status_msg = "Council name changed Successfully";
			
	}
	if(isset($_POST['assignHead_btn']))
	{
		$cid = $_POST['council_name'];		
		$uid = $_POST['users'];
		$role = 'H';
		$user = get_user_by('ID',$uid);
		$obj = new WP_User($uid);
		$obj->set_role('editor');
		$email = "";
		if ( ! empty( $user ) ) {
		$email = $user->user_email;
		}
		$res = $wpdb->insert($table,array('council_id' => $cid,'user_id' => $uid,'role' => $role,'email' => $email),array('%d','%d','%s','%s'));
		if($res)
{
	$join_link = "Please <a href='".get_page_link(964)."' >Join in our community </a> to proceed further with social cause.";

	$status_msg = "You have assigned ".$email." as Head";	
	$sub = "Welcome to DOCM Council Team";
			
			$msg = "Dear Council Head";
			$msg .= "<br> Thanks for joining with our 
council team.<br />";
			$msg .= "Please click the link below to join in our community. If you have not joined before<br />".$join_link;
			$msg .= "";
			$msg .= "<br>Your Council Name:".get_councilname_byid($cid);
			$msg .= "As a Council Head you have previleges to add members under your council and approve the nominated items.";		
			$headers[] = "From: 108@signdc.org";
			$headers[] = "Cc:intelviji@gmail.com";
			$headers[] = "Content-Type: text/html";
			wp_mail($email,$sub,$msg,$headers);
}
		else
			$status_msg = "There is a problem in assignment. Please initiate the process";
	}
	
	?>
<div id="manage_head_div" style="margin-top:30px;height:600px;position:relative;" >
	<h2 align="center" style="font-weght:bold;color:maroon;"><?php echo $status_msg; ?></h2>
<div id="list_div">
<h1 align="center"> List of Council Heads</h1>
<table cellspacing="0" cellpadding="20" border="1">
<tr>
<th> Council Name </th>
<th> Email ID </th>
<th> Council Head Name </th>
<th> Operations </th>
</tr>
<?php	
$query = "SELECT c.council_name,m.id, m.email,m.user_id FROM ".$council_table." as c, ".$table." as m where m.council_id = c.id and m.role='H'";
$head_list = $wpdb->get_results($query);
foreach($head_list as $head)
{ 

?>
		<tr>
		<td> <?php

		if($action == "change" && $mid == $head->id)
		{
$rows = $wpdb->get_results("SELECT * FROM ".$council_table);
?> <form name="change_council" method="post"  action="<?php echo admin_url('admin.php?page=cms-manage-heads'); ?>"  >
			<select name="council_name">
<?php
foreach($rows as $row)
{ ?>
	<option value="<?php echo $row->id; ?>"> <?php echo $row->council_name; ?></option>
<?php
} // end of for each
?>
</select>
<input type="hidden" name="mid" value="<?php echo $_GET['mid'];  ?>" />
<input type="submit" value="Update" name="change_council" />
</form>


	<?php }  // end of if action change check
		else
{ echo $head->council_name; }

  ?>  </td>
		<td> <?php echo $head->email; ?> </td>
		<td> <?php
$user = get_user_by('ID',$head->user_id);
 echo $user->display_name; ?>  </td>
		<td> <a href="<?php echo admin_url('admin.php?page=cms-manage-heads&mid='.$head->id.'&uid='.$head->user_id.'&act=delete');  ?>">Delete </a> | <a href="#">Message </a> | <a href="<?php echo admin_url('admin.php?page=cms-manage-heads&mid='.$head->id.'&uid='.$head->user_id.'&act=change');  ?>">Change Council</a></td>
		</tr>
		
<?php
} ?>
</table>
</div>

	<br /><br />
<button id="manage_heads">Add Council Head</button>
<br /><br />
<div id="assign_div" >
	<form name="assignhead" method="post" id="assignhead" >
	<a href="<?php  echo admin_url('user-new.php');  ?>" id="add_new_user">ADD NEW USER</a> Or Select the existing user in our system and assign them as head. <br /> <br />
<table>
<tr>
<th>
<h3> Select Council </h3> </th>
<th> <h3>Select User from the Dropdown</h3> </th>
</tr>
<tr>
	<?php

$rows = $wpdb->get_results("SELECT * FROM ".$council_table); ?>
<td>
<select name="council_name">
<?php
foreach($rows as $row)
{ ?>
	<option value="<?php echo $row->id; ?>"> <?php echo $row->council_name; ?></option>
<?php
} ?>
</select>
</td>
<?php
	$args = array('role'=>'subscriber');
$user_array = get_users($args);
?>
<td>
<select name="users">
<?php
foreach($user_array as $user)
{ ?>
	<option value="<?php echo $user->ID; ?>"><?php echo $user->display_name.'('.$user->email.' ) '; ?> </option>
<?php
}
?>
</select>
<input type="submit" value="Assign as Head" name="assignHead_btn" id="assignHead_btn" />
</td>
</tr> </table>
</form>
</div>
</div>
<?php
}
/* managing members starts here */
/* ----------------------------------------------- */
function  manage_members()
{
	global $wpdb;
	$table = $wpdb->prefix . "council_members"; 
$council_table = $wpdb->prefix . "council";
	$arr = "";
	$status_msg = "";
	$action = "";
echo '<form name="filter_form" method="post"><br><h4>Select council and click Filter to see the specific council member lists';
echo display_councils_dropdown("council_name_filter");
echo '<input type="submit" value="Filter" name="council_filter" />';
	if(isset($_GET['act']))
	{
		$action = $_GET['act'];
		$mid = $_GET['mid'];	
		$uid = $_GET['uid'];
		if($action == "delete")
		{
			$mem_query = $wpdb->delete($table,array('id' => $mid),array('%d'));
			if($mem_query)
			{
				$status_msg = "Deleted successfully.";
				$sub = "Message from DOCM Council Team";
			
			$msg = "Dear ".$fullname;
			$msg .= "<br> You have been removed from our council team";
			$msg .= "<br />Council Head will contact you on this removal process.";	
			$headers[] = "From: 108@signdc.org";
			$headers[] = "Cc:intelviji@gmail.com";
			$headers[] = "Content-Type: text/html";
			wp_mail($email,$sub,$msg,$headers);

			}
			else
				$status_msg = "Error. Please initiate the process again";

		}
		
	}
	if(isset($_POST['change_council']))
	{
		$mid = $_POST['mid'];
		$council = $_POST['council_name_change'];
		$res = $wpdb->update($table,array('council_id' => $council),array('id' => $mid));
		if(false === $res)
		   $status_msg = "Error in Change. Please initiate them";	
		else	
			$status_msg = "Council name changed Successfully";
			
	}
	if(isset($_POST['assignHead_btn']))
	{
		$cid = $_POST['council_name'];		
		$uid = $_POST['users'];
		$role = 'M';
		$user = get_user_by('ID',$uid);
		$email = "";
		if ( ! empty( $user ) ) {
		$email = $user->user_email;
		}
		$res = $wpdb->insert($table,array('council_id' => $cid,'user_id' => $uid,'role' => $role,'email' => $email),array('%d','%d','%s','%s'));
		if($res)
{
			$status_msg = "You have assigned ".$email." as Member";	
	$sub = "Welcome to DOCM Council Team";
	$msg = "Dear Council Member";
			$msg .= "<br> You are heartly welcome to our DOCM Council Team";
			$msg .= "<br />Council Head will contact you ASAP.";	
			$msg .= "Please Join in our community if you have not joined before";
			$headers[] = "From: 108@signdc.org";
			$headers[] = "Cc:intelviji@gmail.com";
			$headers[] = "Content-Type: text/html";
			wp_mail($email,$sub,$msg,$headers);

	
}
		else
			$status_msg = "There is a problem in assignment. Please initiate the process";
	}
	
	?>
<div id="manage_head_div" style="margin-top:30px;height:600px;position:relative;" >
	<h2 align="center" style="font-weght:bold;color:maroon;"><?php echo $status_msg; ?></h2>
<div id="list_div">
<?php
$ccname = "";
if(isset($_POST['council_filter']))
{
$ccid = $_POST['council_name_filter'];
$ccname .= "-";
$ccname .= get_councilname_byid($ccid);
}
?>
<h1 align="center"> List of Council Members <?php echo $ccname; ?></h1>
<table cellspacing="0" cellpadding="20" border="1">
<tr>
<th> Council Name </th>
<th> Email ID </th>
<th> Council Head Name </th>
<th> Operations </th>
</tr>
<?php	
if(isset($_POST['council_filter']))
{
$cid = $_POST['council_name_filter'];
$query = "SELECT c.council_name,m.id, m.email,m.user_id FROM ".$council_table." as c, ".$table." as m where c.id = ".$cid." and m.role='M' and m.council_id = ".$cid;
}
else
{
$query = "SELECT c.council_name,m.id, m.email,m.user_id FROM ".$council_table." as c, ".$table." as m where m.council_id = c.id and m.role='M'";
}
$head_list = $wpdb->get_results($query);
foreach($head_list as $head)
{ 

?>
		<tr>
		<td> <?php

		if($action == "change" && $mid == $head->id)
		{
?> <form name="change_council" method="post"  action="<?php echo admin_url('admin.php?page=cms-manage-members'); ?>"  />
    <?php echo display_councils_dropdown("council_name_change"); ?>
	<input type="hidden" name="mid" value="<?php echo $_GET['mid'];  ?>" />
<input type="submit" value="Update" name="change_council" />
</form>


	<?php }  // end of if action change check
		else
{ echo $head->council_name; }

  ?>  </td>
		<td> <?php echo $head->email; ?> </td>
		<td> <?php
$user = get_user_by('ID',$head->user_id);
 echo $user->display_name; ?>  </td>
		<td> <a href="<?php echo admin_url('admin.php?page=cms-manage-members&mid='.$head->id.'&uid='.$head->user_id.'&act=delete');  ?>">Delete </a> | <a href="#">Message </a> | <a href="<?php echo admin_url('admin.php?page=cms-manage-members&mid='.$head->id.'&uid='.$head->user_id.'&act=change');  ?>">Change Council</a></td>
		</tr>
		
<?php
} ?>
</table>
</div>

	<br /><br />
<input type="button" id="manage_member" value="Add Council Member" />
<br /><br />
<div id="assign_mem_div" >
	<form name="assignhead" method="post" id="assignhead" >
	Select the user from our community and assign them as Member. <br /> <br />
<table>
<tr>
<th>
<h3> Select Council </h3> </th>
<th> <h3>Select User from the Dropdown</h3> </th>
</tr>
<tr>
<td>
<?php echo display_councils_dropdown("council_name"); ?>
</td>
<?php
$sq = "SELECT user_id FROM ".$GLOBALS['member_table']." WHERE role='M'";
$mems = $wpdb->get_results($sq);
$userids = array();
foreach($mems as $mem)
{
	$userids[]=$mem->user_id;
}
$args = array('role'=>'subscriber','exclude'=>$userids);
$user_array = get_users($args);
?>
<td>
<select name="users">
<?php
foreach($user_array as $user)
{ ?>
	<option value="<?php echo $user->ID; ?>"><?php echo $user->display_name.'('.$user->email.' ) '; ?> </option>
<?php
}
?>
</select>
<input type="submit" value="Assign as Member" name="assignHead_btn" id="assignHead_btn" />
</td>
</tr> </table>
</form>
</div>
</div>
<?php
}
?>
