!function(e){e.fn.PavMegamenuEditor=function(a){function t(a,t){e(".form-setting").hide(),e("#menu-form").show(),e.each(e("#menu-form form").serializeArray(),function(a,n){var o="";e(t).data(n.name.replace("menu_",""))&&(o=e(t).data(n.name.replace("menu_",""))),e("[name="+n.name+"]","#menu-form").val(o)}),f.data("align")&&(e(".button-alignments button").removeClass("active"),e('[data-option="'+f.data("align")+'"]').addClass("active"))}function n(a,t){var n=e(a).offset();e("#submenu-form").css("left",n.left-30),e("#submenu-form").css("top",n.top-e("#submenu-form").height()),e("#submenu-form").show(),e.each(e("#submenu-form form").serializeArray(),function(a,n){e("[name="+n.name+"]","#submenu-form").val(e(t).data(n.name.replace("submenu_","")))})}function o(){e("input, select","#menu-form").change(function(){if(f)if(e(this).hasClass("menu_submenu")){var a=e("a",f);if(e(this).val()&&!e(a).hasClass("dropdown-toggle")){e(a).addClass("dropdown-toggle"),e(a).attr("data-toggle","pavo-dropdown");var t='<div class="dropdown-menu"><div class="dropdown-menu-inner"><div class="row active"></div></div></div>';e(f).addClass("parent").addClass("dropdown"),e(f).append(t),e(f).removeClass("disable-menu")}else 1==e(this).val()?e(f).removeClass("disable-menu"):e(f).addClass("disable-menu");e(f).data("submenu",e(this).val())}else if(e(this).hasClass("menu_subwidth")){var n=parseInt(e(this).val());n>200&&(e(".dropdown-menu",f).width(n),e(f).data("subwidth",n))}})}function i(){e("select, input","#submenu-form").change(function(){f&&"submenu_group"==e(this).attr("name")&&(1==e(this).val()?(e(f).addClass("mega-group"),e(f).children(".dropdown-menu").addClass("dropdown-mega").removeClass("dropdown-menu")):(e(f).removeClass("mega-group"),e(f).children(".dropdown-mega").addClass("dropdown-menu").removeClass("dropdown-mega")),e(f).data("group",e(this).val()))})}function s(a){e(".form-setting").hide(),e("a",a).click(function(o){var i=this,s=e(this).parent();e(".row",a).removeClass("active"),e(".mega-col",a).removeClass("active");var r=e(this).offset();return e("#menu-form").css("left",r.left-30),e("#menu-form").css("top",r.top-e("#menu-form").height()),f=s,s.hasClass("dropdown-submenu")?(e(".dropdown-submenu",s.parent()).removeClass("open"),s.addClass("open"),n(i,s,a)):(s.parent().hasClass("megamenu")&&e("ul.navbar-nav > li").removeClass("open"),s.addClass("open"),t(i,s,a)),o.stopPropagation(),!1}),e("#menu-form .add-row").click(function(){var a=e('<div class="row"></div>'),t=e(f).children(".dropdown-menu").children(".dropdown-menu-inner");t.append(a),t.children(".row").removeClass("active"),a.addClass("active")}),e("#menu-form .remove-row").click(function(){if(f){var a=!1;if(e(".row.active",f).children(".mega-col").each(function(){"menu"==e(this).data("type")&&(a=!0)}),0!=a)return alert("You can remove Row having Menu Item(s) Inside Columns"),!0;e(".row.active",f).remove(),r()}}),e(a).delegate(".row","click",function(t){e(".row",a).removeClass("active"),e(this).addClass("active"),t.stopPropagation()}),e("#menu-form .add-col").click(function(){if(f){var a=6,t=e('<div class="col-sm-'+a+' mega-col active"><div></div></div>');e(".mega-col",f).removeClass("active"),e(".row.active",f).append(t),t.data("colwidth",a);var n=e(".dropdown-menu .mega-col",f).length;e(f).data("cols",n)}}),e(".remove-col").click(function(){if(f){if("menu"==e(".mega-col.active",f).data("type"))return alert("You could not remove this column having menu item(s)"),!0;e(".mega-col.active",f).remove()}d()}),e(a).delegate(".mega-col","click",function(t){e(".mega-col",a).removeClass("active"),e(this).addClass("active");var n=e(this).offset();e("#column-form").css({top:n.top-e("#column-form").height(),left:n.left}).show(),"menu"!=e(this).data("type")?e("#widget-form").css({top:n.top+e(this).height(),left:n.left}).show():e("#widget-form").hide(),e(".row",a).removeClass("active"),e(this).parent().addClass("active"),e("#submenu-form").hide(),e.each(e(this).data(),function(a,t){e("[name="+a+"]","#column-form").val(t)}),t.stopPropagation()}),e("input, select","#column-form").change(function(){if(f){var a=e(".mega-col.active",f);if(e(this).hasClass("colwidth")){var t=e(a).attr("class").replace(/col-sm-\d+/,"");e(a).attr("class",t+" col-sm-"+e(this).val())}e(a).data(e(this).attr("name"),e(this).val())}}),e(".form-setting").each(function(){var a=e(this);e(".popover-title span",this).click(function(){"menu-form"==a.attr("id")?c():"column-form"==a.attr("id")?d():(e("#submenu-form").hide(),e("#widget-form").hide())})}),e(".form-setting").draggable(),e("#btn-inject-widget").click(function(){var a=e("select",e(this).parent()).val();if(a>0){var t=e(".mega-col.active",f),n=e(t).data("widgets");e(t).data("widgets")?-1==e(t).data("widgets").indexOf("wid-"+a)&&e(t).data("widgets",n+"|wid-"+a):e(t).data("widgets","wid-"+a),e(t).children("div").html('<div class="loading">Loading....</div>'),e.ajax({url:v.action_widget+"widgets/"+e(t).data("widgets"),data:"",type:"GET"}).done(function(a){e(t).children("div").html(a)})}else alert("Please select a widget to inject")}),e("#unset-data-menu").click(function(){return confirm("Are you sure to reset megamenu configuration")&&e.ajax({url:v.action+"reset/1",data:"",type:"GET"}).done(function(){location.reload()}),!1}),e(a).delegate(".ves-widget","hover",function(){var a=e(this),t=e(this).parent().parent();if(e(this).find(".w-setting").length<=0){var n=e('<span class="w-setting"></span>');e(a).append(n),n.click(function(){var n=t.data("widgets")+"|",n=n.replace(e(a).attr("id")+"|","").replace(/\|$/,"");t.data("widgets",n),e(a).remove()})}}),e(".button-alignments button").click(function(){if(f){e(".button-alignments button").removeClass("active"),e(this).addClass("active"),e(f).data("align",e(this).data("option"));var a=e(f).attr("class").replace(/aligned-\w+/,"");e(f).attr("class",a),e(f).addClass(e(this).data("option"))}})}function r(){e("#column-form").hide(),e("#mainmenutop .row.active").removeClass("active")}function d(){e("#column-form").hide(),e("#widget-form").hide(),e("#mainmenutop .mega-col.active").removeClass("active")}function c(){e(".form-setting").hide(),e("#mainmenutop .open").removeClass("open"),e("#mainmenutop .row.active").removeClass("active"),e("#mainmenutop .mega-col.active").removeClass("active"),f&&(f=null)}function l(){var a=new Array;e("#megamenu-content #mainmenutop li.parent").each(function(){var t=e(this).data();t.rows=new Array,e(this).children(".dropdown-menu").children("div").children(".row").each(function(){var a=new Object;a.cols=new Array,e(this).children(".mega-col").each(function(){a.cols.push(e(this).data())}),t.rows.push(a)}),a.push(t)});var t=JSON.stringify(a),n="params="+t;n+="&form_key="+window.FORM_KEY,e.ajax({url:v.action_menu,data:n,type:"POST"}).done(function(){location.reload()})}function m(){e("#pavo-progress").hide();var a=new Array;e("#megamenu-content #mainmenutop .mega-col").each(function(){var t=e(this);e(t).data("widgets")&&"menu"!=e(t).data("type")&&a.push(t)});var t=0;a.length>0&&(e("#pavo-progress").show(),e("#megamenu-content").hide()),e.each(a,function(n,o){e.ajax({url:v.action_widget,data:"widgets="+e(o).data("widgets")+"&form_key="+window.FORM_KEY,type:"POST"}).done(function(n){o.children("div").html(n),t++,e("#pavo-progress .progress-bar").css("width",100*t/a.length+"%"),a.length==t&&(e("#megamenu-content").delay(1e3).fadeIn(),e("#pavo-progress").delay(1e3).fadeOut()),e("a",o).attr("href","#megamenu-content")})})}function u(){var a=e("#megamenu-content #mainmenutop");e("a",a).attr("href","#"),e('[data-toggle="dropdown"]',a).attr("data-toggle","pavo-dropdown"),s(a),i(),o(),m()}var v=e.extend({},{lang:null,opt1:null,action:null,action_menu:null,text_warning_select:"Please select One to remove?",text_confirm_remove:"Are you sure to remove footer row?",JSON:null},a),f=null;return this.each(function(){e("#form-setting").hide(),e.ajax({url:v.action}).done(function(a){e("#megamenu-content").html(a),u(),e("#save-data-menu").click(function(){l()})})}),e(".form-setting input").keypress(function(e){return 13==e.which?(e.preventDefault(),!1):void 0}),this}}(jQuery);