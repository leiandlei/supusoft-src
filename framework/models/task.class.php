<?php
//任务模型与任务派人模型： 
class task extends model
{
    //添加任务
    function add($args, $status = '0')
    { 
        $default = array(
            'status' => 1
        );
        $args    = parse_args($args, $default); 
        return $this->db->insert('task', $args);
    }
    /*
     *	添加审核组成员
     *
     */
    function add_team_item($args, $is_log = false)
    { 
        //人员名称
        $default = array(
            'name' => $this->db->get_var("SELECT name FROM `sp_hr` WHERE `id` = '$args[uid]' ")
        );
		//集成项目表信息
		$proj_info=load('audit')->get(array('id'=>$args['pid']));
		
		$default['eid']=$proj_info['eid'];
		$default['iso']=$proj_info['iso'];
		$default['audit_ver']=$proj_info['audit_ver'];
		$default['audit_code']=$proj_info['audit_code'];
		$default['use_code']=$proj_info['use_code'];
		$default['audit_type']=$proj_info['audit_type'];
				
        $args    = parse_args($args, $default);
        return $this->db->insert('task_audit_team', $args);
    }
    /*
     *	编辑审核组成员
     *
     */
    function edit_team_item($tat_id, $args)
    {
        global $db;
        $args    = parse_args($args);
        $af_info = $db->get_row("select * from sp_task_audit_team where id='$tat_id' ");
        $db->update('task_audit_team', $args, array(
            'id' => $tat_id
        ));
        $bf_info = $db->get_row("select * from sp_task_audit_team where id='$tat_id' ");
        $name    = $bf_info[name];
        // 日志
        do {
            ////log_add($bf_info['eid'], 0, "[说明:审核派人-编辑成员:$name]", NULL, serialize($bf_info));
        } while (false);
        return $id;
    }
    function edit($tid, $args, $status = '0')
    {
        global $db;
        $af_info = $this->get(array(
            'id' => $tid
        ));
        $db->update('task', $args, array(
            'id' => $tid
        ));
        $bf_info  = $this->get(array(
            'id' => $tid
        ));
        $sql      = "SELECT cti_code FROM sp_project WHERE tid='$tid' ";
        $res      = $db->query($sql);
        $temp_arr = array();
        while ($row = $db->fetch_array($res)) {
            $temp_arr[] = $row[cti_code];
        }
        // 日志
        do {
            if ($bf_info['status'] == 0) {
                $content = "[说明:审核计划修改](状态:未安排)";
            } elseif ($bf_info['status'] == 1) {
                $content = "[说明:审核计划修改](状态:待派人)";
            } else {
            }
            ////log_add($bf_info['eid'], 0, $content."<项目编号:".implode(',', $temp_arr).">", NULL, serialize($bf_info));
        } while (false);
    }
    function get($args)
    {
        if (empty($args) || !is_array($args))
            return false;
        global $db;
        $where = $db->sqls($args, 'AND');
        $row   = $db->get_row("SELECT * FROM sp_task WHERE $where");
        return $row;
    }
    function del($args)
    {
        if (empty($args) || !is_array($args))
            return false;
        global $db;
        $args = parse_args($args);
        $db->update('task', array(
            'deleted' => 1
        ), $args); //任务标记为删除
    }
    function del_send($tid)
    {
        if (empty($tid))
            return false;
        global $db;
        //删除任务派人
        $db->update('task_audit_team', array(
            'deleted' => 1
        ), array(
            'tid' => $tid
        ));
    }
}
?>