var jsfun = {};

/**
 *根据单选框name获取选中的值
 *@param redioname 单选框name
 * */
jsfun.loadCheckRadioValueByRadioName = function(redioname)
{
	var obj = document.getElementsByName(redioname);
   for(i=0; i<obj.length;i++)if(obj[i].checked)return obj[i].value; 
   return false; 
}

/**
 *根据from返回提交数组
 *@param form 表单
 * */
jsfun.loadFromObjectByFrom=function(form)
{
	var arr = new Object(),elements = form.elements,checkboxName = null;
	for(var i = 0, len = elements.length; i < len; i++)
	{
		field = elements[i];
		// 不发送禁用的表单字段
		if(field.disabled)continue;
		switch(field.type)
		{
			// 不发送下列类型的表单字段 
			case undefined:
			case "button":
			case "submit":
			case "reset":
			case "file":
				break;
			
			// 选择框的处理
			case "select-one":
			case "select-multiple":
				arr[field.name] = this.getSelectValue(field);
				break;

			// 单选、多选和其他类型的表单处理 
			case "checkbox":
				if(checkboxName == null)
				{
					checkboxName = field.name;
					arr[checkboxName] = this.getCheckboxValue(form.elements[checkboxName]);
				}
				break;
			case "radio":
				if(!field.checked)
				{
					break;
				}
			default:
				if(field.name.length > 0)
				{
					arr[field.name] = field.value;
				}
		}
	}
	return arr;
}

/**
 * 生成路径
 **/
jsfun.mkUrl=function(dir,params)
{
	dir = dir?dir:'index';
	dir = api.HOST_URL+'&r='+dir.replace(/(\/|\\)/g,".");
	if(params)dir = dir+'&'+params;
	return dir;
},

/**
 * 获取URL参数 name不填为全部
 **/
jsfun.getURLParams=function(name)
{ 
	var url = location.search; 
	var theRequest = new Object(); 
	if (url.indexOf("?") != -1)
	{ 
		var str = url.substr(1);strs = str.split("&"); 
		for(var i = 0; i < strs.length; i ++)
		{ 
			if(strs[i].split("=")[0]=='i')continue;
			if(strs[i].split("=")[0]=='c')continue;
			if(strs[i].split("=")[0]=='m')continue;
			if(strs[i].split("=")[0]=='do')continue;
			if(strs[i].split("=")[0]=='r')continue;
			theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]); 
		} 
	}
	if(name)
	{
		return theRequest[name]?theRequest[name]:false;
	}else{
		return theRequest;
	}
}
 
/**
 * html反转义
 * @param  string  str 字符转
 * @return string      html
 */
jsfun.htmlDecode=function(str)
{
	var s = "";   
	if (str.length == 0) return "";   
	s = str.replace(/&amp;/g, "&"); 
  	s = s.replace(/&lt;/g, "<");   
  	s = s.replace(/&gt;/g, ">");   
  	s = s.replace(/&nbsp;/g, "　");
  	s = s.replace(/&#39;/g, "\'");   
  	s = s.replace(/&quot;/g, "\"");   
  	s = s.replace(/<br>/g, "\n");
	return s;  
}

/**
 * 写入文件
 * @param  string  path    写入文件路径
 * @param  array   data    要写入的数据
 * @return array           返回是否写入成功
 */
jsfun.filePutContent=function(path,data)
{
	if( !path )false;
	data = data||'';var fso, tf;
	fso  = new ActiveXObject("Scripting.FileSystemObject");
  	tf   = fso.CreateTextFile(path, true);
  	tf.WriteLine( (typeof data=='string')?data:"'"+data+"'" );
  	tf.Close();
  	return jsfun.fileGetContent(path);
}

/**
 * 读取文件
 * @param  string  path    文件路径
 * @return array           返回读取数据
 */
jsfun.fileGetContent=function(path)
{
	if(!path)return false;;
	var fso, ts, s;
	fso = new ActiveXObject("Scripting.FileSystemObject");
	if( !fso.FileExists(path) )false;
	
	ts  = fso.OpenTextFile(path, 1);
	s   = ts.ReadLine();
	return eval('(' + s + ')');
}