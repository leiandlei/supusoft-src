<?php
// var_dump($_SESSION);
if ($step) {
    if($step=='save'){
        if ($_POST) {
            $value['username'] = $_POST['username'];
            $value['password'] = md5(trim($_POST['password']));
            $value['name'] = $_POST['name'];
            //@zbzytech 冲突已经在前端判断过了 但是有点问题 需要再次使用 用的是AJAX/CHECKUSERNAME
            $exist = $customer->username_exist($value['username']);
            if($exist){
                $REQUEST_URI="?c=customer&a=reg";
                showmsg( '相同的用户名', 'error', $REQUEST_URI, 1 );
            }
            //@zbzytech 此处应该加入权限
            $value['sys'] = 'enterprise:add|enterprise:add|enterprise:list_edit|enterprise:list|enterprise:list_edit|contract:alist|contract:alist|contract:list|certificate:list|certificate:edit|customer:edit|customer:edit_site|customer:edit|enterprise:add|enterprise:list_edit|contract:alist|contract:list|contract:add|contract:edit|audit:progress|enterprise:edit|enterprise:edit|certificate:list|contract:upload';
            $value['ctfrom'] = '01000000';
            $cu_id = $customer->add($value);
            if($cu_id){
                $REQUEST_URI='index.php';
                showmsg( 'success', 'success',$REQUEST_URI,1);
            }else{
                echo 'error';
            }
        }
    }elseif($step=='update'){
            $cu_id = $_SESSION['userinfo']['cu_id'];
            $value['username'] = $_SESSION['userinfo']['username'];
            $value['password'] = md5(trim($_POST['password']));
            $value['name'] = $_POST['name'];
            $result = $customer->edit($cu_id,$value);
            //            var_dump($result);
            //            debug($result);
            if($result){
                $REQUEST_URI='?c=customer&a=edit';
                showmsg( 'success', 'success',$REQUEST_URI,1);

            }else{
                $REQUEST_URI='?c=customer&a=edit';
                showmsg( '修改失败', 'error', $REQUEST_URI, 1 );
            }

    }

}else{
    //@zbzytech 修改一定是登陆之后 必须有对应的session才可以
        $cu_id = current_user('cu_id');
        $cu_info = $customer->get($cu_id);
        //var_dump($cu_info);
        // var_dump($_SESSION);
        $approval_disabled = ( 'yes' == $_SESSION['userinfo']['is_customer'] ) ? '' : 'disabled';
        tpl('customer/reg');
}
