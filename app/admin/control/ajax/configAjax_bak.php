<?php
	require_once 'config.ajax.php'; 
	$event = null;
    if (array_key_exists('event', $_REQUEST)) {
        $event = $_REQUEST['event'];
        $params  = array();
        if(array_key_exists('data_params',$_REQUEST))$params = $_REQUEST['data_params'];
    }
    if (is_null($event)) {
        exit();
    }

    $results = null;
    switch ($event) {
    	//合同确认 状态修改
    	case 'eidStatus_ht':
    			if( empty($params['id']) || empty($params['status']) ){
    				$results = ajaxReturn(1,'缺少参数');
    			}
    			$sql_eidStatus_ht = "update `sp_contract` set `status_ht`=".$params['status']." where `ct_id`=".$params['id'];
    			if(mysql_query($sql_eidStatus_ht)){
    				$results = ajaxReturn(0,'成功');
    			}else{
    				$results = ajaxReturn(1,'失败');
    			}
    		break;

        //合同纸件确认 状态修改
        case 'eidStatus_zj':
                if( empty($params['id']) || empty($params['status']) ){
                    $results = ajaxReturn(1,'缺少参数');
                }
                $sql_eidStatus_ht = "update `sp_contract` set `status_zj`=".$params['status']." where `ct_id`=".$params['id'];
                if(mysql_query($sql_eidStatus_ht)){
                    $results = ajaxReturn(0,'成功');
                }else{
                    $results = ajaxReturn(1,'失败');
                }
            break;

        //培训管理-基础信息登记
        case 'training_add':
            if( $params[1]['name']=='tid' && $params[1]['value'] != 0 && $params[1]['value'] != '' ){
                unset($params[0]);
                $sql = "update `sp_training` set";
                foreach ($params as $key => $value) {
                    if($key <= 1)continue;
                    $sql .= " `".$value['name']."`='".$value['value']."',";
                }
                $sql = substr($sql,0,strlen($sql)-1);
                $sql .= ' where `tid`='.$params[1]['value'];
                $query = query($sql);
            }else{
                unset($params[0],$params[1]);
                $query = add_serializeArray('sp_training',$params);
            }
            if($query){
                    $results = ajaxReturn(0,'成功');
                }else{
                    $results = ajaxReturn(1,'失败');
                }
            break;

        //培训管理-删除
        case 'training_del':
                $sql = 'delete from `sp_training` where `tid`='.$params['tid'];
                if(query($sql)){
                    $results = ajaxReturn(0,'成功');
                }else{
                    $results = ajaxReturn(1,'失败');
                }
            break;

        //模板批量下载
        case 'docdown':
                $urlArray = '';
                $file = array();
                foreach ($params as $value) {
                    $url = 'http://'.$_SERVER['SERVER_NAME'].'/lllCAMS/lll/?c=doc';
                    $valueArray = explode('|', $value['value']);
                    foreach ($valueArray as $str) {
                        $strArray = explode(':', $str);
                        $url .= '&'.$strArray[0].'='.$strArray[1];
                    }
                    $urlArray[] = $url.'&downs=1';
                }

                foreach ($urlArray as $value) {
                    $file[] = getUrlContent($value);
                }
                if( !empty($file) ){
                    $results = ajaxReturn(0,'成功',dirname($file[0]));
                }else{
                    $results = ajaxReturn(1,'失败');
                }
            break;

        //模板批量下载  一阶段 二阶段 初审
        case 'docdownlist':
                $url = 'http://'.$_SERVER['SERVER_NAME'].'/lllCAMS/lll/?c=doc';
                $file = $urlArray = array();

                $valueArray = explode('|', $params['a']);
                foreach ($valueArray as $str) {
                    $urlArray[] = $url.'&a='.$str.'&downs=1&ct_id='.$params['ct_id'].'&tid='.$params['tid'];
                }

                foreach ($urlArray as $value) {
                    $file[] = getUrlContent($value);
                }
                if( !empty($file) ){
                    $results = ajaxReturn(0,'成功',dirname($file[0]));
                }else{
                    $results = ajaxReturn(1,'失败');
                }
            break;

        //合同列表待评审改为待受理
        case 'statusTo0':
            $sql = "update sp_contract set status=0 where ct_id=".$params['id'];
            if(query($sql)){
                    $results = ajaxReturn(0,'成功');
                }else{
                    $results = ajaxReturn(1,'失败');
                }
            break;

        //审核员财务统计修改totalmoney
        case 'eidshytotalMoney':

            $sql = "update sp_shytj_detail set totalMoney=".$params['totalMoney']." where id=".$params['id'];
            if(query($sql)){
                    $results = ajaxReturn(0,'成功');
                }else{
                    $results = ajaxReturn(1,'失败');
                }
            break;

        case 'eidshyistodo':

            $sql = "update sp_shytj_detail set is_todo=".$params['is_todo']." where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //文档管理 删除文档
        case 'docmanagedel':
            $sql = "update sp_docmanage set `status`=0 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //文档管理 修改权重
        case 'docmanageEditWeight':
            $sql = "update sp_docmanage set `weight`=".$params['weight']." where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //培训管理 课程删除
        case 'lessonListdel':
            $sql = "update `sp_training_lesson` set `status`=0 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //培训管理 课程文件删除
        case 'lessonInfoFiledel':
            $sql_file  = "select `file` from `sp_training_lesson` where `id`=".$params['id'];
            $allFileID = selectOne($sql_file);
            $array_allFileID = explode(',',$allFileID['file']);
            $key = array_search($params['fileid'],$array_allFileID);
            if(isset($key))@array_splice($array_allFileID,$key,1);
            $sql_update = "update `sp_training_lesson` set `file`='".implode(',',$array_allFileID)."' where `id`=".$params['id'];
            if(query($sql_update)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //培训管理 学生删除
        case 'studentListdel':
            $sql = "update `sp_training_student` set `status`=0 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //培训管理 学员文件删除
        case 'studentInfoFiledel':
            $sql_file  = "select `file` from `sp_training_student` where `id`=".$params['id'];
            $allFileID = selectOne($sql_file);
            $array_allFileID = explode(',',$allFileID['file']);
            $key = array_search($params['fileid'],$array_allFileID);
            if(isset($key))@array_splice($array_allFileID,$key,1);
            $sql_update = "update `sp_training_student` set `file`='".implode(',',$array_allFileID)."' where `id`=".$params['id'];
            if(query($sql_update)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //培训管理 培训删除
        case 'infoListdel':
            $sql = "update `sp_training_info` set `status`=0 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //培训管理 学员文件删除
        case 'infoInfoFiledel':
            $sql_file  = "select `file` from `sp_training_info` where `id`=".$params['id'];
            $allFileID = selectOne($sql_file);
            $array_allFileID = explode(',',$allFileID['file']);
            $key = array_search($params['fileid'],$array_allFileID);
            if(isset($key))@array_splice($array_allFileID,$key,1);
            $sql_update = "update `sp_training_info` set `file`='".implode(',',$array_allFileID)."' where `id`=".$params['id'];
            if(query($sql_update)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
            
            

        //获取身份证信息
        case 'getcardInfo':
            $card = $params['card'];
            if(isIdCard($card)){
                $sql  = "select * from `sp_training_student` where `s_card`='".$card."'";
                $info = selectOne($sql);
                if( empty($info) ){
                    $age = date('Y')-substr($card,6,4);
                    $results = ajaxReturn(1,'',array('age'=>$age));
                }else{
                    $results = ajaxReturn(0,'',$info);
                }
            }else{
                $results = ajaxReturn(2,'身份证错误');
            }
            break;

        //培训-获取证书编号
        case 'getZhenShuNum':
            $key  = 4;
            $type = $params['type'];
            if( !empty($type) && !in_array($type,array(3)) ){
                if($type==2)$key=3;
                $sql = "select i_zsnum from sp_training_info where status=1 and i_zstype=".$type." and i_zsnum like '%".date('Y')."%' ORDER BY i_zsnum desc limit 1";
                extract(selectOne($sql), EXTR_SKIP);
                if( !empty($i_zsnum) ){
                    $arr_i_zsnum = explode('-',$i_zsnum);
                    $str_y       = empty($arr_i_zsnum[0])?date('Y'):$arr_i_zsnum[0];
                    $str_num     = $str = empty($arr_i_zsnum[1])?1:$arr_i_zsnum[1]+1;
                    for ($i=0; $i < $key-(strlen($str)); $i++) {
                        $str_num = '0'.$str_num;
                    }
                    $i_zsnum     = $str_y.'-'.$str_num;
                }else{
                    $i_zsnum     = date('Y').'-0001';
                    if($type==2)$i_zsnum     = date('Y').'-001';;
                }
            }else{
                    $i_zsnum     = '';
            }
            $results = ajaxReturn(0,'',$i_zsnum);
            break;

        //人力资源资格状态修改
        case 'hrZigeUpdate':
            $sql = "update sp_hr_qualification set zige_update='".date('Y-m-d')."' where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
    	default:
    		break;
    }
    mysql_close($DB);
	exit( $results );
?>
