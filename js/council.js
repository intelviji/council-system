var $ = jQuery.noConflict();
$(document).ready(function(){
$("#addform").hide();
$("#assign_div").hide();
$("#assign_mem_div").hide();
$("#addHead").hide();
$("#list_div").slideDown("medium");
$("#addcouncil").click(function(){
	$("#addform").slideDown("slow");
});
$("#manage_heads").click(function(){
	$("#assign_div").toggle("slow");
});
$("#manage_member").click(function(){
	$("#assign_mem_div").toggle("slow");
});


});
