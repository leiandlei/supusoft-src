<?php
///////////////////////////////////////////////系统顶部导航/////////////////////////////////////
$top_nav           = array(
    'uc' => array(
        'name' => '个人中心',
        'icon' => 'a01',
        'src' => 'uc'
    ),
    'main' => array( ///////////////系统主要业务
        'name' => '认证业务', //业务名称
        'icon' => 'a00', //业务图标
        'src' => 'main', //暂时没有启用
        /*'single' => array(
            array(
                'name' => '进度查询',
                'url' => '?c=audit&a=progress',
                'is_stop' => '0' //权限使用
            ),
            array(
                'name' => '人员行程',
                'url' => '?c=audit&a=list_hr_plan',
                'is_stop' => '' //权限使用
            )
        ) *///单页个别操作
    ),

    // 'oasys' => array(
    //    'name' => 'OA业务',
    //     'icon' => 'a03',
    //     'src' => 'uc'
    // )

    // 'customer' => array(
    //    'name' => '认证申请（测试）',
    //     'icon' => 'a03',
    //     'src' => 'customer',
    //     //  'single' => array(
    //     //     array(
    //     //         'name' => '认证进度查询',
    //     //         'url' => '?c=audit&a=progress&eid=0&ep_name=测试企业&cti_code=&te_dates=&te_datee=',
    //     //         'is_stop' => '' //权限使用
    //     //     )
    //     // ), //单页个别操作
    // )
);
/////////////////////////////////////系统主要左部导航配置/////////////////////////
// LY：数组参数1为名字；2为关联目录；3为是否显示
// $left_nav['oasys'] = array(
//     'appName' => 'OA管理',
//     'oa' => array(
//         'name' => 'OA管理',
//         'options' => array(
//             array(
//                 '公告管理',
//                 '?c=notice&a=list',
//                 '1'
//             ),
//             array(
//                 '文档管理(上传文档)',
//                 '?c=files&a=list',
//                 '1'
//             ),
// 			array(
//                 '文档查询',
//                 '?c=files&a=dlist',
//                 '1'
//             ),
// 			array(
//                 '文档列表',
//                 '?c=file_list',
//                 '1'
//             ),
//             array(
//                 '修改密码',
//                 '?c=sys&a=resetpw',
//                 '1'
//             )
//         )
//     )
// );

//@zbzytech 加入企业信息模块
// $left_nav['customer'] = array(
//     'appName' => '认证申请',
//     'basic_information' => array(
//         'name' => '基本信息（测试）',
//         'options' => array(
//             array(
//                 '修改密码（测试）',
//                 '?c=customer&a=edit',
//                 '1'
//             ),
//             array(
//                 '关联公司（测试）',
//                 '?c=customer&a=edit_site&eid=1',
//                 '0'
//             ),
//              array(
//                 '资质上传（测试）',
//                 '?c=customer&a=edit$eid=1',
//                 '0'
//             )
//         )

//     ),//end_basic_information
//     'certification_application' => array(
//         'name' => '认证申请（测试）',
//         'options' => array(
//             array(
//                 '添加企业（测试）',
//                 '?c=enterprise&a=add',
//                 '1'
//             ),
//             array(
//                 '企业列表（测试）',
//                 '?c=enterprise&a=list_edit',
//                 '1'
//             ),
//             array(
//                 '合同登记（测试）',
//                 '?c=contract&a=alist',
//                 '1'
//             ),
//             array(
//                 '合同查询（测试）',
//                 '?c=contract&a=list',
//                 '1'
//             ),
//              array(
//                 '合同添加（测试）',
//                 '?c=contract&a=add',
//                 '0'
//             ),

//         )
//     ),//end_certification_application
//     'certification_result' => array(
//         'name' => '认证结果（测试）',
//         'options' => array(
//             array(
//                 '进度查询（测试）',
//                 '?c=audit&a=progress',
//                 '1'
//             ),
//             array(
//                 '文档下载（测试）',
//                 '?c=enterprise&a=edit',
//                 '0'
//             ),
//             array(
//                 '费用查询（测试）',
//                 '?c=enterprise&a=edit',
//                 '0'
//             ),
//              array(
//                 '证书查询（测试）',
//                 '?c=certificate&a=list',
//                 '1'
//             ),

//         )
//     ),//end_certification_application
// );

//三方认证项目配置信息
$left_nav['main']  = array(
    'appName' => '三方认证',
    'single' => array(
    'name' => '行程进度',
    'options' => array(
            array(
                 '进度查询',
                 '?c=audit&a=progress',
                 '1' //权限使用
            ),
            array(
                 '人员行程(全)',
                 '?c=audit&a=list_hr_plan',
                 '1' //权限使用
            ),
        ) //单页个别操作
    ),
    'hezuofang' => array(
        'name' => '合作方',
        'options' => array(
            array(
                 '机构人员行程',
                 '?c=audit&a=list_hr_plan&b=hezuofang',
                 '1' //权限使用
            ),
            array(
                '新建合作方',
                '?c=partner&a=partner_edit',
                '1'
            ),
            array(
                '合作方管理',
                '?c=partner&a=partner_list',
                '1'
            ),
            array(
                '新建申请',
                '?c=partner&a=apply_edit',
                '1'
            ),
            array(
                '新建申请(新增)',
                '?c=partner&a=apply_add',
                '1'
            ),
            array(
                '申请管理',
                '?c=partner&a=apply_list',
                '1'
            ),
            array(
                '申请管理(新增)',
                '?c=partner&a=apply_list1',
                '1'
            ),
            array(
                '项目清单协调单',
                '?c=concert_sheet&a=hezuofang_xtd',
                '1'
			),
        )
    ),
    'enterprise' => array(
        'name' => '客户管理',
        'options' => array(

            array(
                '客户信息登记',
                '?c=enterprise&a=add',
                '1'
            ),
            array(
                '客户信息编辑',
                '?c=enterprise&a=list_edit',
                '1'
            ),
            array(
                '客户信息查询',
                '?c=enterprise&a=list',
                '1'
            ),
            array(
                '客户文档查询',
                '?c=enterprise&a=list_attach',
                '1'
            ),
            array(
                '客户信息删除',
                '?c=enterprise&a=del',
                '0'
            ),
            array(
                '客户文档删除',
                '?c=enterprise&a=delattach',
                '0'
            ),
            array(
                '编辑客户信息',
                '?c=enterprise&a=edit',
                '0'
            )
        )
    ),
    'contract' => array(
        'name' => '合同评审',
        'options' => array(

            array(
                '合同登记',
                '?c=contract&a=alist',
                '1'
            ),
            array(
                '合同编辑',
                '?c=contract&a=edit',
                '0'
            ),
            array(
                '合同查询',
                '?c=contract&a=list',
                '1'
            ),
            array(
                '变更评审及所有项目',
                '?c=contract&a=add_review',
                '1',
                '?c=contract&a=edit_scope'
            ),
            //array( '变更评审', '?c=contract&a=edit_scope','0'),
            array(
                '合同费用登记',
                '?c=cost&a=add_list',
                '1'
            ),
            array(
                '合同费用查询',
                '?c=cost&a=list',
                '1'
            ),
			 array(
                '专业对照',
                '?c=contract&a=use_code',
                '0'
            ),
            array(
                '合同评审',
                '?c=contract&a=review',
                '0'
            ),
            array(
                '合同审批',
                '?c=contract&a=approval',
                '0'
            ),
            array(
                '合同删除',
                '?c=contract&a=del',
                '0'
            ),
            array(
                '客户文档上传',
                '?c=contract&a=upload',
                '0'
            )
        )
    ),
    'preserve' => array(
        'name' => '客服维护',
        'options' => array(
            array(
                '监督维护',
                '?c=audit&a=list_super',
                '1'
            ),
            array(
                '再认证维护',
                '?c=audit&a=list_ifcation',
                '1'
            ),
            array(
                '监督维护操作',
                '?c=audit&a=edit_super',
                '0'
            ),
            array(
                '监督维护删除',
                '?c=audit&a=del',
                '0'
            ),
            array(
                '再认证维护登记',
                '?c=audit&a=add',
                '0'
            ),
            array(
                '再认证维护编辑',
                '?c=audit&a=edit_ifcation',
                '0'
            )
        )
    ),
    'auditarrange' => array(
        'name' => '审核方案',
        'options' => array(
            array(
                '未安排项目',
                '?c=audit&a=list_wait_arrange',
                '1'
            ),
            array(
                '审核方案',
                '?c=task&a=list&status=1',
                '1'
            ),
            array(
                '审核项目查询',
                '?c=audit&a=list_audit_project',
                '1'
            ),
            array(
                '项目派人查询',
                '?c=audit&a=project_send_query',
                '1'
            ),
            array(
                '增加特殊审核项',
                '?c=audit&a=list_contract_item',
                '1',
                '?c=audit&a=edit_item'
            ),
         /*   array(
                '导出劳务费计算表',
                '?c=audit&a=create_labor_cost',
                '1'
            ),*/
            array(
                '审批项目',
                '?c=task&a=edit_approval',
                '0'
            ),
             array(
                '批量审批项目',
                '?c=ajax&a=task_batch_approval',
                '0'
            ),
             array(
                '批量退回审批',
                '?c=ajax&a=task_batch_unapproval',
                '0'
            ),
            array(
                '审核项目删除',
                '?c=audit&a=del',
                '0'
            ),
            array(
                '项目派人',
                '?c=audit&a=edit_send',
                '0'
            ),
            array(
                '项目派人查询(操作)',
                '?c=audit&a=project_send_query',
                '0'
            ),
            /*array(
                '人员行程',
                '?c=audit&a=list_hr_plan',
                '0'
            )*/
        )
    ),
     'development' => array(
        'name' => '费用结算',
        'options' => array(
            array(
                '项目费用结算',
                '?c=development&a=feiyong',
                 '1' 
            ),
            array(
                '审核员费用结算',
                '?c=development&a=shenheyuan',
                '1' 
            ),
            array(
                '预算单',
                '?c=development&a=yusuansheet',
                '1' 
            ),
            array(
                '结算单',
                '?c=development&a=jiesuansheet',
                '1' 
            )
        )
    ),
    	'auditor' => array(
        'name' => '审核员',
        'options' => array(
            array(
                '行程规划',
                '?c=auditor&a=trip',
                '1'
            ),
            array(
                '审核员公告',
                '?c=auditor&a=notice',
                '1'
            ),
            array(
                '审核任务',
                '?c=auditor&a=task',
                '1'
            ),
            array(
                '审核天数统计',
                '?c=auditor&a=experience',
                '0'
            ),
            array(
                '业务代码申请',
                '?c=auditor&a=appcode',
                '1'
            ),
            array(
                '我的资料',
                '?c=auditor&a=my',
                '1'
            ),
            array(
                '注册资格',
                '?c=auditor&a=reg',
                '1'
            ),
            array(
                '专业能力',
                '?c=auditor&a=code',
                '1'
            ),
            array(
                '专业经历',
                '?c=experience&a=glist',
                '1'
            ),
            array(
                '请假登记',
                '?c=auditor&a=leave_edit',
                '0'
            ),
            array(
                '请假查询',
                '?c=auditor&a=leave_list',
                '0'
            ),
            array(
                '审核任务操作',
                '?c=auditor&a=task_edit',
                '0'
            ),
            array(
                '评定任务操作',
                '?c=auditor&a=edit',
                '0'
            ),
            array(
                '审核任务文档上传',
                '?c=auditor&a=upfile',
                '0'
            ),
            array(
                '审核任务审核信息沟通',
                '?c=auditor&a=task_save',
                '0'
            ),
            array(
                '审核任务评定问题',
                '?c=auditor&a=task_finish',
                '0'
            ),
            array(
                '文档上传',
                '?c=auditor&a=upattach',
                '0'
            ),
            array(
                '下载文档',
                '?c=attachment&a=down',
                '0'
            ),
            array(
                '批量下载',
                '?c=attachment&a=batdown'
            ),
            array(
                '添加专业经历',
                '?c=experience&a=gedit',
                '0',
                '?c=experience&a=gsave'
            ),
            array(
                '添加教育经历',
                '?c=experience&a=jedit',
                '0',
                '?c=experience&a=jsave'
            ),
            array(
                '添加审核经历',
                '?c=experience&a=sedit',
                '0',
                '?c=experience&a=ssave'
            ),
            array(
                '添加培训经历',
                '?c=experience&a=pedit',
                '0',
                '?c=experience&a=psave'
            ),
            array(
                '查看教育经历',
                '?c=experience&a=jlist',
                '0'
            ),
            array(
                '查看审核经历',
                '?c=experience&a=slist',
                '0'
            ),
            array(
                '查看培训经历',
                '?c=experience&a=plist',
                '0'
            ),
            array(
                '查看培训经历',
                '?c=experience&a=glist',
                '0'
            ),
            array(
                '审核员上传头像 ',
                '?c=hr&a=uphrphoto',
                '0'
            )
            ,array(
                '我的薪资'
                ,'?c=finance&a=myList'
                ,'1'
            )
            ,array(
                '我的项目薪资'
                ,'?c=finance&a=myItemList'
                ,'1'
            )
        )
    ),
	// 'plan' => array(
 //        'name' => '计划审批',
 //        'options' => array(
 //            array(
 //                '计划审批',
 //                '?c=task&a=list_plan',
 //                '1'
 //            ),
	// 	)
	// ),
    'assess' => array(
        'name' => '评定管理',
        'options' => array(
            array(
                '资料收回',
                '?c=archive&a=list',
                '1'
            ),
            array(
                '登记资料收回',
                '?c=archive&a=edit',
                '0'
            ),
            array(
                '认证评定',
                '?c=assess&a=list',
                '1'
            ),
            array(
                '认证评定操作',
                '?c=assess&a=edit',
                '0'
            ),
            array(
                '评定问题',
                '?c=assess&a=question',
                '1'
            ),
            array(
                '经理审核',
                '?c=assess&a=list_tg',
                '1'
            ),
			/*  array(
                '评分标准',
                '?c=assess&a=ver_list',
                '1'
            ), */
            array(
                '下载文档',
                '?c=attachment&a=down',
                '0'
            )
        )
    ),
    'cert' => array(
        'name' => '证书管理',
        'options' => array(
            array(
                '证书登记',
                '?c=certificate&a=alist',
                '1',
                '?c=certificate&a=edit'
            ),
            array(
                '监督发证',
                '?c=certificate&a=list_super',
                '0',
                '?c=certificate&a=edit_super'
            ),
			array(
                '审批未通过',
                '?c=certificate&a=lists',
                '0',
                '?c=certificate&a=edit'
            ),
            array(
                '证书审批',
                '?c=certificate&a=approval_list',
                '1',
                '?c=certificate&a=edit'
            ),
			
			array(
                '证书查询',
                '?c=certificate&a=list',
                '1',
                '?c=certificate&a=edit'
            ),
            array(
                '证书邮寄',
                '?c=certificate&a=elist',
                '1'
            ),
			array(
                'pdf扫描归档',
                '?c=certificate&a=save_file',
                '1'
            ),
            array(
                '监督邮寄',
                '?c=certificate&a=audit_elist',
                '1'
            ),
			 // array(
                // '报告邮寄',
                // '?c=task&a=task_elist',
                // '1'
            // ),
            array(
                '监督维护不接受',
                '?c=certificate&a=list_super',
                '1',
                '?c=change&a=add|?c=change&a=save'
            ),
			array(
                '再认证维护不接受',
                '?c=certificate&a=list_ifcation',
                '1',
                '?c=change&a=add|?c=change&a=save'
            ),
			array(
                '应暂停项目',
                '?c=certificate&a=pushed',
                '1',
                '?c=change&a=add|?c=change&a=save'
            ),
            array(
                '应注销证书',
                '?c=certificate&a=annul',
                '1',
                '?c=change&a=add'
            ),
            array(
                '应恢复证书',
                '?c=certificate&a=restore',
                '1',
                '?c=change&a=add'
            ),
            array(
                '证书删除',
                '?c=certificate&a=del',
                '0'
            ),
            array(
                '证书邮寄操作',
                '?c=certificate&a=eedit',
                '0'
            )
        )
    ),
    'change' => array(
        'name' => '变更管理',
        'options' => array(
            array(
                '证书变更',
                '?c=certificate&a=clist',
                '1'
            ),
            array(
                '证书变更查询',
                '?c=change&a=list&status=0&c=change',
                '1'
            ),
            array(
                '证书变更删除',
                '?c=change&a=del',
                '0'
            ),
            array(
                '证书变更操作',
                '?c=change&a=add',
                '0'
            ),
            array(
                '证书变更保存操作',
                '?c=change&a=save',
                '0'
            )
        )
    ),
    'finance' => array(
        'name' => '财务收费',
        'options' => array(
            array(
                '财务收费登记',
                '?c=finance&a=plist',
                '1',
                '?c=finance&a=edit|?c=finance&a=save'
            ),
            array(
                '财务收费明细',
                '?c=finance&a=dlist',
                '1',
                '?c=finance&a=edit|?c=finance&a=save'
            ),
            array(
                '财务发票邮寄',
                '?c=finance&a=elist',
                '1'
            ),
            array(
                '审核员财务审核',
                '?c=finance&a=shyItemList&event=list',
                '1'
            ),
            array(
                '审核员项目发放',
                '?c=finance&a=shytjList&event=list',
                '1'
            )
        )
    ),
    //LY-加入培训管理模块
        'training' => array(
        'name' => '培训管理',
        'options' => array(
             array(
                '课程登记',
                '?c=training&a=lessonEdit',
                '1'
            )
            ,array(
                '课程查询',
                '?c=training&a=lessonList',
                '1'
            )
            ,array(
                '学员登记',
                '?c=training&a=studentEdit',
                '1'
            )
            ,array(
                '学员查询',
                '?c=training&a=studentList',
                '1'
            )
            ,array(
                '培训登记',
                '?c=training&a=infoEdit',
                '1'
            )
            ,array(
                '培训查询',
                '?c=training&a=infoList',
                '1'
            )
            ,array(
                '发证查询',
                '?c=training&a=infoIssueList',
                '1'
            )
        )
        
    ),
    'people' => array(
        'name' => '人力资源',
        'options' => array(
            array(
                '人员登记',
                '?c=hr&a=add',
                '1',
                '?c=hr&a=add'
            ),
            array(
                '人员查询',
                '?c=hr&a=list',
                '1'
            ),
            array(
                '人员编辑',
                '?c=hr&a=edit',
                '0'
            ),
            array(
                '注册资格登记',
                '?c=hr_qualification&a=alist',
                '1',
                '?c=hr_qualification&a=edit'
            ),
            array(
                '注册资格查询',
                '?c=hr_qualification&a=list&status=1',
                '1'
            ),
            array(
                '资格状态查询',
                '?c=hr_qualification&a=zige_status_list',
                '1'
            ),
            array(
                '业务代码登记',
                '?c=hr_code&a=alist',
                '1',
                '?c=hr_code&a=edit'
            ),
            array(
                '业务代码查询',
                '?c=hr_code&a=list',
                '1'
            ),
            array(
                '业务代码申请管理',
                '?c=hr_code&a=clist',
                '1',
                '?c=hr_code&a=app_edit'
            ),
            array(
                '人员专业经历查询',
                '?c=hr_exp&a=glist',
                '1'
            ),
            array(
                '审核经历查询',
                '?c=audit&a=project_send_query',
                '1'
            ),
            array(
                '业务代码删除',
                '?c=hr_code&a=del',
                '0'
            ),
            array(
                '人员专业经历删除',
                '?c=hr_exp&a=gdel',
                '0'
            ),
            array(
                '人员删除',
                '?c=hr&a=del',
                '0'
            ),
            array(
                '小类申请删除',
                '?c=auditor&a=app_del',
                '0'
            ),
			array(
                '请假登记',
                '?c=hr&a=leave_hr_list',
                '1'
            ),
            array(
                '请假查询',
                '?c=hr&a=leave_list',
                '1'
            ),
            array(
                '培训讲课登记',
                '?c=hr&a=train_hr_list',
                '1'
            ),
            array(
                '培训讲课查询',
                '?c=hr&a=train_list',
                '1'
            ),
			array(
                '人员外出登记',
                '?c=hr&a=carbon_hr_list',
                '1'
            ),
            array(
                '人员外出查询',
                '?c=hr&a=carbon_list',
                '1'
            )
        )
    ),
    'examine' => array(
        'name' => '考核管理',
        'options' => array(
            
            array(
               '考核创建',
               '?c=examine&a=usercreate',
               '1'
           ),
           array(
               '考核详情',
               '?c=examine&a=userlist',
               '1'
           )
        )
    ),
    'export' => array(
        'name' => '报表管理',
        'options' => array(
        	array(
                '项目清单',
                '?c=export&a=xmqd',
                '1'
            ),
            array(
                '月报',
                '?c=export&a=report',
                '1'
            ),
            array(
                '年报',
                '?c=baobiao&a=nianbao',
                '1'
            ),
            array(
                '证书报表',
                '?c=baobiao&a=certificate',
                '1'
            ),
            array(
                '获证企业名录',
                '?c=export&a=mdir',
                '1'
            ),
			array(
                '审核计划上报',
                '?c=export&a=plan_report',
                '1'
            ),
			 array(
                '审核工作汇总表',
                '?c=task&a=task_report',
                '1'
            ),
            array(
                '证书年报',
                '?c=export&a=year_report',
                '1'
            ),
            array(
                '审核员年报',
                '?c=export&a=auditor_report',
                '1'
            ),
            array(
                '合同同期比较',
                '?c=export&a=contract',
                '1'
            ),
            array(
                '证书同期比较',
                '?c=export&a=certificate',
                '1'
            )
        )
    ),
    'docmanage'=>array(
            'name'=>'文档管理',
            'options'=>array(
                    array(
                        '新建程序',
                        '?c=docmanage&a=doc_edit',
                        '1'
                    )
                    ,array(
                        '编辑程序',
                        '?c=docmanage&a=doc_list_edit',
                        '1'
                    )
                    ,array(
                        '查看程序',
                        '?c=docmanage&a=doc_list',
                        '1'
                    )
                    
                    ,array(
                        '新建表单',
                        '?c=docmanage&a=from_edit',
                        '1'
                    )
                    ,array(
                        '编辑表单',
                        '?c=docmanage&a=from_list_edit',
                        '1'
                    )
                    ,array(
                        '查看表单',
                        '?c=docmanage&a=from_list',
                        '1'
                    )

                    ,array(
                        '新建常用文档',
                        '?c=docmanage&a=cy_edit',
                        '1'
                    )
                    ,array(
                        '编辑常用文档',
                        '?c=docmanage&a=cy_list_edit',
                        '1'
                    )
                    ,array(
                        '查看常用文档',
                        '?c=docmanage&a=cy_list',
                        '1'
                    )

                    ,array(
                        '归档查询',
                        '?c=docmanage&a=guidang_list',
                        '1'
                    )
                )
        ),

    'weixin' => array(
        'name' => '微信管理',
        'options' => array(
            array(
                '签到管理',
                '?c=qiandao&a=list',
                '1'
            ),
            array(
                '客户申请信息',
                '?c=weixin&a=kehushenqing',
                '1'
            ),
            array(
                '审核员信息',
                '?c=weixin&a=shenheyanxinxi',
                '1'
            ),
            array(
                '客服留言',
                '?c=weixin&a=kefuliuyan',
                '1'
            )
        )
    ),
    
    'system' => array(
        'name' => '系统管理',
        'options' => array(
            array(
                '系统配置',
                '?c=setting',
                '1'
            ),
            array(
                '权限管理',
                '?c=sys&a=list',
                '1'
            ),
            array(
                '系统日志',
                '?c=sys&a=loglist',
                '1'
            ),
            array(
                '计划任务',
                '?c=cron&a=list',
                '1'
            )
            // array(
                // '数据导入',
                // '?m=imp&c=imp',
                // '1'
            // )
        )
    ),
    'notice' => array(
        'name' => '公告管理',
        'options' => array(
            array(
                '公告管理',
                '?c=notice&a=list',
                '1'
            ),
            // array(
            //     '文档管理(上传文档)',
            //     '?c=files&a=list',
            //     '1'
            // ),
            // array(
            //     '文档查询',
            //     '?c=files&a=dlist',
            //     '1'
            // ),
            // array(
            //     '文档列表',
            //     '?c=file_list',
            //     '1'
            // )
        )
    ),
     'development1' => array(
        'name' => '开发功能',
        'options' => array(
            array(
                '合作方结算',
                '?c=development&a=hezuofang',
                '1' 
            ),
            array(
                '财务统计',
                '?c=development&a=tongji',
                '1' 
            )
        )
    ),
    
    // 'check'	=> array(
    //     'name'		=> '系统检测',
    //     'options'	=> array(
    //     array( '项目标准版本-体系', '?c=check&a=Project' ,'1'),
    //     array( '检测监察-计划时间', '?c=check&a=Super' ,'1'),
    //     )
    // ),
);