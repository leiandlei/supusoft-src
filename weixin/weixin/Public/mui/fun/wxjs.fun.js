var wxjsfun = {};
wxjsfun.wx= wx?true:false;
//使用微信上传单张图片
wxjsfun.updateImgae = function(callback)
{
	if(!wxjsfun.wx)return console.log('没有引入微信JSSDK');
	wx.chooseImage(
	{
		count: 1, // 默认9
		sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
		sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
		success: function(res)
		{
			if(res.localIds)
			{
				wx.uploadImage(
				{
					localId: res.localIds.toString(), // 需要上传的图片的本地ID，由chooseImage接口获得
					isShowProgressTips: 1, // 默认为1，显示进度提示
					success: function(res)
					{
						api.httpToApi('common_wechat.savematerial',
						{
							data:{serverid: res.serverId},
							success: function(e){callback(e.data);}
						})
					}
				});
			} else {
				alert('图片选择错误');
			}
		}
	});
}

//微信支付
wxjsfun.Pay = function(params,callback)
{
	if(!wxjsfun.wx)return console.log('没有引入微信JSSDK');
	wx.chooseWXPay(
	{
	    timestamp: params.timeStamp, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
	    nonceStr: params.nonceStr, // 支付签名随机串，不长于 32 位
	    package: params.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
	    signType: params.signType, // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
	    paySign: params.paySign, // 支付签名
	    success: function(res){callback(res)}
	});
}