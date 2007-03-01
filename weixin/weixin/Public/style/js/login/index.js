$(function(){
    $('#button_login').click(function(event) {
    	$.ajax({
          type: "POST",
          async:false,
          url: "../Ajax/login",
          data:$('#login').serializeArray(),
          dataType: "json",
          success: function(e){
                    if( e.errorCode == 0 ){
                    	window.location.href="../Index/index";
                    }else{
                      alert( e.errorStr );
                    }
                  }
        });
    });
});