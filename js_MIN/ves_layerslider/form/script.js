document.observe("dom:loaded",function(){function e(e){n.each(function(n){var t="venustheme_brand_"+n+"_source_setting",u="venustheme_brand_"+n+"_source_setting-head";n==e?($(u).up("div.entry-edit-head").show(),$(t).show(),$(t+"-state").value=1):($(t+"-state").value=0,$(u).up("div.entry-edit-head").hide(),$(t).hide())})}var n=["image","file"];Event.observe($("venustheme_brand_venustheme_brand_source"),"change",function(){e(this.value)}),$$("#venustheme_brand_venustheme_brand_source option").each(function(e){n.push(e.value)}),$$("#venustheme_brand_venustheme_brand_source option").each(function(n){n.selected&&e(n.value)})});