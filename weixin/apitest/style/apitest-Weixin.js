apiList[apiList.length] = {
        'title':'微信:照片回传'
        ,'desc':''
        ,'action':'Weixin/zhaopianhuichuan'
        ,'method':'post'
        ,'request':[
          	,{ 'key':'unionToken'  ,'type':'string'      ,'required': true ,'test-value':'oMeU-wMfUoHPY0ltERhEHAkyzFtQ'     ,'title':'openID' ,'desc':'' }
            ,{ 'key':'unionType'   ,'type':'string'      ,'required': true ,'test-value':'4'                                ,'title':'类型 登录方式：2QQ 3微博 4微信' ,'desc':'' }
            ,{ 'key':'url'         ,'type':'string'      ,'required': true ,'test-value':'http://mmbiz.qpic.cn/mmbiz_png/9AqKibX22rjMZDTh5xgz0neiaNXicaDWJ6Qd8jXH22XpmickdWZNmhKqcJoBmHVGfF0gg6ZpEJHORKfaKYmGjfuibgw/0'                   ,'title':'图片地址'    ,'desc':'' }
        ]
      };