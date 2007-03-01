apiList[apiList.length] = {
        'title':'公告管理:公告列表'
        ,'desc':''
        ,'action':'Notice/getNotice'
        ,'method':'post'
        ,'request':[
             { 'key':'type'         ,'type':'int'      ,'required': true ,'test-value':'1'                   ,'title':'1:公司公告 2:审核员公告'    ,'desc':'' }
            ,{ 'key':'page'         ,'type':'int'      ,'required': false ,'test-value':'1'                  ,'title':'第几页'    ,'desc':'' }
            ,{ 'key':'size'         ,'type':'int'      ,'required': false ,'test-value':'20'                 ,'title':'每页条数'    ,'desc':'' }
        ]
      };

apiList[apiList.length] = {
        'title':'公告管理:公告详情'
        ,'desc':''
        ,'action':'Notice/getNoticeDetail'
        ,'method':'post'
        ,'request':[
             { 'key':'id'         ,'type':'int'      ,'required': true ,'test-value':'1'                   ,'title':'公告ID'    ,'desc':'' }
        ]
    };