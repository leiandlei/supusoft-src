<?php
// var_dump($_SESSION);
if ($a == 'index' or $a == 'line' or $a == 'top') {
     tpl($a);
} else {

    $op = getgp('op');
    
    //如op为NULL则赋值main
    !$op && $op = 'main';
    //@zbzytech 这里加入三重判断 main uc customer,其中uc main是原有的
    if('customer' == $op || isset($_SESSION['userinfo']['is_customer'])){
        $left_nav = $left_nav['customer'];
        $op = 'customer';
        tpl('customer_left');
    } elseif ('uc' != $op) {
        //echo 'customer';
        //var_dump($_SESSION);
        //var_dump($left_nav);
                //LY 普通用户左侧权限控制
        $left_nav = $left_nav[$op];
        tpl('left');
    } else {

        $menus = $items = array();
        $query = $db->query("SELECT * FROM sp_user_menus WHERE uid = '" . current_user('uid') . "' ORDER BY vieworder");
        while ($rt = $db->fetch_array($query)) {
            if ('menu' == $rt['mtype']) {
                $menus[$rt['id']] = $rt;
            } else {
                isset($items[$rt['parent_id']]) or $items[$rt['parent_id']] = array();
                $items[$rt['parent_id']][$rt['id']] = $rt;
            }
        }
        $left_nav = array();
        if ($menus) {
            foreach ($menus as $mid => $menu) {
                if (!$items[$mid])
                    continue;
                $left_nav[$mid] = array(
                    'name' => $menu['name'],
                    'options' => array()
                );
                foreach ($items[$mid] as $iid => $item) {
                    $left_nav[$mid]['options'][$iid] = array(
                        $item['name'],
                        $item['jump'],
                        $item['target']
                    );
                }
            }
        }
        tpl('left_menu');
    }
}
?>