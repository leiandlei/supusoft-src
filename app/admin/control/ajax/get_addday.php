<?php

/*
*计算注册到期
*/

$day = get_addday(getgp('s_date') , getgp('month') , getgp('day'));
    print_json(array(
        'day' => $day
));