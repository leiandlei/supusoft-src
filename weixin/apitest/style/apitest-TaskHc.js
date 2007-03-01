apiList[apiList.length] = {
        'title':'回传:企业列表'
        ,'desc':''
        ,'action':'TaskHc/getTaskHcEList'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'tab'         ,'type':'int'     ,'required': true  ,'test-value':'0'          ,'title':'0未查阅 1已查阅' ,'desc':'' }
            ,{ 'key':'page'        ,'type':'int'     ,'required': false ,'test-value':'1'          ,'title':'页数' ,'desc':'' }
            ,{ 'key':'size'        ,'type':'int'     ,'required': false ,'test-value':'10'         ,'title':'条数' ,'desc':'' }
        ]
      };

apiList[apiList.length] = {
        'title':'回传:消息列表'
        ,'desc':''
        ,'action':'TaskHc/getTaskHcListByEid'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'eid'         ,'type':'int'     ,'required': true  ,'test-value':'0'          ,'title':'' ,'desc':'' }
            ,{ 'key':'page'        ,'type':'int'     ,'required': false ,'test-value':'1'          ,'title':'页数' ,'desc':'' }
            ,{ 'key':'size'        ,'type':'int'     ,'required': false ,'test-value':'10'         ,'title':'条数' ,'desc':'' }
        ]
      };