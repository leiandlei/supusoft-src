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
		//合同评审状态修改
		case 'eidisregister':
                if( empty($params['id']) || empty($params['status_isregister']) ){
                    $results = ajaxReturn(1,'缺少参数');
                }
                $sql_eidStatus_ht = "update `sp_contract_item` set `isregister`=".$params['status_isregister']." where `cti_id`=".$params['id'];
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
        //合同上报 状态修改
        case 'eidStatus_sb':
                if( empty($params['id']) || empty($params['status']) ){
                    $results = ajaxReturn(1,'缺少参数');
                }
                $sql_eidStatus_ht = "update `sp_task` set `status_sb`=".$params['status']." where `id`=".$params['id'];
                if(mysql_query($sql_eidStatus_ht)){
                    $results = ajaxReturn(0,'成功');
                }else{
                    $results = ajaxReturn(1,'失败');
                }
                //($sql_eidStatus_ht);
            break;
        //微信是否推送 状态修改
        case 'eidif_push':
                if( empty($params['id']) || empty($params['status']) ){
                    $results = ajaxReturn(1,'缺少参数');
                }
                $sql_eidif_push = "update `sp_task` set `if_push`=".$params['status']." where `id`=".$params['id'];
                if(mysql_query($sql_eidif_push)){
                    $results = ajaxReturn(0,'成功');
                }else{
                    $results = ajaxReturn(1,'失败');
                }
                //($sql_eidStatus_ht);
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
                    // $url = 'http://'.$_SERVER['SERVER_NAME'].'/lllCAMS/lll/?c=doc';
                    $newa = substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'CAMS'));
                    $url = 'http://'.$_SERVER['SERVER_NAME'].$newa.'?c=doc';
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
                // $url = 'http://'.$_SERVER['SERVER_NAME'].'/lllCAMS/lll/?c=doc';
                $newa = substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'CAMS'));
                $url = 'http://'.$_SERVER['SERVER_NAME'].$newa.'?c=doc';
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
		//证书删除
		case 'certificatedel':
            $sql1  = "update sp_project set `ifchangecert`=1 where id=".$params['pid'];
            $aaa   = mysql_query($sql1);
			$sql2  = "update sp_certificate set `deleted`=1 where id=".$params['id'];

            if(query($sql2)){
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
                    if($type==2)$i_zsnum = date('Y').'-001';
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

        //人力资源资格更改时间修改
        case 'hrZigeUpdateTime':
            $sql = "update sp_hr_qualification set zige_update='".$params['date']."' where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //人力资源资格更改审核次数
        case 'hrq_shenhe_num':
            $sql = "update sp_hr_qualification set shenhe_num='".$params['date']."' where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
        //人力资源资格更改培训课时
        case 'hrq_peixun_keshi':
            $sql = "update sp_hr_qualification set peixun_keshi='".$params['date']."' where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //培训-获取所有学生
        case 'getTrainingStatus':
            $sql = "select * from sp_training_student where id=".$params['id'];
            $r   = selectOne($sql);
            $results=empty($r)?$results = ajaxReturn(1,'失败'):$results = ajaxReturn(0,'成功',$r);
            break;

        //文档管理 归档修改
        case 'guidangEdit':
            $sql = "update sp_contract set guidang=".$params['value']." where ct_id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //证书审批-评定时间
        case 'get_assess_date':
            $id = $_POST['pid'];
            if( empty($id) ) $results = ajaxReturn(1,'失败');
            $sql = "select eid,iso,audit_type from sp_project where id=".$id;
            $r   = selectOne($sql);
            $sql = "select sp_date from sp_project where eid=".$r['eid']." and iso='".$r['iso']."' and audit_type='1003' order by create_date asc limit 1";
            $r   = selectOne($sql);
            if( empty($r) ){
                $results = ajaxReturn(1,'失败');
            }else{
                $results = ajaxReturn(0,'成功',$r);
            }
            break;

        //合作方-删除
        case 'partnerListdel':
            $sql = "update `sp_partner` set `deleted`=1 where pt_id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
        //合作方-状态修改
        case 'partnerStatus':
            $sql = "update `sp_partner` set `status`=".$params['status']." where pt_id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
        //是否催款-状态修改
        case 'partnerStatuss':

        $sql = "update `sp_partner` set `dunning_status`=".$params['dunning_status']." where pt_id=".$params['id'];
        if(query($sql)){
            $results = ajaxReturn(0,'成功');
        }else{
            $results = ajaxReturn(1,'失败');
        }
        break;

        //人力资源注册资格-状态修改
        case 'zhcestatus':

        $sql = "update `sp_hr_qualification` set `status`=".$params['status']." where id=".$params['id'];
        if(query($sql)){
            $results = ajaxReturn(0,'成功');
        }else{
            $results = ajaxReturn(1,'失败');
        }
        break;

        //合作方-申请管理-删除
        case 'partnerInfoListdel':
            $sql = "update `sp_partner_info` set `deleted`=1 where pti_id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
        //合作方-申请管理-状态修改
        case 'partnerInfoStatus':
            $sql = "update `sp_partner_info` set `status`=".$params['status']." where pti_id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
        //合作方-申请管理-删除(新加sp_partner_enterprises表)
        case 'partnerEnterprisesListdel':
            $sql = "update `sp_partner_enterprises` set `deleted`=1 where pt_id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
        //合作方-项目清单协调单-删除(sp_partner_coordinator)
        case 'partnerxtdEnterprisesListdel':
            $sql = "update `sp_partner_coordinator` set `deleted`=1 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
  		//合作方-请假、培训、外出-删除(sp_task_audit_team)
        case 'leavelistdel':
            $sql = "update `sp_task_audit_team` set `deleted`=1 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        case 'qualificationlistdel':
            $sql = "update `sp_hr_qualification` set `deleted`=1 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
            
        case 'userinfoListdel':
            $sql = "update `sp_examine_user_info` set `deleted`=1 where id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //合作方-申请管理-状态修改(新加sp_partner_enterprises表)
        case 'partnerEnterprisesStatus':
            $sql = "update `sp_partner_enterprises` set `status`=".$params['status']." where pt_id=".$params['id'];
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;
        //合作方-项目协调单-状态修改(新加sp_partner_enterprises表)
        case 'coorEnterprisesStatus':
            $sql = "update `sp_partner_coordinator` set `status`=".$params['status']." where id=".$params['id'];
            // print_r($sql);exit;
            if(query($sql)){
                $results = ajaxReturn(0,'成功');
            }else{
                $results = ajaxReturn(1,'失败');
            }
            break;

        //更改客户申请
        case 'updateshenhexinxi':
            require_once '../../../../framework/Api.class.php';
            $results = Api::httpToApi('Renzhengapply/updateApply',$params);
            var_dump($results);exit;
            break;
            
        default:
            break;
    }
    mysql_close($DB);
    exit( $results );
?>
