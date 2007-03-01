<?php
/* 
* @Author: mantou
* @Date:   2017-12-14 17:32:33
* @Last Modified by:   mantou
* @Last Modified time: 2017-12-15 10:44:23
*/
$file_arr = array(
    //"SH-001"=>"审核方案策划和审核任务下达书",
    //"SH-002"=>"审核方案策划与实施记录及有效期内体系运行情况评价报告",
    "SH-002-1"=>"QMS审核方案策划与实施记录",
    "SH-002-2"=>"EMS审核方案策划与实施记录",
    "SH-002-3"=>"OHSMS审核方案策划与实施记录",
    "SH-003"=>"管理体系文件审查报告",
    //"SH-004"=>"审核通知书",
    //"SH-005"=>"缴纳认证费用通知单",
    "SH-006"=>"审核员规范声明",
    "SH-007"=>"管理体系审核计划",
    "SH-008"=>"管理体系审核前专业培训记录",
    "SH-009"=>"现场审核检查单",
    "SH-010"=>"首次会议签到表",
    "SH-011"=>"末次会议签到表",
    "SH-012"=>"不符合项报告",
    "SH-013"=>"观察项报告",
    "SH-014"=>"组织认证证书 子证书表达要求说明",
    "SH-015"=>"多场所组织证书附件表达要求说明",
    "SH-016"=>"一阶段审核报告",
    "SH-017"=>"管理体系审核报告",
    "SH-018"=>"末次会议记录",
    "SH-019"=>"认证组织信息确认、变更反馈单",
    "SH-020"=>"获证客户需求及相关意见反馈卡",
    "SH-021"=>"现场审核差旅费报销清单",
    "SH-022"=>"审核员评价表",
    "SH-023"=>"管理体系初审 再认证审核档案清单",
    "SH-024"=>"管理体系监督审核档案清单",  
    "FJ-001"=>"审核一致性要点提示(内部审核员)",  
    "FJ-002"=>"CCAA审核经历记录表",
    "HB000-1"=>"临时场所分布表",
    "HB000-2"=>"多场所分布表",

);
$tid      = getgp('tid');
$ct_id    = getgp('ct_id');
if($tid){
	$form_file = $a_link = $a_link_2017 = "";
    foreach ($file_arr as $k => $val) 
    {
        $form_file .= '<tr><td width="300">';
        $form_file .= $k . " " . $val;
        $form_file .= "</td><td>";
        $form_file .= '<input type="hidden" name="sort[]" value="' . $k . '"/><input type="file" name="archive[]" />';
        $form_file .= "</td><td><span>";

        if ($span[$k])
            $form_file .= $span[$k] . "(已上传)";
        else
            $form_file .= "无";
        $form_file .= "</span><br/></td></tr>";
        $a_link .= "<li>
                    
                      <a href='?c=doc&a=$k&ct_id=$ct_id&tid=$tid&banben=1'>$k $val</a>
                    </li>";
    }
}
if(getgp('type')=='shenpi')
{
    tpl('ajax/select_shenpi_2017');
}else if(getgp('type')=='hebiao')
{
    tpl('ajax/select_hebiao_2017');
}else if(getgp('type')=='pingding')
{
    tpl('ajax/select_pingding_2017');
}else{
    tpl('ajax/select_shenbiao_2017');
}

?>
