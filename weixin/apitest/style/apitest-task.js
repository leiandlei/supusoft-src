apiList[apiList.length] = {
        'title':'审核任务:列表'
        ,'desc':''
        ,'action':'Task/getAuditTaskList'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'userID'      ,'type':'int'     ,'required': false ,'test-value':'1'           ,'title':'审核员ID' ,'desc':'管理员可用' }
            ,{ 'key':'is_finish'   ,'type':'int'     ,'required': true ,'test-value':'0'            ,'title':'状态 0未完成 1已完成' ,'desc':'' }
        ]
      };