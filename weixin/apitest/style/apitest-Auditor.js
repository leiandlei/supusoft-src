apiList[apiList.length] = {
        'title':'签到:签到'
        ,'desc':''
        ,'action':'Auditor/taskqd'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'type'        ,'type':'string'     ,'required': true ,'test-value':'1'           ,'title':'签到类型 1上班签到 2下班签到' ,'desc':'' }
            ,{ 'key':'lat'         ,'type':'float'      ,'required': true ,'test-value':'39.9139710000','title':'纬度' ,'desc':'' }
            ,{ 'key':'lng'         ,'type':'float'      ,'required': true ,'test-value':'116.2545600000','title':'精度' ,'desc':'' }
          	,{ 'key':'date'        ,'type':'string'     ,'required': false ,'test-value':'2016-06-28' ,'title':'签到日期' ,'desc':'管理员可用' }
          	,{ 'key':'userID'      ,'type':'int'        ,'required': false ,'test-value':'107'		  ,'title':'用户id'   ,'desc':'管理员可用' }
        ]
      };

apiList[apiList.length] = {
        'title':'签到:列表'
        ,'desc':''
        ,'action':'Auditor/taskqdList'
        ,'method':'post'
        ,'request':[
            ,{ 'key':'tid'       ,'type':'string'     ,'required': true ,'test-value':'10'           ,'title':'审核计划ID' ,'desc':'' }
            ,{ 'key':'ct_id'     ,'type':'string'     ,'required': true ,'test-value':'3'            ,'title':'合同ID' ,'desc':'' }
            ,{ 'key':'qd_date'   ,'type':'string'     ,'required': false,'test-value':'2016-07-04'   ,'title':'签到日期' ,'desc':'' }
            ,{ 'key':'qd_type'   ,'type':'string'     ,'required': false,'test-value':'1'            ,'title':'签到类型 1上班签到 2下班签到' ,'desc':'' }
            ,{ 'key':'page'      ,'type':'int'        ,'required': false,'test-value':'107'          ,'title':'页数'   ,'desc':'' }
            ,{ 'key':'size'      ,'type':'int'        ,'required': false,'test-value':'107'          ,'title':'条数'   ,'desc':'' }
        ]
      };