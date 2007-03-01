<?php
//未安排=》退回到监督维护
$pid = (int)getgp( 'pid' );
$sv_note=getgp( 'sv_note' );
if( $pid ){
	$audit->edit( $pid, array( 'status' => 6,'sv_status'=>'0' ,'sv_note'=>$sv_note) ); 
	print json_encode( array( 'state' => 'ok' ) );
} else {
print <<<EOT
<script type="text/javascript">
alert('项目ID传递错误！');
history.go(-1);
</script>
EOT;
}
?>