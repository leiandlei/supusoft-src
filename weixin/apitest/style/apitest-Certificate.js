apiList[apiList.length] = {
        'title':'证书:列表'
        ,'desc':''
        ,'action':'Certificate/getCertificateList'
        ,'method':'post'
        ,'request':[
            ,{ 'key':'unionToken'        ,'type':'string'     ,'required': true ,'test-value':'oMeU-wMfUoHPY0ltERhEHAkyzFtQ'           ,'title':'' ,'desc':'' }
            ,{ 'key':'unionType'         ,'type':'int'        ,'required': true ,'test-value':'4'           ,'title':'' ,'desc':'' }
        ]
      };