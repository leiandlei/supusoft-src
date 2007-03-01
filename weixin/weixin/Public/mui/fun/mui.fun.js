var muifun = {};

//根据数组获取mui轮播图html
muifun.loadLoopHtmlByArray = function(loop_arr,loop_arr_default)
{
	loop_arr = loop_arr?loop_arr:(loop_arr_default?loop_arr_default:([]));
	if(!loop_arr)return '';
	var listHtml ='',listDiv='';
		listHtml = listHtml+'<div class="mui-slider-item mui-slider-item-duplicate">';
		listHtml = listHtml+	'<a href="#" style="width:100%;height:100%;">';
		listHtml = listHtml+		'<img src="'+(loop_arr.length>1?loop_arr[loop_arr.length-1]:loop_arr[0])+'" style="width:100%;height:100%;">';
		listHtml = listHtml+	'</a>';
		listHtml = listHtml+'</div>';

		for( i in loop_arr )
		{
			listHtml = listHtml+'<div class="mui-slider-item">';
			listHtml = listHtml+	'<a href="#" style="width:100%;height:100%;">';
			listHtml = listHtml+		'<img src="'+loop_arr[i] +'" style="width:100%;height:100%;">';
			listHtml = listHtml+	'</a>';
			listHtml = listHtml+'</div>';
			listDiv  = (listDiv)?(listDiv +'<div class="mui-indicator"></div>'):('<div class="mui-indicator mui-active"></div>');
		}
		listHtml = listHtml+'<div class="mui-slider-item mmui-slider-item-duplicate">';
		listHtml = listHtml+	'<a href="#" style="width:100%;height:100%;">';
		listHtml = listHtml+		'<img src="'+loop_arr[0]+'" style="width:100%;height:100%;">';
		listHtml = listHtml+	'</a>';
		listHtml = listHtml+'</div>';
		return {listHtml:listHtml,listDiv:listDiv};
}
