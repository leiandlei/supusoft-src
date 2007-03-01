var DistrictNum = 0;
var Region = {
	regions	: null,
	province	: null,
	city		: null,
	dist	: null,
	init	: function(url){
		var self = this;
		self.province = jQuery('#province-select');
		self.city = jQuery('#city-select');
		self.dist = jQuery('#district-select');
		//self.city.parent().hide();self.dist.parent().hide();
		jQuery.ajaxSetup({async:false});
		jQuery.getJSON(url,function(data){
			self.regions = data;
			for(var i in self.regions.province){
				self.province.append('<option value="'+self.regions.province[i].code+'">'+self.regions.province[i].name+'</option>');
			}
			self.province.change(function(){
				self.loadCity();
			});
			self.city.change(function(){
				self.loadDistrict();
			});
		});
		return self;
	},
	eInit	: function(val){
		var self = this;
		if(!val) return;
		var _prov = val.substring(0,2);
		var _city = val.substring(0,4);
		var _dist = val;
		if(!_prov || _prov == '00'){
			return;
		}
		self.province.val(_prov+'0000');
		self.loadCity();
		if(_city == _prov+'00'){
			return;
		}
		self.city.val(_city+'00');
		self.loadDistrict();
		if(_dist == _prov+_city+'00'){
			return;
		}
		self.dist.val(_dist);
	},
	loadCity	: function(){
		var self = this;
		self.city.parent().hide();
		self.city.empty();
		self.city.append('<option value="">请选择市</option>');
		for(var i in self.regions.city){
			if(self.regions.city[i].code.substring(0,2) == self.province.val().substring(0,2)){
				if( self.regions.city[i].code.substring(2,4) == '01' && self.regions.city[i].name == '' ){
					self.regions.city[i].name = '市辖区';
				} else if( self.regions.city[i].code.substring(2,4) == '02' && self.regions.city[i].name == '' ){
					self.regions.city[i].name = '--县';
				}
				self.city.append('<option value="'+self.regions.city[i].code+'">'+self.regions.city[i].name+'</option>');
			}
		}
		self.city.parent().show();
		//self.loadDistrict();
		//self.dist.parent().hide();
	},
	loadDistrict: function(){
		var self = this;
		self.dist.parent().hide();
		self.dist.empty();
		self.dist.append('<option value="">请选择区/县</option>');
		DistrictNum = 0;
		for(var i in self.regions.district){
			if(self.regions.district[i].code.substring(0,4) == self.city.val().substring(0,4) ){
				DistrictNum++;
				self.dist.append('<option value="'+self.regions.district[i].code+'">'+self.regions.district[i].name+'</option>');
			}
		}
		self.dist.parent().show();
	},
	SetDistrict	: function(){}
}