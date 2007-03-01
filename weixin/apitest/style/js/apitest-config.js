var API_URL     = top.location.href+'index.php/Home/';//接口地址
    API_URL     = API_URL.replace(/\/apitest\//g,'/api/');
var SECRET_BROWSER  = 'ksdhbfiuyh98182y379812hi9'//浏览器设备混淆码
var SECRET_PC       = 'ksdhbfiuyh98182y379812hi9'//PC混淆码
var SECRET_ANDROID  = 'ksdhbfiuyh98182y379812hi9'//安卓混淆码
var SECRET_IOS      = 'ksdhbfiuyh98182y379812hi9'//IOS混淆码
var SECRET_OTHER    = 'ksdhbfiuyh98182y379812hi9'//其他混淆码
var USER_RANDCODE   = 'f9823r2ioeoiwaeefadsafeww';//用户信息混淆码
var headerList =[
  {
    "key":'Userid'
    ,"type":'int'
    ,"title":'Userid'
    ,"desc":'当前用户ID，登录后可获得。'
    ,"required":true
    ,"test-value":"1"
    ,"click":null
  }
  ,{
    "key":'Requesttime'
    ,"type":'string'
    ,"title":'Requesttime'
    ,"desc":'请求时的时间戳，单位：秒'
    ,"required":true
    ,"test-value":""
    ,"click":function(){
      $(this).siblings("input").val(parseInt(((new Date()).getTime())/1000));
    }
  }
  ,{
    "key":'Logintime'
    ,"type":'string'
    ,"title":'Logintime'
    ,"desc":'登录时间，时间戳，单位：秒，数据来自服务器'
    ,"required":true
    ,"test-value":""
    ,"click":function(){
      $(this).siblings("input").val(parseInt(((new Date()).getTime())/1000));
    }
  }
  ,{
    "key":'Clientversion'
    ,"type":'string'
    ,"title":'版本号'
    ,"desc":''
    ,"required":true
    ,"test-value":"1.0"
    ,"click":null
  }
  ,{
    "key":'Devicetype'
    ,"type":'string'
    ,"title":'设备类型'
    ,"desc":'1:浏览器设备 2:PC 3:安卓 4:iOS 5:其他 默认浏览器设备'
    ,"required":true
    ,"test-value":"1"
    ,"click":null
  }
  ,{
    "key":'Checkcode'
    ,"type":'string'
    ,"title":'Checkcode'
    ,"desc":'Userid和Logintime组合加密后的产物，用于进行用户信息加密。数据来自服务器'
    ,"required":true
    ,"test-value":""
    ,"click":function(){
      var _headers = getHeaders();
      $(this).siblings("input").val(hex_md5(_headers['Userid']+hex_md5(_headers['Logintime']+USER_RANDCODE)));
    }
  }
  ,{
    "key":'Sign'
    ,"type":'string'
    ,"title":'接口加密校验'
    ,"desc":'取头信息里Clientversion,Devicetype,Requesttime,Userid,Logintime,Checkcode  和 表单数据 \n每个都使用key=value（空则空字符串）格式组合成字符串然后放入同一个数组 \n 并放入私钥字符串后自然排序 \n 连接为字符串后进行MD5加密，获得Sign \n 将Sign也放入头信息，进行传输。'
    ,"required":true
    ,"test-value":""
    ,"click":function(){
      var tmpArr = [];

      var _headers = getHeaders();

      var _headerKeys = ['Clientversion','Devicetype','Requesttime','Userid','Logintime','Checkcode'];
      for (var i in _headerKeys)
      {
        if (_headers[_headerKeys[i]]!==null)
        {
          tmpArr.push(_headerKeys[i]+'='+_headers[_headerKeys[i]]);
        }
      }

      $('form').find('[form-type=field]').each(function(){
        var _key = $(this).val();
        if (_key!='' && $(this).parent().siblings().find("input[type=text]").length>0)
        {
          var _val = $(this).parent().siblings().find("input").val();
          tmpArr.push(_key+'='+_val);
        }
      });

      var _link = $('#link_api_url').val();

      if (_link.indexOf('?')>0)
      {
        var _keyValuesStr = _link.replace(/(.*)?\?(.*)(#.*|$)/g,'$2');
        var _keyValues = _keyValuesStr.split('&');
        for (var i in _keyValues)
        {
          tmpArr.push(_keyValues[i]);
        }
      }
      var secret = '';//默认浏览器设备
      switch(_headers['Devicetype']){
        case 1://浏览器设备
          secret = 'secret='+SECRET_BROWSER;
          break;
        case 2://PC
          secret = 'secret='+SECRET_PC;
          break;
        case 3://安卓
          secret = 'secret='+SECRET_ANDROID;
          break;
        case 4://iOS
          secret = 'secret='+SECRET_IOS;
          break;
        case 5://其他
          secret = 'secret='+SECRET_OTHER;
          break;
        default://浏览器设备
          secret = 'secret='+SECRET_BROWSER;
          break;
      }
      tmpArr.push(secret);
      tmpArr = tmpArr.sort();
      var tmpArrString = tmpArr.join('');
      var tmpArrMd5 = hex_md5( tmpArrString );
      $(this).siblings("input").val(tmpArrMd5);
      console.log(tmpArr,tmpArrString,tmpArrMd5)
    }
  }
  // ,{
  //    "key":'Is_sql_print'//参数key值
  //   ,"type":'string'//参数key值类型
  //   ,"title":'Is_sql_print'//参数标题
  //   ,"desc":'是否打印sql'//参数描述
  //   ,"required":true
  //   ,"test-value":"0"
  //   ,"click":null
  // }
];
var apiList = [
      {
        "title":'example:test'
        ,"desc":''
        ,"action":'apitest.php'
        ,"method":"post"
        ,"request":[
          {
            "key":'name'
            ,"type":'string'
            ,"title":'name'
            ,"desc":''
            ,"required":true
            ,"test-value":"zhangsan"
          }
          ,{
            "key":'password'
            ,"type":'md5'
            ,"title":'password'
            ,"desc":''
            ,"required":true
            ,"test-value":"123456"
          }
          ,{
            "key":'avatar'
            ,"type":'file'
            ,"title":'avatar'
            ,"desc":''
            ,"required":true
            ,"test-value":""
          }
          ,{
            "key":'photos[]'
            ,"type":'file'
            ,"title":'avatar'
            ,"desc":''
            ,"required":true
            ,"test-value":""
          }
          ,{
            "key":'age'
            ,"type":'int'
            ,"title":'age'
            ,"desc":''
            ,"required":true
            ,"test-value":"29"
          }
          ,{
            "key":'content'
            ,"type":'string'
            ,"title":'content'
            ,"desc":''
            ,"required":true
            ,"test-value":"内容"
          }
        ]
      }
    ];