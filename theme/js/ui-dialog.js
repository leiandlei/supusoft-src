
/*
 * 函数名：message_dialog( message, style )
 * 功  能：弹出消息窗
 * 参  数：message	：提示的消息
 *		   style	：样式图标
 */
function message_dialog( message, style ){
	if( undefined == style ) style = 'success';
	$('<div style="margin-bottom:10px;display:none;" class="clearfix"><span id="msg-ico"></span><h6></h6></div>').dialog({
		title	: '提示信息',
		width	: 240,
		height	: 160,
		autoOpen: true,
		resizable: false,
		autoResize: true,
		modal	: true,
		overlay	: {opacity: 0.5,background: "black"},
		close: function(ev,ui){
			$(this).remove();
			$('#batch-approval-btn').attr( 'disabled', true );
		},
		buttons	:{
			'确定'	: function(){
				if( $(this).find('.note-msg').hasClass( 'msg-ico-success' ) ) window.location.reload();
				$(this).dialog('close');
			}
		},
		open	: function(){
			var btn = $('.ui-dialog-buttonpane').find('button:contains("确定")');
			btn.removeClass('ui-state-default').addClass('ui-state-default-highlight');
			$(this).find('#msg-ico').addClass('notice-ico-'+style);
			$(this).find('h6').text( message );
			$(this).find('.ui-widget-content').addClass('notice-'+style);
		}
	});
}



/*
 * 函数名：confirm_dialog( message, action, style )
 * 功  能：弹出询问窗
 * 参  数：message	：提示的消息
 *		   action	：点击确定执行的操作 可以是函数或URL地址 是函数将执行，URL地址则转向
 *		   style	：样式图标
 */
function confirm_dialog( message, action, style ){
	if( undefined == style ) style = 'notice';
	$('<div style="display:none;"><p class="tal note-msg" style="padding-left:60px;line-height:1.6em;height:48px;"></p></div>').dialog({
		title	: '提示信息',
		width	: 360,
		height	: 180,
		autoOpen: true,
		resizable: false,
		autoResize: true,
		modal	: true,
		overlay	: {opacity: 0.5,background: "black"},
		close: function(ev,ui){
			$(this).remove();
		},
		buttons	:{
			'取消'	: function(){
				$(this).dialog('close');
			},
			'确定'	: function(){
				if( typeof action  == 'string' ){
					window.location.href = action;
				} else if( typeof action == 'function' ){
					action();
				}
				$(this).dialog('close');
			}
		},
		open	: function(){
			var btn = $('.ui-dialog-buttonpane').find('button:contains("确定")');
			btn.removeClass('ui-state-default').addClass('ui-state-default-highlight');
			var btn2 = $('.ui-dialog-buttonpane').find('button:contains("取消")');
			btn2.removeClass('ui-state-default').addClass('ui-state-default');

			$(this).find('.note-msg').addClass( 'msg-ico-'+style ).html( message );
		}
	});
}



/*
 * 函数名：iframe_dialog( title, url, width, height )
 * 功  能：子框架窗口
 * 参  数：title	：窗口标题
 *		   url		：指向的页面地址
 *		   i_width	：窗口宽度
 *		   i_height	：窗口高度
 */
function iframe_dialog( i_title, from_url, i_width, i_height ){
	$('<iframe border="0" id="iframe-dialog" frameborder="no" src="' + from_url + '" />').dialog({
		title: i_title,
		autoOpen: true,
		width: i_width,
		height: i_height,
		modal: true,
		resizable: false,
		autoResize: true,
		overlay: {opacity: 0.5,background: "black"},
		close: function(ev,ui){$(this).remove();}
		}).width(i_width).height(i_height);

}

/*
 * 函数名：close_iframe_dialog()
 * 功  能：关闭 子框架 窗口
 */
function close_iframe_dialog(){
	$('#iframe-dialog').dialog('close');
}