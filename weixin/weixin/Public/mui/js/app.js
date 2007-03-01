mui.ready(function()
{
	//跳转事件
	mui(".jump").on('tap', '.href', function() {
		if(!this.getAttribute('url')) return false;
		var params = this.getAttribute('params') ? ('?' + this.getAttribute('params')) : '';
		var styles = new Object();
		styles.top = 0;
		styles.bottom = 0;
		var extras = new Object();
		var href = this.getAttribute('url');
		styles = this.getAttribute('styles') ? this.getAttribute('styles') : styles;
		extras = this.getAttribute('extras') ? this.getAttribute('extras') : extras;
		mui.openWindow({
			id: href,
			url: href + params,
			styles: styles,
			extras: extras
		})
	});
	
	//ajax事件
	mui(".ajax").on('tap','.todo',function()
	{
		var params = this.getAttribute('data-params');if(!params)return false;params = eval('('+params+')');
		if(!params.action)return false;params.taptype = params.taptype?params.taptype:'toast';
		
		var apiParams = {};
		apiParams.type    = params.type?params.type:'';
		apiParams.data    = params.data?params.data:{};
		apiParams.success = params.success?params.success:'';
		apiParams.error   = params.error?params.error:'';
		switch(params.taptype)
		{
			case 'toast':
				api.httpToApi(params.action,apiParams);
				break;
			case 'confirm':
				mui.confirm('确定要执行该操作吗','',['取消','确定'],function(item)
				{
					if(item.index=='1')
					{
						api.httpToApi(params.action,apiParams);
					}
				},'div')
				break;
			default:
				return false;
				break;
		}
	});
});