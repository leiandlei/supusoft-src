<?php
//【审核员模块】【删除---】
	$pid = (int)getgp('pid');
	$bf=$audit->get(array('id' => $pid ));
	$audit->del( array('id' => $pid ), $from='shap' );
	log_add($bf[eid], current_user("uid"), "删除项目".$bf[ct_code], NULL, serialize($bf));
	$url=$_SERVER['HTTP_REFERER'];
	showmsg( 'success', 'success', $url );
