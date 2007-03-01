//年月(单选)
function show(inputname)
{
	$(inputname).click(function(){
		var input=$(this);
		$(input).parent("div").before("<div class=\"menu_bg_layer\"></div>");
		$(".menu_bg_layer").height($(document).height());
		$(".menu_bg_layer").css({ width: $(document).width(), position: "absolute", left: "0", top: "0" , "z-index": "0"});
		$(input).parent("div").css("position","relative");
		
		var myDate = new Date();
		var y=myDate.getFullYear()+3;
		var ymin=y-11;
		htm="<div class=\"showyearbox yearlist\">";		
		htm+="<div class=\"tit\">选择年份>></div>";
		htm+="<ul>";
		for (i=y;i>=ymin;i--)
		{
			htm+="<li title=\""+i+"\">"+i+"年</li>";
		}
		htm+="<div class=\"clear\"></div>";
		htm+="</ul>";
		htm+="</div>";
		//
		htm+="<div class=\"showyearbox monthlist\">";
		htm+="<div class=\"tit\">选择月份>></div>";
		htm+="<ul>";
		for (i=1;i<=12;i++)
		{
			if( i<10 ){
				htm+="<li title=\"-0"+i+"\">"+i+"月</li>";
			}else{
				htm+="<li title=\"-"+i+"\">"+i+"月</li>";
			}
		}
		htm+="<div class=\"clear\"></div>";
		htm+="</ul>";
		htm+="</div>";
		$(input).blur();
		if ($(input).parent("div").find(".showyearbox").html())
		{
			$(input).parent("div").children(".showyearbox.yearlist").slideToggle("fast");
		}
		else
		{
			$(input).parent("div").append(htm);
			$(input).parent("div").children(".showyearbox.yearlist").slideToggle("fast");
		}
		//
		$(input).parent("div").children(".yearlist").find("li").unbind("click").click(function()
		{
			$(input).val($(this).attr("title"));
			$(input).parent("div").children(".yearlist").hide();
			$(input).parent("div").children(".monthlist").show();
			$(input).parent("div").children(".monthlist").find("li").unbind("click").click(function()
			{
				$(input).val($(input).val()+$(this).attr("title"));
				$(".menu_bg_layer").hide();
				$(input).parent("div").css("position","");
				$(input).parent("div").find(".showyearbox").hide();
			});	
		});	
		//
		$(".showyearbox>ul>li").hover(
		function()
		{
		$(this).css("background-color","#DAECF5");
		$(this).css("color","#ff0000");
		},
		function()
		{
		$(this).css("background-color","");
		$(this).css("color","");
		}
		);
		//
		$(".menu_bg_layer").click(function(){
					$(".menu_bg_layer").hide();
					$(input).parent("div").css("position","");
					$(input).parent("div").find(".showyearbox").hide();			
		});
	});
}