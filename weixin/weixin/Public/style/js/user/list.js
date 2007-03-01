$(function(){

	//修改
    // $('.eid').click(function(event) {
	   //  var id = $(this).parent().parent().data('id');
	   //  $.ajax({
	   //      type: "POST",
	   //     	async:false,
	   //      url: "./config/configAjax.php?event=userEid",
	   //    	data: {
	   //      	data_params:{id:id}
	   //    	},
	   //      dataType: "json",
	   //      success: function(e){
	   //                  if( e.errorCode == 0 ){
	   //                  	window.location.href="./index.php?action=user:list"; 
	   //                  }else{
	   //                    	alert('请刷新后重试');
	   //                  }
	   //              }
	   //  });
    // });

    //删除
    $('.del').click(function(event) {
	    var userId = $(this).parent().parent().data('id');
	    $.ajax({
	        type: "POST",
	       	async:false,
	        url: "./config/configAjax.php?event=userDel",
	      	data: {
	        	id:userId
	      	},
	        dataType: "json",
	        success: function(e){
	                    if( e.errorCode == 0 ){
	                    	window.location.href="./index.php?action=user:list"; 
	                    }else{
	                      	alert('请刷新后重试');
	                    }
	                }
	    });
    });

    //显示模态框
    $(".motai").click(function() {
		$("#Modal").modal();
	});

    //模态框修改
	$("#motai_sub").click(function(){
		$.ajax({
             type: "POST",
             url: "/config/configAjax.php?event=",
             data: {
             		data:$("#Modal_from").serializeArray(),
             	},
             dataType: "json",
             success: function(data){
                         if(data.status==0){
                         	window.location.href="userChart.php"; 
                         }else{
                         	alert(data.msg);
                         }
                      }
        });
	});
});