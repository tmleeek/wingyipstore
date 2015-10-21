!function($){function Timepicker(){this.regional=[],this.regional[""]={currentText:"Now",closeText:"Done",ampm:!1,timeFormat:"hh:mm tt",timeOnlyTitle:"Choose Time",timeText:"Time",hourText:"Hour",minuteText:"Minute",secondText:"Second",timezoneText:"Time Zone"},this._defaults={showButtonPanel:!0,timeOnly:!1,showHour:!0,showMinute:!0,showSecond:!1,showTimezone:!1,showTime:!0,stepHour:.05,stepMinute:.05,stepSecond:.05,hour:0,minute:0,second:0,timezone:"+0000",hourMin:0,minuteMin:0,secondMin:0,hourMax:23,minuteMax:59,secondMax:59,minDateTime:null,maxDateTime:null,hourGrid:0,minuteGrid:0,secondGrid:0,alwaysSetTime:!0,separator:" ",altFieldTimeOnly:!0,showTimepicker:!0,timezoneList:["-1100","-1000","-0900","-0800","-0700","-0600","-0500","-0400","-0300","-0200","-0100","+0000","+0100","+0200","+0300","+0400","+0500","+0600","+0700","+0800","+0900","+1000","+1100","+1200"]},$.extend(this._defaults,this.regional[""])}function extendRemove(e,t){$.extend(e,t);for(var i in t)(null===t[i]||void 0===t[i])&&(e[i]=t[i]);return e}$.extend($.ui,{timepicker:{version:"0.9.5"}}),$.extend(Timepicker.prototype,{$input:null,$altInput:null,$timeObj:null,inst:null,hour_slider:null,minute_slider:null,second_slider:null,timezone_select:null,hour:0,minute:0,second:0,timezone:"+0000",hourMinOriginal:null,minuteMinOriginal:null,secondMinOriginal:null,hourMaxOriginal:null,minuteMaxOriginal:null,secondMaxOriginal:null,ampm:"",formattedDate:"",formattedTime:"",formattedDateTime:"",timezoneList:["-1100","-1000","-0900","-0800","-0700","-0600","-0500","-0400","-0300","-0200","-0100","+0000","+0100","+0200","+0300","+0400","+0500","+0600","+0700","+0800","+0900","+1000","+1100","+1200"],setDefaults:function(e){return extendRemove(this._defaults,e||{}),this},_newInst:function($input,o){var tp_inst=new Timepicker,inlineSettings={};for(var attrName in this._defaults){var attrValue=$input.attr("time:"+attrName);if(attrValue)try{inlineSettings[attrName]=eval(attrValue)}catch(err){inlineSettings[attrName]=attrValue}}return tp_inst._defaults=$.extend({},this._defaults,inlineSettings,o,{beforeShow:function(e,t){$.isFunction(o.beforeShow)&&o.beforeShow(e,t,tp_inst)},onChangeMonthYear:function(e,t,i){tp_inst._updateDateTime(i),$.isFunction(o.onChangeMonthYear)&&o.onChangeMonthYear.call($input[0],e,t,i,tp_inst)},onClose:function(e,t){tp_inst.timeDefined===!0&&""!=$input.val()&&tp_inst._updateDateTime(t),$.isFunction(o.onClose)&&o.onClose.call($input[0],e,t,tp_inst)},timepicker:tp_inst}),tp_inst.hour=tp_inst._defaults.hour,tp_inst.minute=tp_inst._defaults.minute,tp_inst.second=tp_inst._defaults.second,tp_inst.ampm="",tp_inst.$input=$input,o.altField&&(tp_inst.$altInput=$(o.altField).css({cursor:"pointer"}).focus(function(){$input.trigger("focus")})),void 0!==tp_inst._defaults.minDate&&tp_inst._defaults.minDate instanceof Date&&(tp_inst._defaults.minDateTime=new Date(tp_inst._defaults.minDate.getTime())),void 0!==tp_inst._defaults.minDateTime&&tp_inst._defaults.minDateTime instanceof Date&&(tp_inst._defaults.minDate=new Date(tp_inst._defaults.minDateTime.getTime())),void 0!==tp_inst._defaults.maxDate&&tp_inst._defaults.maxDate instanceof Date&&(tp_inst._defaults.maxDateTime=new Date(tp_inst._defaults.maxDate.getTime())),void 0!==tp_inst._defaults.maxDateTime&&tp_inst._defaults.maxDateTime instanceof Date&&(tp_inst._defaults.maxDate=new Date(tp_inst._defaults.maxDateTime.getTime())),tp_inst},_addTimePicker:function(e){var t=this.$altInput&&this._defaults.altFieldTimeOnly?this.$input.val()+" "+this.$altInput.val():this.$input.val();this.timeDefined=this._parseTime(t),this._limitMinMaxDateTime(e,!1),this._injectTimePicker()},_parseTime:function(e,t){var i,n=this._defaults.timeFormat.toString().replace(/h{1,2}/gi,"(\\d?\\d)").replace(/m{1,2}/gi,"(\\d?\\d)").replace(/s{1,2}/gi,"(\\d?\\d)").replace(/t{1,2}/gi,"(am|pm|a|p)?").replace(/z{1}/gi,"((\\+|-)\\d\\d\\d\\d)?").replace(/\s/g,"\\s?")+"$",s=this._getFormatPositions();if(this.inst||(this.inst=$.datepicker._getInst(this.$input[0])),t||!this._defaults.timeOnly){var a=$.datepicker._get(this.inst,"dateFormat"),r=new RegExp("[.*+?|()\\[\\]{}\\\\]","g");n=".{"+a.length+",}"+this._defaults.separator.replace(r,"\\$&")+n}return i=e.match(new RegExp(n,"i")),i?(-1!==s.t&&(this.ampm=(void 0===i[s.t]||0===i[s.t].length?"":"A"==i[s.t].charAt(0).toUpperCase()?"AM":"PM").toUpperCase()),-1!==s.h&&(this.hour="AM"==this.ampm&&"12"==i[s.h]?0:"PM"==this.ampm&&"12"!=i[s.h]?(parseFloat(i[s.h])+12).toFixed(0):Number(i[s.h])),-1!==s.m&&(this.minute=Number(i[s.m])),-1!==s.s&&(this.second=Number(i[s.s])),-1!==s.z&&(this.timezone=i[s.z]),!0):!1},_getFormatPositions:function(){var e=this._defaults.timeFormat.toLowerCase().match(/(h{1,2}|m{1,2}|s{1,2}|t{1,2}|z)/g),t={h:-1,m:-1,s:-1,t:-1,z:-1};if(e)for(var i=0;i<e.length;i++)-1==t[e[i].toString().charAt(0)]&&(t[e[i].toString().charAt(0)]=i+1);return t},_injectTimePicker:function(){var e=this.inst.dpDiv,t=this._defaults,i=this,n=(t.hourMax-t.hourMax%t.stepHour).toFixed(0),s=(t.minuteMax-t.minuteMax%t.stepMinute).toFixed(0),a=(t.secondMax-t.secondMax%t.stepSecond).toFixed(0),r=this.inst.id.toString().replace(/([^A-Za-z0-9_])/g,"");if(0===e.find("div#ui-timepicker-div-"+r).length&&t.showTimepicker){var d,o=' style="display:none;"',l='<div class="ui-timepicker-div" id="ui-timepicker-div-'+r+'"><dl><dt class="ui_tpicker_time_label" id="ui_tpicker_time_label_'+r+'"'+(t.showTime?"":o)+">"+t.timeText+'</dt><dd class="ui_tpicker_time" id="ui_tpicker_time_'+r+'"'+(t.showTime?"":o)+'></dd><dt class="ui_tpicker_hour_label" id="ui_tpicker_hour_label_'+r+'"'+(t.showHour?"":o)+">"+t.hourText+"</dt>",u=0,h=0,c=0;if(t.showHour&&t.hourGrid>0){l+='<dd class="ui_tpicker_hour"><div id="ui_tpicker_hour_'+r+'"'+(t.showHour?"":o)+'></div><div style="padding-left: 1px"><table><tr>';for(var m=t.hourMin;n>m;m+=t.hourGrid){u++;var p=t.ampm&&m>12?m-12:m;10>p&&(p="0"+p),t.ampm&&(0==m?p="12a":p+=12>m?"a":"p"),l+="<td>"+p+"</td>"}l+="</tr></table></div></dd>"}else l+='<dd class="ui_tpicker_hour" id="ui_tpicker_hour_'+r+'"'+(t.showHour?"":o)+"></dd>";if(l+='<dt class="ui_tpicker_minute_label" id="ui_tpicker_minute_label_'+r+'"'+(t.showMinute?"":o)+">"+t.minuteText+"</dt>",t.showMinute&&t.minuteGrid>0){l+='<dd class="ui_tpicker_minute ui_tpicker_minute_'+t.minuteGrid+'"><div id="ui_tpicker_minute_'+r+'"'+(t.showMinute?"":o)+'></div><div style="padding-left: 1px"><table><tr>';for(var _=t.minuteMin;s>_;_+=t.minuteGrid)h++,l+="<td>"+(10>_?"0":"")+_+"</td>";l+="</tr></table></div></dd>"}else l+='<dd class="ui_tpicker_minute" id="ui_tpicker_minute_'+r+'"'+(t.showMinute?"":o)+"></dd>";if(l+='<dt class="ui_tpicker_second_label" id="ui_tpicker_second_label_'+r+'"'+(t.showSecond?"":o)+">"+t.secondText+"</dt>",t.showSecond&&t.secondGrid>0){l+='<dd class="ui_tpicker_second ui_tpicker_second_'+t.secondGrid+'"><div id="ui_tpicker_second_'+r+'"'+(t.showSecond?"":o)+'></div><div style="padding-left: 1px"><table><tr>';for(var f=t.secondMin;a>f;f+=t.secondGrid)c++,l+="<td>"+(10>f?"0":"")+f+"</td>";l+="</tr></table></div></dd>"}else l+='<dd class="ui_tpicker_second" id="ui_tpicker_second_'+r+'"'+(t.showSecond?"":o)+"></dd>";l+='<dt class="ui_tpicker_timezone_label" id="ui_tpicker_timezone_label_'+r+'"'+(t.showTimezone?"":o)+">"+t.timezoneText+"</dt>",l+='<dd class="ui_tpicker_timezone" id="ui_tpicker_timezone_'+r+'"'+(t.showTimezone?"":o)+"></dd>",l+="</dl></div>",$tp=$(l),t.timeOnly===!0&&($tp.prepend('<div class="ui-widget-header ui-helper-clearfix ui-corner-all"><div class="ui-datepicker-title">'+t.timeOnlyTitle+"</div></div>"),e.find(".ui-datepicker-header, .ui-datepicker-calendar").hide()),this.hour_slider=$tp.find("#ui_tpicker_hour_"+r).slider({orientation:"horizontal",value:this.hour,min:t.hourMin,max:n,step:t.stepHour,slide:function(e,t){i.hour_slider.slider("option","value",t.value),i._onTimeChange()}}),this.minute_slider=$tp.find("#ui_tpicker_minute_"+r).slider({orientation:"horizontal",value:this.minute,min:t.minuteMin,max:s,step:t.stepMinute,slide:function(e,t){i.minute_slider.slider("option","value",t.value),i._onTimeChange()}}),this.second_slider=$tp.find("#ui_tpicker_second_"+r).slider({orientation:"horizontal",value:this.second,min:t.secondMin,max:a,step:t.stepSecond,slide:function(e,t){i.second_slider.slider("option","value",t.value),i._onTimeChange()}}),this.timezone_select=$tp.find("#ui_tpicker_timezone_"+r).append("<select></select>").find("select"),$.fn.append.apply(this.timezone_select,$.map(t.timezoneList,function(e){return $("<option />").val("object"==typeof e?e.value:e).text("object"==typeof e?e.label:e)})),this.timezone_select.val("undefined"!=typeof this.timezone&&null!=this.timezone&&""!=this.timezone?this.timezone:t.timezone),this.timezone_select.change(function(){i._onTimeChange()}),t.showHour&&t.hourGrid>0&&(d=100*u*t.hourGrid/(n-t.hourMin),$tp.find(".ui_tpicker_hour table").css({width:d+"%",marginLeft:d/(-2*u)+"%",borderCollapse:"collapse"}).find("td").each(function(){$(this).click(function(){var e=$(this).html();if(t.ampm){var n=e.substring(2).toLowerCase(),s=parseInt(e.substring(0,2));e="a"==n?12==s?0:s:12==s?12:s+12}i.hour_slider.slider("option","value",e),i._onTimeChange(),i._onSelectHandler()}).css({cursor:"pointer",width:100/u+"%",textAlign:"center",overflow:"hidden"})})),t.showMinute&&t.minuteGrid>0&&(d=100*h*t.minuteGrid/(s-t.minuteMin),$tp.find(".ui_tpicker_minute table").css({width:d+"%",marginLeft:d/(-2*h)+"%",borderCollapse:"collapse"}).find("td").each(function(){$(this).click(function(){i.minute_slider.slider("option","value",$(this).html()),i._onTimeChange(),i._onSelectHandler()}).css({cursor:"pointer",width:100/h+"%",textAlign:"center",overflow:"hidden"})})),t.showSecond&&t.secondGrid>0&&$tp.find(".ui_tpicker_second table").css({width:d+"%",marginLeft:d/(-2*c)+"%",borderCollapse:"collapse"}).find("td").each(function(){$(this).click(function(){i.second_slider.slider("option","value",$(this).html()),i._onTimeChange(),i._onSelectHandler()}).css({cursor:"pointer",width:100/c+"%",textAlign:"center",overflow:"hidden"})});var g=e.find(".ui-datepicker-buttonpane");if(g.length?g.before($tp):e.append($tp),this.$timeObj=$tp.find("#ui_tpicker_time_"+r),null!==this.inst){var k=this.timeDefined;this._onTimeChange(),this.timeDefined=k}var M=function(){i._onSelectHandler()};this.hour_slider.bind("slidestop",M),this.minute_slider.bind("slidestop",M),this.second_slider.bind("slidestop",M)}},_limitMinMaxDateTime:function(e,t){var i=this._defaults,n=new Date(e.selectedYear,e.selectedMonth,e.selectedDay);if(this._defaults.showTimepicker){if(null!==this._defaults.minDateTime&&n){var s=this._defaults.minDateTime,a=new Date(s.getFullYear(),s.getMonth(),s.getDate(),0,0,0,0);(null===this.hourMinOriginal||null===this.minuteMinOriginal||null===this.secondMinOriginal)&&(this.hourMinOriginal=i.hourMin,this.minuteMinOriginal=i.minuteMin,this.secondMinOriginal=i.secondMin),e.settings.timeOnly||a.getTime()==n.getTime()?(this._defaults.hourMin=s.getHours(),this.hour<=this._defaults.hourMin?(this.hour=this._defaults.hourMin,this._defaults.minuteMin=s.getMinutes(),this.minute<=this._defaults.minuteMin?(this.minute=this._defaults.minuteMin,this._defaults.secondMin=s.getSeconds()):(this.second<this._defaults.secondMin&&(this.second=this._defaults.secondMin),this._defaults.secondMin=this.secondMinOriginal)):(this._defaults.minuteMin=this.minuteMinOriginal,this._defaults.secondMin=this.secondMinOriginal)):(this._defaults.hourMin=this.hourMinOriginal,this._defaults.minuteMin=this.minuteMinOriginal,this._defaults.secondMin=this.secondMinOriginal)}if(null!==this._defaults.maxDateTime&&n){var r=this._defaults.maxDateTime,d=new Date(r.getFullYear(),r.getMonth(),r.getDate(),0,0,0,0);(null===this.hourMaxOriginal||null===this.minuteMaxOriginal||null===this.secondMaxOriginal)&&(this.hourMaxOriginal=i.hourMax,this.minuteMaxOriginal=i.minuteMax,this.secondMaxOriginal=i.secondMax),e.settings.timeOnly||d.getTime()==n.getTime()?(this._defaults.hourMax=r.getHours(),this.hour>=this._defaults.hourMax?(this.hour=this._defaults.hourMax,this._defaults.minuteMax=r.getMinutes(),this.minute>=this._defaults.minuteMax?(this.minute=this._defaults.minuteMax,this._defaults.secondMin=r.getSeconds()):(this.second>this._defaults.secondMax&&(this.second=this._defaults.secondMax),this._defaults.secondMax=this.secondMaxOriginal)):(this._defaults.minuteMax=this.minuteMaxOriginal,this._defaults.secondMax=this.secondMaxOriginal)):(this._defaults.hourMax=this.hourMaxOriginal,this._defaults.minuteMax=this.minuteMaxOriginal,this._defaults.secondMax=this.secondMaxOriginal)}void 0!==t&&t===!0&&(this.hour_slider.slider("option",{min:this._defaults.hourMin,max:this._defaults.hourMax}).slider("value",this.hour),this.minute_slider.slider("option",{min:this._defaults.minuteMin,max:this._defaults.minuteMax}).slider("value",this.minute),this.second_slider.slider("option",{min:this._defaults.secondMin,max:this._defaults.secondMax}).slider("value",this.second))}},_onTimeChange:function(){var e=this.hour_slider?this.hour_slider.slider("value"):!1,t=this.minute_slider?this.minute_slider.slider("value"):!1,i=this.second_slider?this.second_slider.slider("value"):!1,n=this.timezone_select?this.timezone_select.val():!1;e!==!1&&(e=parseInt(e,10)),t!==!1&&(t=parseInt(t,10)),i!==!1&&(i=parseInt(i,10));var s=12>e?"AM":"PM",a=e!=this.hour||t!=this.minute||i!=this.second||this.ampm.length>0&&this.ampm!=s||n!=this.timezone;a&&(e!==!1&&(this.hour=e),t!==!1&&(this.minute=t),i!==!1&&(this.second=i),n!==!1&&(this.timezone=n),this._limitMinMaxDateTime(this.inst,!0)),this._defaults.ampm&&(this.ampm=s),this._formatTime(),this.$timeObj&&this.$timeObj.text(this.formattedTime),this.timeDefined=!0,a&&this._updateDateTime()},_onSelectHandler:function(){var e=this._defaults.onSelect,t=this.$input?this.$input[0]:null;e&&t&&e.apply(t,[this.formattedDateTime,this])},_formatTime:function(e,t,i){void 0==i&&(i=this._defaults.ampm),e=e||{hour:this.hour,minute:this.minute,second:this.second,ampm:this.ampm,timezone:this.timezone};var n=t||this._defaults.timeFormat.toString();if(i){var s="AM"==e.ampm?e.hour:e.hour%12;s=0===Number(s)?12:s,n=n.toString().replace(/hh/g,(10>s?"0":"")+s).replace(/h/g,s).replace(/mm/g,(e.minute<10?"0":"")+e.minute).replace(/m/g,e.minute).replace(/ss/g,(e.second<10?"0":"")+e.second).replace(/s/g,e.second).replace(/TT/g,e.ampm.toUpperCase()).replace(/Tt/g,e.ampm.toUpperCase()).replace(/tT/g,e.ampm.toLowerCase()).replace(/tt/g,e.ampm.toLowerCase()).replace(/T/g,e.ampm.charAt(0).toUpperCase()).replace(/t/g,e.ampm.charAt(0).toLowerCase()).replace(/z/g,e.timezone)}else n=n.toString().replace(/hh/g,(e.hour<10?"0":"")+e.hour).replace(/h/g,e.hour).replace(/mm/g,(e.minute<10?"0":"")+e.minute).replace(/m/g,e.minute).replace(/ss/g,(e.second<10?"0":"")+e.second).replace(/s/g,e.second).replace(/z/g,e.timezone),n=$.trim(n.replace(/t/gi,""));return arguments.length?n:void(this.formattedTime=n)},_updateDateTime:function(e){e=this.inst||e,dt=new Date(e.selectedYear,e.selectedMonth,e.selectedDay),dateFmt=$.datepicker._get(e,"dateFormat"),formatCfg=$.datepicker._getFormatConfig(e),timeAvailable=null!==dt&&this.timeDefined,this.formattedDate=$.datepicker.formatDate(dateFmt,null===dt?new Date:dt,formatCfg);var t=this.formattedDate;void 0!==e.lastVal&&e.lastVal.length>0&&0===this.$input.val().length||(this._defaults.timeOnly===!0?t=this.formattedTime:this._defaults.timeOnly!==!0&&(this._defaults.alwaysSetTime||timeAvailable)&&(t+=this._defaults.separator+this.formattedTime),this.formattedDateTime=t,this._defaults.showTimepicker?this.$altInput&&this._defaults.altFieldTimeOnly===!0?(this.$altInput.val(this.formattedTime),this.$input.val(this.formattedDate)):this.$altInput?(this.$altInput.val(t),this.$input.val(t)):this.$input.val(t):this.$input.val(this.formattedDate),this.$input.trigger("change"))}}),$.fn.extend({timepicker:function(e){e=e||{};var t=arguments;return"object"==typeof e&&(t[0]=$.extend(e,{timeOnly:!0})),$(this).each(function(){$.fn.datetimepicker.apply($(this),t)})},datetimepicker:function(e){e=e||{};var t=arguments;return"string"==typeof e?"getDate"==e?$.fn.datepicker.apply($(this[0]),t):this.each(function(){var e=$(this);e.datepicker.apply(e,t)}):this.each(function(){var t=$(this);t.datepicker($.timepicker._newInst(t,e)._defaults)})}}),$.datepicker._base_selectDate=$.datepicker._selectDate,$.datepicker._selectDate=function(e,t){var i=this._getInst($(e)[0]),n=this._get(i,"timepicker");n?(n._limitMinMaxDateTime(i,!0),i.inline=i.stay_open=!0,this._base_selectDate(e,t+n._defaults.separator+n.formattedTime),i.inline=i.stay_open=!1,this._notifyChange(i),this._updateDatepicker(i)):this._base_selectDate(e,t)},$.datepicker._base_updateDatepicker=$.datepicker._updateDatepicker,$.datepicker._updateDatepicker=function(e){if("boolean"!=typeof e.stay_open||e.stay_open===!1){this._base_updateDatepicker(e);var t=this._get(e,"timepicker");t&&t._addTimePicker(e)}},$.datepicker._base_doKeyPress=$.datepicker._doKeyPress,$.datepicker._doKeyPress=function(e){var t=$.datepicker._getInst(e.target),i=$.datepicker._get(t,"timepicker");if(i&&$.datepicker._get(t,"constrainInput")){var n=i._defaults.ampm,s=i._defaults.timeFormat.toString().replace(/[hms]/g,"").replace(/TT/g,n?"APM":"").replace(/Tt/g,n?"AaPpMm":"").replace(/tT/g,n?"AaPpMm":"").replace(/T/g,n?"AP":"").replace(/tt/g,n?"apm":"").replace(/t/g,n?"ap":"")+" "+i._defaults.separator+$.datepicker._possibleChars($.datepicker._get(t,"dateFormat")),a=String.fromCharCode(void 0===e.charCode?e.keyCode:e.charCode);return e.ctrlKey||" ">a||!s||s.indexOf(a)>-1}return $.datepicker._base_doKeyPress(e)},$.datepicker._base_doKeyUp=$.datepicker._doKeyUp,$.datepicker._doKeyUp=function(e){var t=$.datepicker._getInst(e.target),i=$.datepicker._get(t,"timepicker");if(i&&i._defaults.timeOnly&&t.input.val()!=t.lastVal)try{$.datepicker._updateDatepicker(t)}catch(n){$.datepicker.log(n)}return $.datepicker._base_doKeyUp(e)},$.datepicker._base_gotoToday=$.datepicker._gotoToday,$.datepicker._gotoToday=function(e){this._base_gotoToday(e),this._setTime(this._getInst($(e)[0]),new Date)},$.datepicker._disableTimepickerDatepicker=function(e){var t=this._getInst(e),i=this._get(t,"timepicker");$(e).datepicker("getDate"),i&&(i._defaults.showTimepicker=!1,i._updateDateTime(t))},$.datepicker._enableTimepickerDatepicker=function(e){var t=this._getInst(e),i=this._get(t,"timepicker");$(e).datepicker("getDate"),i&&(i._defaults.showTimepicker=!0,i._addTimePicker(t),i._updateDateTime(t))},$.datepicker._setTime=function(e,t){var i=this._get(e,"timepicker");if(i){var n=i._defaults,s=t?t.getHours():n.hour,a=t?t.getMinutes():n.minute,r=t?t.getSeconds():n.second;(s<n.hourMin||s>n.hourMax||a<n.minuteMin||a>n.minuteMax||r<n.secondMin||r>n.secondMax)&&(s=n.hourMin,a=n.minuteMin,r=n.secondMin),i.hour_slider?i.hour_slider.slider("value",s):i.hour=s,i.minute_slider?i.minute_slider.slider("value",a):i.minute=a,i.second_slider?i.second_slider.slider("value",r):i.second=r,i._onTimeChange(),i._updateDateTime(e)}},$.datepicker._setTimeDatepicker=function(e,t,i){var n=this._getInst(e),s=this._get(n,"timepicker");if(s){this._setDateFromField(n);var a;t&&("string"==typeof t?(s._parseTime(t,i),a=new Date,a.setHours(s.hour,s.minute,s.second)):a=new Date(t.getTime()),"Invalid Date"==a.toString()&&(a=void 0),this._setTime(n,a))}},$.datepicker._base_setDateDatepicker=$.datepicker._setDateDatepicker,$.datepicker._setDateDatepicker=function(e,t){var i=this._getInst(e),n=t instanceof Date?new Date(t.getTime()):t;this._updateDatepicker(i),this._base_setDateDatepicker.apply(this,arguments),this._setTimeDatepicker(e,n,!0)},$.datepicker._base_getDateDatepicker=$.datepicker._getDateDatepicker,$.datepicker._getDateDatepicker=function(e,t){var i=this._getInst(e),n=this._get(i,"timepicker");if(n){this._setDateFromField(i,t);var s=this._getDate(i);return s&&n._parseTime($(e).val(),n.timeOnly)&&s.setHours(n.hour,n.minute,n.second),s}return this._base_getDateDatepicker(e,t)},$.timepicker=new Timepicker,$.timepicker.version="0.9.5"}(jQuery);