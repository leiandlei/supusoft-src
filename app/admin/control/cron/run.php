<?php
if (file_exists(APP_DIR . "/cron/{$_REQUEST['run_script']}.php")) {
    require_once(APP_DIR . "/cron/{$_REQUEST['run_script']}.php");
    echo '已经手动运行：' . $_REQUEST['run_script'];
} else {
    echo '任务不存在';
}