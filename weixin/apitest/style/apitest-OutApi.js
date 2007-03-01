apiList[apiList.length] = {
        'title':'对外接口:用户新增'
        ,'desc':''
        ,'action':'OutApi/userAdd'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'username'     ,'type':'string'     ,'required': true ,'test-value':'ceshiqiye'    ,'title':'用户名'                   ,'desc':'' }
          	,{ 'key':'password'     ,'type':'md5'        ,'required': true ,'test-value':'123456'       ,'title':'密  码'                   ,'desc':'' }
          	,{ 'key':'name'         ,'type':'string'     ,'required': false,'test-value':'测试企业'     ,'title':'姓名'                     ,'desc':'' }
          	,{ 'key':'status'       ,'type':'tinyint'    ,'required': false,'test-value':'1'            ,'title':'是否启用 1:启用 0:停用'   ,'desc':'' }
          	,{ 'key':'deleted'      ,'type':'tinyint'    ,'required': false,'test-value':'0'            ,'title':'状态 1:删除 0:正常'       ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:用户修改'
        ,'desc':''
        ,'action':'OutApi/userEdit'
        ,'method':'post'
        ,'request':[
        	,{ 'key':'cu_id'        ,'type':'int'        ,'required': true ,'test-value':'1'            ,'title':'用户ID'                       ,'desc':'' }
          	,{ 'key':'username'     ,'type':'string'     ,'required': false,'test-value':'ceshiqiye'    ,'title':'用户名'                   ,'desc':'' }
          	,{ 'key':'password'     ,'type':'md5'        ,'required': false,'test-value':'123456'       ,'title':'密  码'                   ,'desc':'' }
          	,{ 'key':'name'         ,'type':'string'     ,'required': false,'test-value':'测试企业'     ,'title':'姓名'                     ,'desc':'' }
          	,{ 'key':'status'       ,'type':'tinyint'    ,'required': false,'test-value':'1'            ,'title':'是否启用 1:启用 0:停用'   ,'desc':'' }
          	,{ 'key':'deleted'      ,'type':'tinyint'    ,'required': false,'test-value':'0'            ,'title':'状态 1:删除 0:正常'       ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:企业新增'
        ,'desc':''
        ,'action':'OutApi/enterprisesAdd'
        ,'method':'post'
        ,'request':[
            ,{ 'key':'cu_id'            ,'type':'int'       ,'required': true ,'test-value':'33'                                   ,'title':'用户id'   ,'desc':'' }
          	,{ 'key':'work_code'        ,'type':'string'    ,'required': true ,'test-value':'888888888'                            ,'title':'组织机构代码'   ,'desc':'' }
          	,{ 'key':'ctfrom'           ,'type':'string'    ,'required': true ,'test-value':'01000000'                             ,'title':'合同来源'       ,'desc':'' }
          	,{ 'key':'ep_name'          ,'type':'string'    ,'required': true ,'test-value':'测试公司'                             ,'title':'组织名称'       ,'desc':'' }
          	,{ 'key':'ep_oldname'       ,'type':'string'    ,'required': false,'test-value':'测试公司原名'                         ,'title':'组织原名'       ,'desc':'' }
          	,{ 'key':'nature'           ,'type':'string'    ,'required': true ,'test-value':'040105'                               ,'title':'组织性质'       ,'desc':'' }
          	,{ 'key':'ep_level'         ,'type':'string'    ,'required': false,'test-value':'A'                                    ,'title':'客户级别'       ,'desc':'' }
          	,{ 'key':'statecode'        ,'type':'string'    ,'required': true ,'test-value':'156'                                  ,'title':'国家代码'       ,'desc':'' }
          	,{ 'key':'industry'         ,'type':'string'    ,'required': true ,'test-value':'2770；2661；'                         ,'title':'行业代码'       ,'desc':'' }
          	,{ 'key':'delegate'         ,'type':'string'    ,'required': true ,'test-value':'张三'                                 ,'title':'法人代表'       ,'desc':'' }
          	,{ 'key':'ep_amount'        ,'type':'int'       ,'required': true ,'test-value':'150'                                  ,'title':'企业人数'       ,'desc':'' }
          	,{ 'key':'manager_daibiao'  ,'type':'string'    ,'required': true ,'test-value':'李四'                                 ,'title':'管理者代表'     ,'desc':'' }
          	,{ 'key':'phone_daibiao'    ,'type':'string'    ,'required': true ,'test-value':'13145480880'                          ,'title':'手机'           ,'desc':'' }
          	,{ 'key':'email_job'        ,'type':'string'    ,'required': false,'test-value':'10959697@qq.com'                      ,'title':'邮箱'           ,'desc':'' }
          	,{ 'key':'capital'          ,'type':'string'    ,'required': true ,'test-value':'100'                                  ,'title':'注册资本'       ,'desc':'单位万' }
          	,{ 'key':'currency'         ,'type':'string'    ,'required': true ,'test-value':'01'                                   ,'title':'货币单位 01:CNY 人民币 02:USD 美元 03:JPY 日元 04:KRW 韩元 05:EUR 欧元 06:AUD 澳大利亚元 07:CAD 加拿大元 08:HKD 香港元 09:MOP 澳门元 10:TWD 台湾元 11:RUB 卢布 99:OTHER 其他'       ,'desc':'' }
          	,{ 'key':'website'          ,'type':'string'    ,'required': false,'test-value':'www.baidu.com'                        ,'title':'组织网站'       ,'desc':'' }
          	,{ 'key':'note'             ,'type':'string'    ,'required': false,'test-value':'测试备注信息'                         ,'title':'备注信息'       ,'desc':'' }

          	,{ 'key':'ep_phone'         ,'type':'string'    ,'required': true ,'test-value':'13145480880'                          ,'title':'组织电话'       ,'desc':'' }
          	,{ 'key':'ep_fax'           ,'type':'string'    ,'required': true ,'test-value':'010-10101010'                         ,'title':'组织传真'       ,'desc':'' }
          	,{ 'key':'person_mail'      ,'type':'string'    ,'required': false,'test-value':'10959697@qq.com'                      ,'title':'联系邮箱'       ,'desc':'' }
          	,{ 'key':'areaaddr'         ,'type':'string'    ,'required': true ,'test-value':'广东省广州市天河区'                   ,'title':'行政区划'       ,'desc':'' }
          	,{ 'key':'areacode'         ,'type':'string'    ,'required': true ,'test-value':'440106'                               ,'title':'区划代码'       ,'desc':'' }
          	,{ 'key':'ep_addr'          ,'type':'string'    ,'required': true ,'test-value':'广东省广州市天河区'                   ,'title':'注册地址'       ,'desc':'' }
          	,{ 'key':'ep_addr_e'        ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'注册地址英文'   ,'desc':'' }
          	,{ 'key':'ep_addrcode'      ,'type':'string'    ,'required': true ,'test-value':'440106'                               ,'title':'注册地址邮编'   ,'desc':'' }
          	,{ 'key':'cta_addr'         ,'type':'string'    ,'required': true ,'test-value':'广东省广州市天河区'                   ,'title':'通讯地址'       ,'desc':'' }
          	,{ 'key':'cta_addr_e'       ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'通讯地址英文'   ,'desc':'' }
          	,{ 'key':'cta_addrcode'     ,'type':'string'    ,'required': true ,'test-value':'440106'                               ,'title':'通讯地址邮编'   ,'desc':'' }
          	,{ 'key':'gb_addr'          ,'type':'string'    ,'required': true ,'test-value':'广东省广州市天河区'                   ,'title':'办公地址'       ,'desc':'' }
          	,{ 'key':'gb_addr_e'        ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'办公地址英文'   ,'desc':'' }
          	,{ 'key':'gb_addrcode'      ,'type':'string'    ,'required': false,'test-value':'440106'                               ,'title':'办公地址有变'   ,'desc':'' }
          	,{ 'key':'prod_check'     ,'type':'string'    ,'required': false,'test-value':'1'                                    ,'title':'其他地址 1:生产地址 2:服务地址 3:运营地址','desc':'多个用,隔开' }
          	,{ 'key':'prod_addr'        ,'type':'string'    ,'required': false,'test-value':'广东省广州市天河区'                   ,'title':'其他地址'       ,'desc':'' }
          	,{ 'key':'prod_addr_e'      ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'其他地址英文'   ,'desc':'' }
          	,{ 'key':'prod_addrcode'    ,'type':'string'    ,'required': false,'test-value':'440106'                               ,'title':'其他地址邮编'   ,'desc':'' }
          	,{ 'key':'deleted'          ,'type':'tinyint'    ,'required': false,'test-value':'0'                                   ,'title':'状态 1:删除 0:正常','desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:企业修改'
        ,'desc':''
        ,'action':'OutApi/enterprisesEdit'
        ,'method':'post'
        ,'request':[
        	,{ 'key':'eid'              ,'type':'int'       ,'required': true ,'test-value':'1'                                    ,'title':'合同id'       ,'desc':'' }
          	,{ 'key':'work_code'        ,'type':'string'    ,'required': false,'test-value':'888888888'                            ,'title':'组织机构代码'   ,'desc':'' }
          	,{ 'key':'ctfrom'           ,'type':'string'    ,'required': false,'test-value':'01000000'                             ,'title':'合同来源'       ,'desc':'' }
          	,{ 'key':'ep_name'          ,'type':'string'    ,'required': false,'test-value':'测试公司'                             ,'title':'组织名称'       ,'desc':'' }
          	,{ 'key':'ep_oldname'       ,'type':'string'    ,'required': false,'test-value':'测试公司原名'                         ,'title':'组织原名'       ,'desc':'' }
          	,{ 'key':'nature'           ,'type':'string'    ,'required': false,'test-value':'040105'                               ,'title':'组织性质'       ,'desc':'' }
          	,{ 'key':'ep_level'         ,'type':'string'    ,'required': false,'test-value':'A'                                    ,'title':'客户级别'       ,'desc':'' }
          	,{ 'key':'statecode'        ,'type':'string'    ,'required': false,'test-value':'156'                                  ,'title':'国家代码'       ,'desc':'' }
          	,{ 'key':'industry'         ,'type':'string'    ,'required': false,'test-value':'2770；2661；'                         ,'title':'行业代码'       ,'desc':'' }
          	,{ 'key':'delegate'         ,'type':'string'    ,'required': false,'test-value':'张三'                                 ,'title':'法人代表'       ,'desc':'' }
          	,{ 'key':'ep_amount'        ,'type':'int'       ,'required': false,'test-value':'150'                                  ,'title':'企业人数'       ,'desc':'' }
          	,{ 'key':'manager_daibiao'  ,'type':'string'    ,'required': false,'test-value':'李四'                                 ,'title':'管理者代表'     ,'desc':'' }
          	,{ 'key':'phone_daibiao'    ,'type':'string'    ,'required': false,'test-value':'13145480880'                          ,'title':'手机'           ,'desc':'' }
          	,{ 'key':'email_job'        ,'type':'string'    ,'required': false,'test-value':'10959697@qq.com'                      ,'title':'邮箱'           ,'desc':'' }
          	,{ 'key':'capital'          ,'type':'string'    ,'required': false,'test-value':'100'                                  ,'title':'注册资本'       ,'desc':'单位万' }
          	,{ 'key':'currency'         ,'type':'string'    ,'required': false,'test-value':'01'                                   ,'title':'货币单位 01:CNY 人民币 02:USD 美元 03:JPY 日元 04:KRW 韩元 05:EUR 欧元 06:AUD 澳大利亚元 07:CAD 加拿大元 08:HKD 香港元 09:MOP 澳门元 10:TWD 台湾元 11:RUB 卢布 99:OTHER 其他'       ,'desc':'' }
          	,{ 'key':'website'          ,'type':'string'    ,'required': false,'test-value':'www.baidu.com'                        ,'title':'组织网站'       ,'desc':'' }
          	,{ 'key':'note'             ,'type':'string'    ,'required': false,'test-value':'测试备注信息'                         ,'title':'备注信息'       ,'desc':'' }

          	,{ 'key':'ep_phone'         ,'type':'string'    ,'required': false,'test-value':'13145480880'                          ,'title':'组织电话'       ,'desc':'' }
          	,{ 'key':'ep_fax'           ,'type':'string'    ,'required': false,'test-value':'010-10101010'                         ,'title':'组织传真'       ,'desc':'' }
          	,{ 'key':'person_mail'      ,'type':'string'    ,'required': false,'test-value':'10959697@qq.com'                      ,'title':'联系邮箱'       ,'desc':'' }
          	,{ 'key':'areaaddr'         ,'type':'string'    ,'required': false,'test-value':'广东省广州市天河区'                   ,'title':'行政区划'       ,'desc':'' }
          	,{ 'key':'areacode'         ,'type':'string'    ,'required': false,'test-value':'440106'                               ,'title':'区划代码'       ,'desc':'' }
          	,{ 'key':'ep_addr'          ,'type':'string'    ,'required': false,'test-value':'广东省广州市天河区'                   ,'title':'注册地址'       ,'desc':'' }
          	,{ 'key':'ep_addr_e'        ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'注册地址英文'   ,'desc':'' }
          	,{ 'key':'ep_addrcode'      ,'type':'string'    ,'required': false,'test-value':'440106'                               ,'title':'注册地址邮编'   ,'desc':'' }
          	,{ 'key':'cta_addr'         ,'type':'string'    ,'required': false,'test-value':'广东省广州市天河区'                   ,'title':'通讯地址'       ,'desc':'' }
          	,{ 'key':'cta_addr_e'       ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'通讯地址英文'   ,'desc':'' }
          	,{ 'key':'cta_addrcode'     ,'type':'string'    ,'required': false,'test-value':'440106'                               ,'title':'通讯地址邮编'   ,'desc':'' }
          	,{ 'key':'gb_addr'          ,'type':'string'    ,'required': false,'test-value':'广东省广州市天河区'                   ,'title':'办公地址'       ,'desc':'' }
          	,{ 'key':'gb_addr_e'        ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'办公地址英文'   ,'desc':'' }
          	,{ 'key':'gb_addrcode'      ,'type':'string'    ,'required': false,'test-value':'440106'                               ,'title':'办公地址有变'   ,'desc':'' }
          	,{ 'key':'prod_check'     ,'type':'string'    ,'required': false,'test-value':'1'                                    ,'title':'其他地址 1:生产地址 2:服务地址 3:运营地址','desc':'多个用,隔开' }
          	,{ 'key':'prod_addr'        ,'type':'string'    ,'required': false,'test-value':'广东省广州市天河区'                   ,'title':'其他地址'       ,'desc':'' }
          	,{ 'key':'prod_addr_e'      ,'type':'string'    ,'required': false,'test-value':'guangdongshengguangzhoushitianhequ'   ,'title':'其他地址英文'   ,'desc':'' }
          	,{ 'key':'prod_addrcode'    ,'type':'string'    ,'required': false,'test-value':'440106'                               ,'title':'其他地址邮编'   ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:财务信息新增/修改'
        ,'desc':''
        ,'action':'OutApi/metasEdit'
        ,'method':'post'
        ,'request':[
        	  ,{ 'key':'eid'        ,'type':'int'       ,'required': true ,'test-value':'1'               ,'title':'企业ID'       ,'desc':'' }
          	,{ 'key':'grows'      ,'type':'string'    ,'required': false,'test-value':'111111111111'    ,'title':'公司税号'       ,'desc':'' }
          	,{ 'key':'r_add'      ,'type':'string'    ,'required': false,'test-value':'222222222222'    ,'title':'开户地址'       ,'desc':'' }
          	,{ 'key':'r_tel'      ,'type':'string'    ,'required': false,'test-value':'333333333333'    ,'title':'开户电话'       ,'desc':'' }
          	,{ 'key':'bank'       ,'type':'string'    ,'required': false,'test-value':'444444444444'    ,'title':'开户银行'       ,'desc':'' }
          	,{ 'key':'account'    ,'type':'string'    ,'required': false,'test-value':'555555555555'    ,'title':'银行帐号'       ,'desc':'' }
          	,{ 'key':'name_ac'    ,'type':'string'    ,'required': false,'test-value':'666666666666'    ,'title':'开户名称'       ,'desc':'' }
          	,{ 'key':'ac_remark'  ,'type':'string'    ,'required': false,'test-value':'777777777777'    ,'title':'开票备注'       ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:财务信息删除'
        ,'desc':''
        ,'action':'OutApi/metasDel'
        ,'method':'post'
        ,'request':[
        	,{ 'key':'meta_id'    ,'type':'int'       ,'required': true ,'test-value':'1'               ,'title':'财务信息ID'     ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:分场所新增'
        ,'desc':''
        ,'action':'OutApi/enterprisesSiteAdd'
        ,'method':'post'
        ,'request':[
        	  ,{ 'key':'eid'         ,'type':'int'        ,'required': true ,'test-value':'1'            ,'title':'主公司id'        ,'desc':'' }
          	,{ 'key':'ep_name'     ,'type':'string'     ,'required': false,'test-value':'ceshiqiye'    ,'title':'主公司名称'      ,'desc':'' }
          	,{ 'key':'es_type'     ,'type':'tinyint'    ,'required': true ,'test-value':'1000'         ,'title':'分场所类型 1000:固定场所 1001:临时场所'      ,'desc':'' }
          	,{ 'key':'es_name'     ,'type':'string'     ,'required': true ,'test-value':'测试分场所'   ,'title':'分场所名称'      ,'desc':'' }
          	,{ 'key':'es_addr'     ,'type':'string'     ,'required': true ,'test-value':'测试场所地址' ,'title':'地址'            ,'desc':'' }
          	,{ 'key':'es_tel'      ,'type':'string'     ,'required': false,'test-value':'13145480880'  ,'title':'联系电话'        ,'desc':'' }
          	,{ 'key':'es_fax'      ,'type':'string'     ,'required': false,'test-value':'010-10101010' ,'title':'传真'            ,'desc':'' }
          	,{ 'key':'es_person'   ,'type':'string'     ,'required': false,'test-value':'张三'         ,'title':'联系人'          ,'desc':'' }
          	,{ 'key':'es_mobile'   ,'type':'string'     ,'required': false,'test-value':'13145480880'  ,'title':'联系人手机'      ,'desc':'' }
          	,{ 'key':'es_num'      ,'type':'string'     ,'required': true ,'test-value':'50'     	   ,'title':'分现场人数'      ,'desc':'' }
          	,{ 'key':'es_km'       ,'type':'string'     ,'required': false,'test-value':'100'     	   ,'title':'距总部距离'      ,'desc':'' }
          	,{ 'key':'es_scope'    ,'type':'string'     ,'required': false,'test-value':'测试企业范围' ,'title':'分场所范围'      ,'desc':'' }
          	,{ 'key':'es_note'     ,'type':'string'     ,'required': false,'test-value':'测试企业备注' ,'title':'备注信息'        ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:分场所修改'
        ,'desc':''
        ,'action':'OutApi/enterprisesSiteEdit'
        ,'method':'post'
        ,'request':[
        	  ,{ 'key':'es_id'       ,'type':'int'        ,'required': true ,'test-value':'1'            ,'title':'分场所id'        ,'desc':'' }
        	  ,{ 'key':'eid'         ,'type':'int'        ,'required': false,'test-value':'1'            ,'title':'主公司id'        ,'desc':'' }
          	,{ 'key':'ep_name'     ,'type':'string'     ,'required': false,'test-value':'ceshiqiye'    ,'title':'主公司名称'      ,'desc':'' }
          	,{ 'key':'es_type'     ,'type':'tinyint'    ,'required': false,'test-value':'1000'         ,'title':'分场所类型 1000:固定场所 1001:临时场所'      ,'desc':'' }
          	,{ 'key':'es_name'     ,'type':'string'     ,'required': false,'test-value':'测试分场所'   ,'title':'分场所名称'      ,'desc':'' }
          	,{ 'key':'es_addr'     ,'type':'string'     ,'required': false,'test-value':'测试场所地址' ,'title':'地址'            ,'desc':'' }
          	,{ 'key':'es_tel'      ,'type':'string'     ,'required': false,'test-value':'13145480880'  ,'title':'联系电话'        ,'desc':'' }
          	,{ 'key':'es_fax'      ,'type':'string'     ,'required': false,'test-value':'010-10101010' ,'title':'传真'            ,'desc':'' }
          	,{ 'key':'es_person'   ,'type':'string'     ,'required': false,'test-value':'张三'         ,'title':'联系人'          ,'desc':'' }
          	,{ 'key':'es_mobile'   ,'type':'string'     ,'required': false,'test-value':'13145480880'  ,'title':'联系人手机'      ,'desc':'' }
          	,{ 'key':'es_num'      ,'type':'string'     ,'required': false,'test-value':'50'     	   ,'title':'分现场人数'      ,'desc':'' }
          	,{ 'key':'es_km'       ,'type':'string'     ,'required': false,'test-value':'100'     	   ,'title':'距总部距离'      ,'desc':'' }
          	,{ 'key':'es_scope'    ,'type':'string'     ,'required': false,'test-value':'测试企业范围' ,'title':'分场所范围'      ,'desc':'' }
          	,{ 'key':'es_note'     ,'type':'string'     ,'required': false,'test-value':'测试企业备注' ,'title':'备注信息'        ,'desc':'' }
          	,{ 'key':'deleted'     ,'type':'tinyint'    ,'required': false,'test-value':'0'            ,'title':'状态 1:删除 0:正常'       ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:合同新增'
        ,'desc':''
        ,'action':'OutApi/contractAdd'
        ,'method':'post'
        ,'request':[
             { 'key':'eid'               ,'type':'int'        ,'required': true ,'test-value':'1'              ,'title':'企业id'             ,'desc':'' }
        	  ,{ 'key':'ct_code'           ,'type':'string'     ,'required': true ,'test-value':'CA20160001M'    ,'title':'合同编号'           ,'desc':'' }
        	  ,{ 'key':'is_first'          ,'type':'string'     ,'required': true ,'test-value':'y'              ,'title':'是否初次 y是 n否'   ,'desc':'' }
        	  ,{ 'key':'pre_date'          ,'type':'date'       ,'required': true ,'test-value':'2016-07-01'     ,'title':'审核预期'           ,'desc':'' }
          	,{ 'key':'zxfgznbms'         ,'type':'string'     ,'required': true ,'test-value':'10'             ,'title':'体系覆盖职能部门数' ,'desc':'' }
          	,{ 'key':'audit_require'     ,'type':'string'     ,'required': false,'test-value':'审核要求'       ,'title':'审核要求'           ,'desc':'' }
          	,{ 'key':'finance_require'   ,'type':'string'     ,'required': false,'test-value':'财务要求'       ,'title':'财务要求'           ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:合同修改'
        ,'desc':''
        ,'action':'OutApi/contractEdit'
        ,'method':'post'
        ,'request':[
        	,{ 'key':'ct_id'             ,'type':'string'     ,'required': true ,'test-value':'1'              ,'title':'合同id'             ,'desc':'' }
        	,{ 'key':'ct_code'           ,'type':'string'     ,'required': false,'test-value':'CA20160001M'    ,'title':'合同编号'           ,'desc':'' }
        	,{ 'key':'is_first'          ,'type':'string'     ,'required': false,'test-value':'y'              ,'title':'是否初次 y是 n否'   ,'desc':'' }
        	,{ 'key':'pre_date'          ,'type':'date'       ,'required': false,'test-value':'2016-07-01'     ,'title':'审核预期'           ,'desc':'' }
          	,{ 'key':'zxfgznbms'         ,'type':'string'     ,'required': false,'test-value':'10'             ,'title':'体系覆盖职能部门数' ,'desc':'' }
          	,{ 'key':'audit_require'     ,'type':'string'     ,'required': false,'test-value':'审核要求'       ,'title':'审核要求'           ,'desc':'' }
          	,{ 'key':'finance_require'   ,'type':'string'     ,'required': false,'test-value':'财务要求'       ,'title':'财务要求'           ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:体系新增'
        ,'desc':''
        ,'action':'OutApi/contractItemAdd'
        ,'method':'post'
        ,'request':[
             { 'key':'eid'        ,'type':'string'     ,'required': true ,'test-value':'1'              ,'title':'企业'              ,'desc':'' }
        	  ,{ 'key':'ct_id'      ,'type':'string'     ,'required': true ,'test-value':'1'              ,'title':'合同id'              ,'desc':'' }
          	,{ 'key':'audit_ver'  ,'type':'string'     ,'required': true ,'test-value':'A100101'        ,'title':'标准版本'            ,'desc':'' }
          	,{ 'key':'audit_type' ,'type':'string'     ,'required': true ,'test-value':'1001'           ,'title':'审核类型 1001:初审 1004:监一 1005:监二 1007:再认证'  ,'desc':'' }
          	,{ 'key':'cti_code'   ,'type':'string'     ,'required': true ,'test-value':'CA20160001M'    ,'title':'项目编号'            ,'desc':'' }
          	,{ 'key':'total'      ,'type':'string'     ,'required': true ,'test-value':'20'             ,'title':'体系人数'            ,'desc':'' }
          	,{ 'key':'renum'      ,'type':'string'     ,'required': true ,'test-value':'0'              ,'title':'复评次数'            ,'desc':'' }
          	,{ 'key':'is_turn'    ,'type':'string'     ,'required': true ,'test-value':'·0'     	      ,'title':'机构转入 0:否 1:是'   ,'desc':'' }
          	,{ 'key':'scope'      ,'type':'string'     ,'required': true ,'test-value':'营业执照范围'  ,'title':'申请范围'             ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'对外接口:体系修改'
        ,'desc':''
        ,'action':'OutApi/contractItemEdit'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'cti_id'     ,'type':'string'     ,'required': true ,'test-value':'1'              ,'title':'体系id'              ,'desc':'' }
          	,{ 'key':'audit_ver'  ,'type':'string'     ,'required': false,'test-value':'A100101'        ,'title':'标准版本'            ,'desc':'' }
          	,{ 'key':'audit_type' ,'type':'string'     ,'required': false,'test-value':'1001'           ,'title':'审核类型 1001:初审 1004:监一 1005:监二 1007:再认证'  ,'desc':'' }
          	,{ 'key':'cti_code'   ,'type':'string'     ,'required': false,'test-value':'CA20160001M'    ,'title':'项目编号'            ,'desc':'' }
          	,{ 'key':'total'      ,'type':'string'     ,'required': false,'test-value':'20'             ,'title':'体系人数'            ,'desc':'' }
          	,{ 'key':'renum'      ,'type':'string'     ,'required': false,'test-value':'0'              ,'title':'复评次数'            ,'desc':'' }
          	,{ 'key':'is_turn'    ,'type':'string'     ,'required': false,'test-value':'·0'     	      ,'title':'机构转入 0:否 1:是'   ,'desc':'' }
          	,{ 'key':'scope'      ,'type':'string'     ,'required': false,'test-value':'营业执照范围'  ,'title':'申请范围'             ,'desc':'' }
        ]
    };