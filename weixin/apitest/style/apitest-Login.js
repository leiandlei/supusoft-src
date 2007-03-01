apiList[apiList.length] = {
        'title':'登录:登录'
        ,'desc':''
        ,'action':'Login/login'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'user'              ,'type':'string'     ,'required': true ,'test-value':'admin'         ,'title':'用户名' ,'desc':'' }
          	,{ 'key':'password'          ,'type':'md5'        ,'required': true ,'test-value':'zgrz001'       ,'title':'密  码' ,'desc':'' }
          	,{ 'key':'login_type'        ,'type':'string'     ,'required': true ,'test-value':'stuff'         ,'title':'stuff机构 customer企业或合作方' ,'desc':'' }
        ]
      };
apiList[apiList.length] = {
        'title':'登录:第三方登录'
        ,'desc':''
        ,'action':'Login/login_unionlogin'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'unionToken'        ,'type':'string'     ,'required': true ,'test-value':'oMeU-wMfUoHPY0ltERhEHAkyzFtQ'         ,'title':'openID' ,'desc':'' }
          	,{ 'key':'unionType'         ,'type':'int'        ,'required': true ,'test-value':'4'        ,'title':'类型 登录方式：2QQ 3微博 4微信' ,'desc':'' }
          	,{ 'key':'user'              ,'type':'string'     ,'required': false,'test-value':'admin'         ,'title':'用户名' ,'desc':'' }
          	,{ 'key':'password'          ,'type':'string'        ,'required': false,'test-value':'zgrz001'       ,'title':'密  码' ,'desc':'' }
        ]
      };