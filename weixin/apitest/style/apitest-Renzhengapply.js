apiList[apiList.length] = {
        'title':'认证申请:我的申请'
        ,'desc':''
        ,'action':'Renzhengapply/getApply'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'unionToken'    ,'type':'int'     ,'required': false ,'test-value':'oMeU-wMfUoHPY0ltERhEHAkyzFtQ'    ,'title':'' ,'desc':'' }
            ,{ 'key':'unionType'     ,'type':'int'     ,'required': true  ,'test-value':'4'           ,'title':'类型 2QQ 3微博 4微信' ,'desc':'' }
        ]
      };

apiList[apiList.length] = {
        'title':'认证申请:添加申请'
        ,'desc':''
        ,'action':'Renzhengapply/apply'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'unionToken'    ,'type':'int'     ,'required': false ,'test-value':'oMeU-wMfUoHPY0ltERhEHAkyzFtQ'    ,'title':'' ,'desc':'' }
            ,{ 'key':'unionType'     ,'type':'int'     ,'required': true  ,'test-value':'4'           ,'title':'类型 2QQ 3微博 4微信' ,'desc':'' }
            ,{ 'key':'nickName'      ,'type':'int'     ,'required': false ,'test-value':'Kiral'       ,'title':'昵称' 	  ,'desc':'' }
            ,{ 'key':'sex'      	 ,'type':'int'     ,'required': false ,'test-value':'1'	          ,'title':'地址' 	  ,'desc':'' }
            ,{ 'key':'location'      ,'type':'int'     ,'required': false ,'test-value':'北京=>石景山','title':'地址' 	  ,'desc':'' }
            ,{ 'key':'avatar'        ,'type':'int'     ,'required': false ,'test-value':''            ,'title':'头像' 	  ,'desc':'' }
            ,{ 'key':'ep_name'       ,'type':'int'     ,'required': false ,'test-value':'1'           ,'title':'企业名称' ,'desc':'' }
            ,{ 'key':'iso'           ,'type':'int'     ,'required': false ,'test-value':'1'           ,'title':'申请体系' ,'desc':'' }
            ,{ 'key':'tel_person'    ,'type':'int'     ,'required': false ,'test-value':'1'           ,'title':'联系人'   ,'desc':'' }
            ,{ 'key':'tel'           ,'type':'int'     ,'required': false ,'test-value':'1'           ,'title':'联系方式' ,'desc':'' }
            ,{ 'key':'note'          ,'type':'int'     ,'required': false ,'test-value':'1'           ,'title':'备注'     ,'desc':'' }
        ]
      };

apiList[apiList.length] = {
        'title':'认证申请:申请列表(web)'
        ,'desc':''
        ,'action':'Renzhengapply/getApplyToWeb'
        ,'method':'post'
        ,'request':[
            { 'key':'tab'    ,'type':'int'     ,'required': false ,'test-value':'0'    ,'title':'0未受理 1已受理' ,'desc':'' }
        ]
    };

apiList[apiList.length] = {
        'title':'认证申请:更改'
        ,'desc':''
        ,'action':'Renzhengapply/updateApply'
        ,'method':'post'
        ,'request':[
             { 'key':'id'     ,'type':'int'     ,'required': true ,'test-value':'1'    ,'title':'' ,'desc':'' }
            ,{ 'key':'status' ,'type':'int'     ,'required': true ,'test-value':'1'    ,'title':'0未受理 1已受理' ,'desc':'' }
        ]
    };