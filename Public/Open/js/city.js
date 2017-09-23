/**
 * city
 * @authors baojunbo <baojunbo@gmail.com>
 * @date    2014-03-25 10:54:37
 */

(function($) {
	$.City = function(cityId, container, address) {
		cityId = cityId;
		this.cityId = cityId;
		this.container = container;
		this.provinces = provinces;
		this.init();
	};

	var changeCity = function(cid, container, cityId, event) {
		var cityList = citys[cid];
		var containerId = container.attr("id");
		var containerObj= document.getElementById(containerId);
		var childs = containerObj.getElementsByTagName("select").length;
		$("#" + containerId + " input").val(cityId);
		if (typeof event != "undefined") {
			var selectId = event.target.id;
			var index = selectId.substring(selectId.indexOf("_") + 1);
			var next = parseInt(index) + 1;
			for (var i = next; i <= childs; i ++) {
				// $("#level_" + i).remove();
				var obj = document.getElementById("level_" + i);
				if (obj) {
					containerObj.removeChild(obj);
					// console.log(obj);
				}
			}
			$("#" + containerId + " input").val(cid);
		}

		if (typeof cityList != "undefined") {
			var cChilds = containerObj.getElementsByTagName("select").length;
			var cityNum = cityList.length;
			var selected = false;
			var select = document.createElement("select");
			var nextChild = cChilds + 1;
			var nextId = "level_" + nextChild;
			var nextCid = cityId.substring(0, nextChild * 2);
			select.id = nextId;
			select.options.add(new Option("--", ""));
			for (var i = 0; i < cityNum; i ++) {
				if (nextCid == cityList[i]["id"] && typeof event == "undefined") {
					selected = true;
				} else {
					selected = false;
				}
				select.options.add(new Option(cityList[i]["name"], cityList[i]["id"], selected, selected));
			}
			$(select).bind("change", function(event){
				changeCity($(this).val(), container, cityId, event);
			});
			$(select).addClass("form-control").css({"width":"auto", "float": "left", "margin-right": "5px"});
			container.append(select);
			if (cityId != "" && typeof event == "undefined") {
				// console.log(nextCid);
				var ciyid = $(select).val();
				changeCity(ciyid, container, cityId);
			}
		}
	}

	$.City.prototype = {
		init: function() {
			var selected = false;
			var select = document.createElement("select");
			var input = document.createElement("input");
			var provinceId = this.cityId.substring(0, 2);
			input.name = "cityId";
			input.type = "hidden";
			select.id = "level_1";
			select.options.add(new Option("--", ""));
			for(cid in this.provinces) {
				if (cid == provinceId) {
					selected = true;
				} else {
					selected = false;
				}
				select.options.add(new Option(this.provinces[cid]["name"], cid, selected, selected));
			}
			var container = this.container;
			var cityId = this.cityId;
			$(select).bind("change", function(event){
				changeCity($(this).val(), container, cityId, event);
			});
			$(select).addClass("form-control").css({"width":"auto", "float": "left", "margin-right": "5px"});
			container.append(select);
			container.append(input).append(select);
			if (provinceId.length < 2) {
				provinceId = $("#level_1 option:selected").val();
			}
			if (cityId != "") {
				changeCity(provinceId, container, cityId);
			}
		},

		selected: function() {
			// console.log("ddd");
		},
	};

	$.fn.city = function(cityId) {
		if (typeof cityId == "undefined") {
			var cityId = "";
		}
		var city = new $.City(cityId, $(this));
		city.selected();
	}

	$.cityText = function(cityId, domName) {
		var city = "", cid = "";
		var provinceId = cityId.substring(0, 2);
		var length = cityId.length;
		var level = length / 2;
		if (typeof provinces[provinceId] != "undefined") city = provinces[provinceId]["name"];

		for (var l = 1; l <= level; l ++) {
			var postion = l * 2;
			cid = cityId.substring(0, postion);
			nextCid =  cityId.substring(0, postion + 2);
			if (typeof citys[cid] != "undefined") {
				for (var i = 0; i < citys[cid].length; i ++) {
					if (nextCid == citys[cid][i]["id"]) {
						city += citys[cid][i]["name"];
						break;
					}
				}
			}
		}
		if (typeof domName != "undefined") {
			$("#" + domName).text(city);
		} else {
			document.write(city);
		}
	}
})(jQuery);
