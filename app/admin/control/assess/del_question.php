<?php
if($_GET['note_id']){
	$db->update('assess_notes',array('deleted'=>'1'),array('id'=>$_GET['note_id']));
	 showmsg( 'success', 'success', "?c=assess&a=edit&tid=$_GET[tid]&pd_id=$_GET[pd_id]&auditor=$auditor#tab-question" );
}