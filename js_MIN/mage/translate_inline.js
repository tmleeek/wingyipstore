var TranslateInline=Class.create();TranslateInline.prototype={initialize:function(t,e,i){if(this.ajaxUrl=e,this.area=i,this.trigTimer=null,this.trigContentEl=null,Prototype.Browser.IE){$$("*[translate]").each(this.initializeElement.bind(this));var a=this;Ajax.Responders.register({onComplete:function(){window.setTimeout(a.reinitElements.bind(a),50)}});var s="undefined"!=typeof HTMLElement?HTMLElement:Element,n=s.prototype.update;s.prototype.update=function(){n.apply(this,arguments),$(this).select("*[translate]").each(a.initializeElement.bind(a))}}this.trigEl=$(t),this.trigEl.observe("click",this.formShow.bind(this)),Event.observe(document.body,"mousemove",function(e){var i=Event.element(e);$(i).match("*[translate]")||(i=i.up("*[translate]")),i&&$(i).match("*[translate]")?this.trigShow(i,e):Event.element(e).match("#"+t)?this.trigHideClear():this.trigHideDelayed()}.bind(this)),this.helperDiv=document.createElement("div")},initializeElement:function(t){t.initializedTranslate||(t.addClassName("translate-inline"),t.initializedTranslate=!0)},reinitElements:function(){$$("*[translate]").each(this.initializeElement.bind(this))},trigShow:function(t,e){if(this.trigContentEl!=t){this.trigHideClear(),this.trigContentEl=t;var i=Element.cumulativeOffset(t);this.trigEl.style.left=i[0]+"px",this.trigEl.style.top=i[1]+"px",this.trigEl.style.display="block",Event.stop(e)}},trigHide:function(){this.trigEl.style.display="none",this.trigContentEl=null},trigHideDelayed:function(){null===this.trigTimer&&(this.trigTimer=window.setTimeout(this.trigHide.bind(this),2e3))},trigHideClear:function(){clearInterval(this.trigTimer),this.trigTimer=null},formShow:function(){if(!this.formIsShown){this.formIsShown=!0;var el=this.trigContentEl;if(el){this.trigHideClear(),eval("var data = "+el.getAttribute("translate"));var content='<form id="translate-inline-form">',t=new Template('<div class="magento_table_container"><table cellspacing="0"><tr><th class="label">Location:</th><td class="value">#{location}</td></tr><tr><th class="label">Scope:</th><td class="value">#{scope}</td></tr><tr><th class="label">Shown:</th><td class="value">#{shown_escape}</td></tr><tr><th class="label">Original:</th><td class="value">#{original_escape}</td></tr><tr><th class="label">Translated:</th><td class="value">#{translated_escape}</td></tr><tr><th class="label"><label for="perstore_#{i}">Store View Specific:</label></th><td class="value"><input id="perstore_#{i}" name="translate[#{i}][perstore]" type="checkbox" value="1"/></td></tr><tr><th class="label"><label for="custom_#{i}">Custom:</label></th><td class="value"><input name="translate[#{i}][original]" type="hidden" value="#{scope}::#{original_escape}"/><input id="custom_#{i}" name="translate[#{i}][custom]" class="input-text" value="#{translated_escape}" /></td></tr></table></div>');for(i=0;i<data.length;i++)data[i].i=i,data[i].shown_escape=this.escapeHTML(data[i].shown),data[i].translated_escape=this.escapeHTML(data[i].translated),data[i].original_escape=this.escapeHTML(data[i].original),content+=t.evaluate(data[i]);content+='</form><p class="a-center accent">Please refresh the page to see your changes after submitting this form.</p>',this.overlayShowEffectOptions=Windows.overlayShowEffectOptions,this.overlayHideEffectOptions=Windows.overlayHideEffectOptions,Windows.overlayShowEffectOptions={duration:0},Windows.overlayHideEffectOptions={duration:0},Dialog.confirm(content,{draggable:!0,resizable:!0,closable:!0,className:"magento",title:"Translation",width:650,height:470,zIndex:2100,recenterAuto:!1,hideEffect:Element.hide,showEffect:Element.show,id:"translate-inline",buttonClass:"form-button button",okLabel:"Submit",ok:this.formOk.bind(this),cancel:this.formClose.bind(this),onClose:this.formClose.bind(this)}),this.trigHide()}}},formOk:function(t){if(!this.formIsSubmitted){this.formIsSubmitted=!0;for(var e=$("translate-inline-form").getInputs(),i={},a=0;a<e.length;a++)"checkbox"==e[a].type?e[a].checked&&(i[e[a].name]=e[a].value):i[e[a].name]=e[a].value;i.area=this.area,new Ajax.Request(this.ajaxUrl,{method:"post",parameters:i,onComplete:this.ajaxComplete.bind(this,t)}),this.formIsSubmitted=!1}},ajaxComplete:function(t){t.close(),this.formClose(t)},formClose:function(){Windows.overlayShowEffectOptions=this.overlayShowEffectOptions,Windows.overlayHideEffectOptions=this.overlayHideEffectOptions,this.formIsShown=!1},escapeHTML:function(t){this.helperDiv.innerHTML="";var e=document.createTextNode(t);this.helperDiv.appendChild(e);var i=this.helperDiv.innerHTML;return i=i.replace(/"/g,"&quot;")}};