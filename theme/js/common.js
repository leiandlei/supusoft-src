function ajax(data,event,type){
	var url     = './app/admin/control/ajax/configAjax.php?event='+event;
	var type    = type||'POST';
	var results = '';
	$.ajax({
            type: type,
            url: url,
            async:false,
            data: {
                data_params:data
            },
            dataType: "json",
            success:function(e){
                    results = e;
                }
    });
	return results;
}