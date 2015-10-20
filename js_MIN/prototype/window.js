var Window=Class.create();Window.keepMultiModalWindow=!1,Window.hasEffectLib="undefined"!=typeof Effect,Window.resizeEffectDuration=.4,Window.prototype={initialize:function(){var t,e=0;if(arguments.length>0&&("string"==typeof arguments[0]?(t=arguments[0],e=1):t=arguments[0]?arguments[0].id:null),t||(t="window_"+(new Date).getTime()),$(t)&&alert("Window "+t+" is already registered in the DOM! Make sure you use setDestroyOnClose() or destroyOnClose: true in the constructor"),this.options=Object.extend({className:"dialog",windowClassName:null,blurClassName:null,minWidth:100,minHeight:20,resizable:!0,closable:!0,minimizable:!0,maximizable:!0,draggable:!0,userData:null,showEffect:Window.hasEffectLib?Effect.Appear:Element.show,hideEffect:Window.hasEffectLib?Effect.Fade:Element.hide,showEffectOptions:{},hideEffectOptions:{},effectOptions:null,parent:document.body,title:"&nbsp;",url:null,onload:Prototype.emptyFunction,width:200,height:300,opacity:1,recenterAuto:!0,wiredDrag:!1,closeOnEsc:!0,closeCallback:null,destroyOnClose:!1,gridX:1,gridY:1},arguments[e]||{}),this.options.blurClassName&&(this.options.focusClassName=this.options.className),"undefined"==typeof this.options.top&&"undefined"==typeof this.options.bottom&&(this.options.top=this._round(500*Math.random(),this.options.gridY)),"undefined"==typeof this.options.left&&"undefined"==typeof this.options.right&&(this.options.left=this._round(500*Math.random(),this.options.gridX)),this.options.effectOptions&&(Object.extend(this.options.hideEffectOptions,this.options.effectOptions),Object.extend(this.options.showEffectOptions,this.options.effectOptions),this.options.showEffect==Element.Appear&&(this.options.showEffectOptions.to=this.options.opacity)),Window.hasEffectLib&&(this.options.showEffect==Effect.Appear&&(this.options.showEffectOptions.to=this.options.opacity),this.options.hideEffect==Effect.Fade&&(this.options.hideEffectOptions.from=this.options.opacity)),this.options.hideEffect==Element.hide&&(this.options.hideEffect=function(){Element.hide(this.element),this.options.destroyOnClose&&this.destroy()}.bind(this)),this.options.parent!=document.body&&(this.options.parent=$(this.options.parent)),this.element=this._createWindow(t),this.element.win=this,this.eventMouseDown=this._initDrag.bindAsEventListener(this),this.eventMouseUp=this._endDrag.bindAsEventListener(this),this.eventMouseMove=this._updateDrag.bindAsEventListener(this),this.eventOnLoad=this._getWindowBorderSize.bindAsEventListener(this),this.eventMouseDownContent=this.toFront.bindAsEventListener(this),this.eventResize=this._recenter.bindAsEventListener(this),this.eventKeyUp=this._keyUp.bindAsEventListener(this),this.topbar=$(this.element.id+"_top"),this.bottombar=$(this.element.id+"_bottom"),this.content=$(this.element.id+"_content"),Event.observe(this.topbar,"mousedown",this.eventMouseDown),Event.observe(this.bottombar,"mousedown",this.eventMouseDown),Event.observe(this.content,"mousedown",this.eventMouseDownContent),Event.observe(window,"load",this.eventOnLoad),Event.observe(window,"resize",this.eventResize),Event.observe(window,"scroll",this.eventResize),Event.observe(document,"keyup",this.eventKeyUp),Event.observe(this.options.parent,"scroll",this.eventResize),this.options.draggable){var i=this;[this.topbar,this.topbar.up().previous(),this.topbar.up().next()].each(function(t){t.observe("mousedown",i.eventMouseDown),t.addClassName("top_draggable")}),[this.bottombar.up(),this.bottombar.up().previous(),this.bottombar.up().next()].each(function(t){t.observe("mousedown",i.eventMouseDown),t.addClassName("bottom_draggable")})}this.options.resizable&&(this.sizer=$(this.element.id+"_sizer"),Event.observe(this.sizer,"mousedown",this.eventMouseDown)),this.useLeft=null,this.useTop=null,"undefined"!=typeof this.options.left?(this.element.setStyle({left:parseFloat(this.options.left)+"px"}),this.useLeft=!0):(this.element.setStyle({right:parseFloat(this.options.right)+"px"}),this.useLeft=!1),"undefined"!=typeof this.options.top?(this.element.setStyle({top:parseFloat(this.options.top)+"px"}),this.useTop=!0):(this.element.setStyle({bottom:parseFloat(this.options.bottom)+"px"}),this.useTop=!1),this.storedLocation=null,this.setOpacity(this.options.opacity),this.options.zIndex&&this.setZIndex(this.options.zIndex),this.options.destroyOnClose&&this.setDestroyOnClose(!0),this._getWindowBorderSize(),this.width=this.options.width,this.height=this.options.height,this.visible=!1,this.constraint=!1,this.constraintPad={top:0,left:0,bottom:0,right:0},this.width&&this.height&&this.setSize(this.options.width,this.options.height),this.setTitle(this.options.title),Windows.register(this)},destroy:function(){if(this._notify("onDestroy"),Event.stopObserving(this.topbar,"mousedown",this.eventMouseDown),Event.stopObserving(this.bottombar,"mousedown",this.eventMouseDown),Event.stopObserving(this.content,"mousedown",this.eventMouseDownContent),Event.stopObserving(window,"load",this.eventOnLoad),Event.stopObserving(window,"resize",this.eventResize),Event.stopObserving(window,"scroll",this.eventResize),Event.stopObserving(this.content,"load",this.options.onload),Event.stopObserving(document,"keyup",this.eventKeyUp),this._oldParent){for(var t=this.getContent(),e=null,i=0;i<t.childNodes.length&&(e=t.childNodes[i],1!=e.nodeType);i++)e=null;e&&this._oldParent.appendChild(e),this._oldParent=null}this.sizer&&Event.stopObserving(this.sizer,"mousedown",this.eventMouseDown),this.options.url&&(this.content.src=null),this.iefix&&Element.remove(this.iefix),Element.remove(this.element),Windows.unregister(this)},setCloseCallback:function(t){this.options.closeCallback=t},getContent:function(){return this.content},setContent:function(t,e,i){var s=$(t);if(null==s)throw"Unable to find element '"+t+"' in DOM";this._oldParent=s.parentNode;var o=null,n=null;e&&(o=Element.getDimensions(s)),i&&(n=Position.cumulativeOffset(s));var h=this.getContent();this.setHTMLContent(""),h=this.getContent(),h.appendChild(s),s.show(),e&&this.setSize(o.width,o.height),i&&this.setLocation(n[1]-this.heightN,n[0]-this.widthW)},setHTMLContent:function(t){if(this.options.url){this.content.src=null,this.options.url=null;var e='<div id="'+this.getId()+'_content" class="'+this.options.className+'_content"> </div>';$(this.getId()+"_table_content").innerHTML=e,this.content=$(this.element.id+"_content")}this.getContent().innerHTML=t},setAjaxContent:function(t,e,i,s){this.showFunction=i?"showCenter":"show",this.showModal=s||!1,e=e||{},this.setHTMLContent(""),this.onComplete=e.onComplete,this._onCompleteHandler||(this._onCompleteHandler=this._setAjaxContent.bind(this)),e.onComplete=this._onCompleteHandler,new Ajax.Request(t,e),e.onComplete=this.onComplete},_setAjaxContent:function(t){Element.update(this.getContent(),t.responseText),this.onComplete&&this.onComplete(t),this.onComplete=null,this[this.showFunction](this.showModal)},setURL:function(t){this.options.url&&(this.content.src=null),this.options.url=t;var e="<iframe frameborder='0' name='"+this.getId()+"_content'  id='"+this.getId()+"_content' src='"+t+"' width='"+this.width+"' height='"+this.height+"'> </iframe>";$(this.getId()+"_table_content").innerHTML=e,this.content=$(this.element.id+"_content")},getURL:function(){return this.options.url?this.options.url:null},refresh:function(){this.options.url&&($(this.element.getAttribute("id")+"_content").src=this.options.url)},setCookie:function(t,e,i,s,o){t=t||this.element.id,this.cookie=[t,e,i,s,o];var n=WindowUtilities.getCookie(t);if(n){var h=n.split(","),l=h[0].split(":"),r=h[1].split(":"),d=parseFloat(h[2]),a=parseFloat(h[3]),c=h[4],u=h[5];this.setSize(d,a),"true"==c?this.doMinimize=!0:"true"==u&&(this.doMaximize=!0),this.useLeft="l"==l[0],this.useTop="t"==r[0],this.element.setStyle(this.useLeft?{left:l[1]}:{right:l[1]}),this.element.setStyle(this.useTop?{top:r[1]}:{bottom:r[1]})}},getId:function(){return this.element.id},setDestroyOnClose:function(){this.options.destroyOnClose=!0},setConstraint:function(t,e){this.constraint=t,this.constraintPad=Object.extend(this.constraintPad,e||{}),this.useTop&&this.useLeft&&this.setLocation(parseFloat(this.element.style.top),parseFloat(this.element.style.left))},_initDrag:function(t){if(!(Event.element(t)==this.sizer&&this.isMinimized()||Event.element(t)!=this.sizer&&this.isMaximized())){if(Prototype.Browser.IE&&0==this.heightN&&this._getWindowBorderSize(),this.pointer=[this._round(Event.pointerX(t),this.options.gridX),this._round(Event.pointerY(t),this.options.gridY)],this.currentDrag=this.options.wiredDrag?this._createWiredElement():this.element,Event.element(t)==this.sizer)this.doResize=!0,this.widthOrg=this.width,this.heightOrg=this.height,this.bottomOrg=parseFloat(this.element.getStyle("bottom")),this.rightOrg=parseFloat(this.element.getStyle("right")),this._notify("onStartResize");else{this.doResize=!1;var e=$(this.getId()+"_close");if(e&&Position.within(e,this.pointer[0],this.pointer[1]))return void(this.currentDrag=null);if(this.toFront(),!this.options.draggable)return;this._notify("onStartMove")}Event.observe(document,"mouseup",this.eventMouseUp,!1),Event.observe(document,"mousemove",this.eventMouseMove,!1),WindowUtilities.disableScreen("__invisible__","__invisible__",this.overlayOpacity),document.body.ondrag=function(){return!1},document.body.onselectstart=function(){return!1},this.currentDrag.show(),Event.stop(t)}},_round:function(t,e){return 1==e?t:t=Math.floor(t/e)*e},_updateDrag:function(t){var e=[this._round(Event.pointerX(t),this.options.gridX),this._round(Event.pointerY(t),this.options.gridY)],i=e[0]-this.pointer[0],s=e[1]-this.pointer[1];if(this.doResize){var o=this.widthOrg+i,n=this.heightOrg+s;i=this.width-this.widthOrg,s=this.height-this.heightOrg,this.useLeft?o=this._updateWidthConstraint(o):this.currentDrag.setStyle({right:this.rightOrg-i+"px"}),this.useTop?n=this._updateHeightConstraint(n):this.currentDrag.setStyle({bottom:this.bottomOrg-s+"px"}),this.setSize(o,n),this._notify("onResize")}else{if(this.pointer=e,this.useLeft){var h=parseFloat(this.currentDrag.getStyle("left"))+i,l=this._updateLeftConstraint(h);this.pointer[0]+=l-h,this.currentDrag.setStyle({left:l+"px"})}else this.currentDrag.setStyle({right:parseFloat(this.currentDrag.getStyle("right"))-i+"px"});if(this.useTop){var r=parseFloat(this.currentDrag.getStyle("top"))+s,d=this._updateTopConstraint(r);this.pointer[1]+=d-r,this.currentDrag.setStyle({top:d+"px"})}else this.currentDrag.setStyle({bottom:parseFloat(this.currentDrag.getStyle("bottom"))-s+"px"});this._notify("onMove")}this.iefix&&this._fixIEOverlapping(),this._removeStoreLocation(),Event.stop(t)},_endDrag:function(t){WindowUtilities.enableScreen("__invisible__"),this._notify(this.doResize?"onEndResize":"onEndMove"),Event.stopObserving(document,"mouseup",this.eventMouseUp,!1),Event.stopObserving(document,"mousemove",this.eventMouseMove,!1),Event.stop(t),this._hideWiredElement(),this._saveCookie(),document.body.ondrag=null,document.body.onselectstart=null},_updateLeftConstraint:function(t){if(this.constraint&&this.useLeft&&this.useTop){var e=this.options.parent==document.body?WindowUtilities.getPageSize().windowWidth:this.options.parent.getDimensions().width;t<this.constraintPad.left&&(t=this.constraintPad.left),t+this.width+this.widthE+this.widthW>e-this.constraintPad.right&&(t=e-this.constraintPad.right-this.width-this.widthE-this.widthW)}return t},_updateTopConstraint:function(t){if(this.constraint&&this.useLeft&&this.useTop){var e=this.options.parent==document.body?WindowUtilities.getPageSize().windowHeight:this.options.parent.getDimensions().height,i=this.height+this.heightN+this.heightS;t<this.constraintPad.top&&(t=this.constraintPad.top),t+i>e-this.constraintPad.bottom&&(t=e-this.constraintPad.bottom-i)}return t},_updateWidthConstraint:function(t){if(this.constraint&&this.useLeft&&this.useTop){var e=this.options.parent==document.body?WindowUtilities.getPageSize().windowWidth:this.options.parent.getDimensions().width,i=parseFloat(this.element.getStyle("left"));i+t+this.widthE+this.widthW>e-this.constraintPad.right&&(t=e-this.constraintPad.right-i-this.widthE-this.widthW)}return t},_updateHeightConstraint:function(t){if(this.constraint&&this.useLeft&&this.useTop){var e=this.options.parent==document.body?WindowUtilities.getPageSize().windowHeight:this.options.parent.getDimensions().height,i=parseFloat(this.element.getStyle("top"));i+t+this.heightN+this.heightS>e-this.constraintPad.bottom&&(t=e-this.constraintPad.bottom-i-this.heightN-this.heightS)}return t},_createWindow:function(t){var e=this.options.className,i=document.createElement("div");i.setAttribute("id",t),i.className="dialog",this.options.windowClassName&&(i.className+=" "+this.options.windowClassName);var s;s=this.options.url?'<iframe frameborder="0" name="'+t+'_content"  id="'+t+'_content" src="'+this.options.url+'"> </iframe>':'<div id="'+t+'_content" class="'+e+'_content"> </div>';var o=this.options.closable?"<div class='"+e+"_close' id='"+t+"_close' onclick='Windows.close(\""+t+"\", event)'> </div>":"",n=this.options.minimizable?"<div class='"+e+"_minimize' id='"+t+"_minimize' onclick='Windows.minimize(\""+t+"\", event)'> </div>":"",h=this.options.maximizable?"<div class='"+e+"_maximize' id='"+t+"_maximize' onclick='Windows.maximize(\""+t+"\", event)'> </div>":"",l=this.options.resizable?"class='"+e+"_sizer' id='"+t+"_sizer'":"class='"+e+"_se'";return i.innerHTML=o+n+h+"      <a href='#' id='"+t+"_focus_anchor'><!-- --></a>      <table id='"+t+"_row1' class=\"top table_window\">        <tr>          <td class='"+e+"_nw'></td>          <td class='"+e+"_n'><div id='"+t+"_top' class='"+e+"_title title_window'>"+this.options.title+"</div></td>          <td class='"+e+"_ne'></td>        </tr>      </table>      <table id='"+t+"_row2' class=\"mid table_window\">        <tr>          <td class='"+e+"_w'></td>            <td id='"+t+"_table_content' class='"+e+"_content' valign='top'>"+s+"</td>          <td class='"+e+"_e'></td>        </tr>      </table>        <table id='"+t+"_row3' class=\"bot table_window\">        <tr>          <td class='"+e+"_sw'></td>            <td class='"+e+"_s'><div id='"+t+"_bottom' class='status_bar'><span style='float:left; width:1px; height:1px'></span></div></td>            <td "+l+"></td>        </tr>      </table>    ",Element.hide(i),this.options.parent.insertBefore(i,this.options.parent.firstChild),Event.observe($(t+"_content"),"load",this.options.onload),i},changeClassName:function(t){var e=this.options.className,i=this.getId();$A(["_close","_minimize","_maximize","_sizer","_content"]).each(function(s){this._toggleClassName($(i+s),e+s,t+s)}.bind(this)),this._toggleClassName($(i+"_top"),e+"_title",t+"_title"),$$("#"+i+" td").each(function(i){i.className=i.className.sub(e,t)}),this.options.className=t},_toggleClassName:function(t,e,i){t&&(t.removeClassName(e),t.addClassName(i))},setLocation:function(t,e){t=this._updateTopConstraint(t),e=this._updateLeftConstraint(e);var i=this.currentDrag||this.element;i.setStyle({top:t+"px"}),i.setStyle({left:e+"px"}),this.useLeft=!0,this.useTop=!0},getLocation:function(){var t={};return t=this.useTop?Object.extend(t,{top:this.element.getStyle("top")}):Object.extend(t,{bottom:this.element.getStyle("bottom")}),t=this.useLeft?Object.extend(t,{left:this.element.getStyle("left")}):Object.extend(t,{right:this.element.getStyle("right")})},getSize:function(){return{width:this.width,height:this.height}},setSize:function(t,e,i){if(t=parseFloat(t),e=parseFloat(e),!this.minimized&&t<this.options.minWidth&&(t=this.options.minWidth),!this.minimized&&e<this.options.minHeight&&(e=this.options.minHeight),this.options.maxHeight&&e>this.options.maxHeight&&(e=this.options.maxHeight),this.options.maxWidth&&t>this.options.maxWidth&&(t=this.options.maxWidth),this.useTop&&this.useLeft&&Window.hasEffectLib&&Effect.ResizeWindow&&i)new Effect.ResizeWindow(this,null,null,t,e,{duration:Window.resizeEffectDuration});else{this.width=t,this.height=e;var s=this.currentDrag?this.currentDrag:this.element;if(s.setStyle({width:t+this.widthW+this.widthE+"px"}),s.setStyle({height:e+this.heightN+this.heightS+"px"}),!this.currentDrag||this.currentDrag==this.element){var o=$(this.element.id+"_content");o.setStyle({height:e+"px"}),o.setStyle({width:t+"px"})}}},updateHeight:function(){this.setSize(this.width,this.content.scrollHeight,!0)},updateWidth:function(){this.setSize(this.content.scrollWidth,this.height,!0)},toFront:function(){this.element.style.zIndex<Windows.maxZIndex&&this.setZIndex(Windows.maxZIndex+1),this.iefix&&this._fixIEOverlapping()},getBounds:function(t){this.width&&this.height&&this.visible||this.computeBounds();var e=this.width,i=this.height;t||(e+=this.widthW+this.widthE,i+=this.heightN+this.heightS);var s=Object.extend(this.getLocation(),{width:e+"px",height:i+"px"});return s},computeBounds:function(){if(!this.width||!this.height){var t=WindowUtilities._computeSize(this.content.innerHTML,this.content.id,this.width,this.height,0,this.options.className);this.height?this.width=t+5:this.height=t+5}this.setSize(this.width,this.height),this.centered&&this._center(this.centerTop,this.centerLeft)},show:function(t){if(this.visible=!0,t){if("undefined"==typeof this.overlayOpacity){var e=this;return void setTimeout(function(){e.show(t)},10)}Windows.addModalWindow(this),this.modal=!0,this.setZIndex(Windows.maxZIndex+1),Windows.unsetOverflow(this)}else this.element.style.zIndex||this.setZIndex(Windows.maxZIndex+1);this.oldStyle&&this.getContent().setStyle({overflow:this.oldStyle}),this.computeBounds(),this._notify("onBeforeShow"),this.options.showEffect!=Element.show&&this.options.showEffectOptions?this.options.showEffect(this.element,this.options.showEffectOptions):this.options.showEffect(this.element),this._checkIEOverlapping(),WindowUtilities.focusedWindow=this,this._notify("onShow"),$(this.element.id+"_focus_anchor").focus()},showCenter:function(t,e,i){this.centered=!0,this.centerTop=e,this.centerLeft=i,this.show(t)},isVisible:function(){return this.visible},_center:function(t,e){var i=WindowUtilities.getWindowScroll(this.options.parent),s=WindowUtilities.getPageSize(this.options.parent);"undefined"==typeof t&&(t=(s.windowHeight-(this.height+this.heightN+this.heightS))/2),t+=i.top,"undefined"==typeof e&&(e=(s.windowWidth-(this.width+this.widthW+this.widthE))/2),e+=i.left,this.setLocation(t,e),this.toFront()},_recenter:function(){if(this.centered){var t=WindowUtilities.getPageSize(this.options.parent),e=WindowUtilities.getWindowScroll(this.options.parent);if(this.pageSize&&this.pageSize.windowWidth==t.windowWidth&&this.pageSize.windowHeight==t.windowHeight&&this.windowScroll.left==e.left&&this.windowScroll.top==e.top)return;this.pageSize=t,this.windowScroll=e,$("overlay_modal")&&$("overlay_modal").setStyle({height:t.pageHeight+"px"}),this.options.recenterAuto&&this._center(this.centerTop,this.centerLeft)}},hide:function(){this.visible=!1,this.modal&&(Windows.removeModalWindow(this),Windows.resetOverflow()),this.oldStyle=this.getContent().getStyle("overflow")||"auto",this.getContent().setStyle({overflow:"hidden"}),this.options.hideEffect(this.element,this.options.hideEffectOptions),this.iefix&&this.iefix.hide(),this.doNotNotifyHide||this._notify("onHide")},close:function(){if(this.visible){if(this.options.closeCallback&&!this.options.closeCallback(this))return;if(this.options.destroyOnClose){var t=this.destroy.bind(this);if(this.options.hideEffectOptions.afterFinish){var e=this.options.hideEffectOptions.afterFinish;this.options.hideEffectOptions.afterFinish=function(){e(),t()}}else this.options.hideEffectOptions.afterFinish=function(){t()}}Windows.updateFocusedWindow(),this.doNotNotifyHide=!0,this.hide(),this.doNotNotifyHide=!1,this._notify("onClose")}},minimize:function(){if(!this.resizing){var t=$(this.getId()+"_row2");if(this.minimized){this.minimized=!1;var e=this.r2Height;if(this.r2Height=null,this.useLeft&&this.useTop&&Window.hasEffectLib&&Effect.ResizeWindow)new Effect.ResizeWindow(this,null,null,null,this.height+e,{duration:Window.resizeEffectDuration});else{var i=this.element.getHeight()+e;this.height+=e,this.element.setStyle({height:i+"px"}),t.show()}if(!this.useTop){var s=parseFloat(this.element.getStyle("bottom"));this.element.setStyle({bottom:s-e+"px"})}this.toFront()}else{this.minimized=!0;var e=t.getDimensions().height;this.r2Height=e;var i=this.element.getHeight()-e;if(this.useLeft&&this.useTop&&Window.hasEffectLib&&Effect.ResizeWindow?new Effect.ResizeWindow(this,null,null,null,this.height-e,{duration:Window.resizeEffectDuration}):(this.height-=e,this.element.setStyle({height:i+"px"}),t.hide()),!this.useTop){var s=parseFloat(this.element.getStyle("bottom"));this.element.setStyle({bottom:s+e+"px"})}}this._notify("onMinimize"),this._saveCookie()}},maximize:function(){if(!this.isMinimized()&&!this.resizing){if(Prototype.Browser.IE&&0==this.heightN&&this._getWindowBorderSize(),null!=this.storedLocation)this._restoreLocation(),this.iefix&&this.iefix.hide();else{this._storeLocation(),Windows.unsetOverflow(this);var t=WindowUtilities.getWindowScroll(this.options.parent),e=WindowUtilities.getPageSize(this.options.parent),i=t.left,s=t.top;if(this.options.parent!=document.body){t={top:0,left:0,bottom:0,right:0};var o=this.options.parent.getDimensions();e.windowWidth=o.width,e.windowHeight=o.height,s=0,i=0}this.constraint&&(e.windowWidth-=Math.max(0,this.constraintPad.left)+Math.max(0,this.constraintPad.right),e.windowHeight-=Math.max(0,this.constraintPad.top)+Math.max(0,this.constraintPad.bottom),i+=Math.max(0,this.constraintPad.left),s+=Math.max(0,this.constraintPad.top));var n=e.windowWidth-this.widthW-this.widthE,h=e.windowHeight-this.heightN-this.heightS;this.useLeft&&this.useTop&&Window.hasEffectLib&&Effect.ResizeWindow?new Effect.ResizeWindow(this,s,i,n,h,{duration:Window.resizeEffectDuration}):(this.setSize(n,h),this.element.setStyle(this.useLeft?{left:i}:{right:i}),this.element.setStyle(this.useTop?{top:s}:{bottom:s})),this.toFront(),this.iefix&&this._fixIEOverlapping()}this._notify("onMaximize"),this._saveCookie()}},isMinimized:function(){return this.minimized},isMaximized:function(){return null!=this.storedLocation},setOpacity:function(t){Element.setOpacity&&Element.setOpacity(this.element,t)},setZIndex:function(t){this.element.setStyle({zIndex:t}),Windows.updateZindex(t,this)},setTitle:function(t){t&&""!=t||(t="&nbsp;"),Element.update(this.element.id+"_top",t)},getTitle:function(){return $(this.element.id+"_top").innerHTML},setStatusBar:function(t){$(this.getId()+"_bottom");"object"==typeof t?this.bottombar.firstChild?this.bottombar.replaceChild(t,this.bottombar.firstChild):this.bottombar.appendChild(t):this.bottombar.innerHTML=t},_checkIEOverlapping:function(){!this.iefix&&navigator.appVersion.indexOf("MSIE")>0&&navigator.userAgent.indexOf("Opera")<0&&"absolute"==this.element.getStyle("position")&&(new Insertion.After(this.element.id,'<iframe id="'+this.element.id+'_iefix" style="display:none;position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);" src="javascript:false;" frameborder="0" scrolling="no"></iframe>'),this.iefix=$(this.element.id+"_iefix")),this.iefix&&setTimeout(this._fixIEOverlapping.bind(this),50)},_fixIEOverlapping:function(){Position.clone(this.element,this.iefix),this.iefix.style.zIndex=this.element.style.zIndex-1,this.iefix.show()},_keyUp:function(t){27==t.keyCode&&this.options.closeOnEsc&&this.close()},_getWindowBorderSize:function(){var t=this._createHiddenDiv(this.options.className+"_n");this.heightN=Element.getDimensions(t).height,t.parentNode.removeChild(t);var t=this._createHiddenDiv(this.options.className+"_s");this.heightS=Element.getDimensions(t).height,t.parentNode.removeChild(t);var t=this._createHiddenDiv(this.options.className+"_e");this.widthE=Element.getDimensions(t).width,t.parentNode.removeChild(t);var t=this._createHiddenDiv(this.options.className+"_w");this.widthW=Element.getDimensions(t).width,t.parentNode.removeChild(t);var t=document.createElement("div");t.className="overlay_"+this.options.className,document.body.appendChild(t);var e=this;setTimeout(function(){e.overlayOpacity=$(t).getStyle("opacity"),t.parentNode.removeChild(t)},10),Prototype.Browser.IE&&(this.heightS=$(this.getId()+"_row3").getDimensions().height,this.heightN=$(this.getId()+"_row1").getDimensions().height),Prototype.Browser.WebKit&&Prototype.Browser.WebKitVersion<420&&this.setSize(this.width,this.height),this.doMaximize&&this.maximize(),this.doMinimize&&this.minimize()},_createHiddenDiv:function(t){var e=document.body,i=document.createElement("div");return i.setAttribute("id",this.element.id+"_tmp"),i.className=t,i.style.display="none",i.innerHTML="",e.insertBefore(i,e.firstChild),i},_storeLocation:function(){null==this.storedLocation&&(this.storedLocation={useTop:this.useTop,useLeft:this.useLeft,top:this.element.getStyle("top"),bottom:this.element.getStyle("bottom"),left:this.element.getStyle("left"),right:this.element.getStyle("right"),width:this.width,height:this.height})},_restoreLocation:function(){null!=this.storedLocation&&(this.useLeft=this.storedLocation.useLeft,this.useTop=this.storedLocation.useTop,this.useLeft&&this.useTop&&Window.hasEffectLib&&Effect.ResizeWindow?new Effect.ResizeWindow(this,this.storedLocation.top,this.storedLocation.left,this.storedLocation.width,this.storedLocation.height,{duration:Window.resizeEffectDuration}):(this.element.setStyle(this.useLeft?{left:this.storedLocation.left}:{right:this.storedLocation.right}),this.element.setStyle(this.useTop?{top:this.storedLocation.top}:{bottom:this.storedLocation.bottom}),this.setSize(this.storedLocation.width,this.storedLocation.height)),Windows.resetOverflow(),this._removeStoreLocation())},_removeStoreLocation:function(){this.storedLocation=null},_saveCookie:function(){if(this.cookie){var t="";t+=this.useLeft?"l:"+(this.storedLocation?this.storedLocation.left:this.element.getStyle("left")):"r:"+(this.storedLocation?this.storedLocation.right:this.element.getStyle("right")),t+=this.useTop?",t:"+(this.storedLocation?this.storedLocation.top:this.element.getStyle("top")):",b:"+(this.storedLocation?this.storedLocation.bottom:this.element.getStyle("bottom")),t+=","+(this.storedLocation?this.storedLocation.width:this.width),t+=","+(this.storedLocation?this.storedLocation.height:this.height),t+=","+this.isMinimized(),t+=","+this.isMaximized(),WindowUtilities.setCookie(t,this.cookie)}},_createWiredElement:function(){if(!this.wiredElement){Prototype.Browser.IE&&this._getWindowBorderSize();var t=document.createElement("div");t.className="wired_frame "+this.options.className+"_wired_frame",t.style.position="absolute",this.options.parent.insertBefore(t,this.options.parent.firstChild),this.wiredElement=$(t)}this.wiredElement.setStyle(this.useLeft?{left:this.element.getStyle("left")}:{right:this.element.getStyle("right")}),this.wiredElement.setStyle(this.useTop?{top:this.element.getStyle("top")}:{bottom:this.element.getStyle("bottom")});var e=this.element.getDimensions();return this.wiredElement.setStyle({width:e.width+"px",height:e.height+"px"}),this.wiredElement.setStyle({zIndex:Windows.maxZIndex+30}),this.wiredElement},_hideWiredElement:function(){this.wiredElement&&this.currentDrag&&(this.currentDrag==this.element?this.currentDrag=null:(this.element.setStyle(this.useLeft?{left:this.currentDrag.getStyle("left")}:{right:this.currentDrag.getStyle("right")}),this.element.setStyle(this.useTop?{top:this.currentDrag.getStyle("top")}:{bottom:this.currentDrag.getStyle("bottom")}),this.currentDrag.hide(),this.currentDrag=null,this.doResize&&this.setSize(this.width,this.height)))},_notify:function(t){this.options[t]?this.options[t](this):Windows.notify(t,this)}};var Windows={windows:[],modalWindows:[],observers:[],focusedWindow:null,maxZIndex:0,overlayShowEffectOptions:{duration:.5},overlayHideEffectOptions:{duration:.5},addObserver:function(t){this.removeObserver(t),this.observers.push(t)},removeObserver:function(t){this.observers=this.observers.reject(function(e){return e==t})},notify:function(t,e){this.observers.each(function(i){i[t]&&i[t](t,e)})},getWindow:function(t){return this.windows.detect(function(e){return e.getId()==t})},getFocusedWindow:function(){return this.focusedWindow},updateFocusedWindow:function(){this.focusedWindow=this.windows.length>=2?this.windows[this.windows.length-2]:null},register:function(t){this.windows.push(t)},addModalWindow:function(t){0==this.modalWindows.length?WindowUtilities.disableScreen(t.options.className,"overlay_modal",t.overlayOpacity,t.getId(),t.options.parent):(Window.keepMultiModalWindow?($("overlay_modal").style.zIndex=Windows.maxZIndex+1,Windows.maxZIndex+=1,WindowUtilities._hideSelect(this.modalWindows.last().getId())):this.modalWindows.last().element.hide(),WindowUtilities._showSelect(t.getId())),this.modalWindows.push(t)},removeModalWindow:function(){this.modalWindows.pop(),0==this.modalWindows.length?WindowUtilities.enableScreen():Window.keepMultiModalWindow?(this.modalWindows.last().toFront(),WindowUtilities._showSelect(this.modalWindows.last().getId())):this.modalWindows.last().element.show()},register:function(t){this.windows.push(t)},unregister:function(t){this.windows=this.windows.reject(function(e){return e==t})},closeAll:function(){this.windows.each(function(t){Windows.close(t.getId())})},closeAllModalWindows:function(){WindowUtilities.enableScreen(),this.modalWindows.each(function(t){t&&t.close()})},minimize:function(t,e){var i=this.getWindow(t);i&&i.visible&&i.minimize(),Event.stop(e)},maximize:function(t,e){var i=this.getWindow(t);i&&i.visible&&i.maximize(),Event.stop(e)},close:function(t,e){var i=this.getWindow(t);i&&i.close(),e&&Event.stop(e)},blur:function(t){var e=this.getWindow(t);e&&(e.options.blurClassName&&e.changeClassName(e.options.blurClassName),this.focusedWindow==e&&(this.focusedWindow=null),e._notify("onBlur"))},focus:function(t){var e=this.getWindow(t);e&&(this.focusedWindow&&this.blur(this.focusedWindow.getId()),e.options.focusClassName&&e.changeClassName(e.options.focusClassName),this.focusedWindow=e,e._notify("onFocus"))},unsetOverflow:function(t){this.windows.each(function(t){t.oldOverflow=t.getContent().getStyle("overflow")||"auto",t.getContent().setStyle({overflow:"hidden"})}),t&&t.oldOverflow&&t.getContent().setStyle({overflow:t.oldOverflow})},resetOverflow:function(){this.windows.each(function(t){t.oldOverflow&&t.getContent().setStyle({overflow:t.oldOverflow})})},updateZindex:function(t,e){t>this.maxZIndex&&(this.maxZIndex=t,this.focusedWindow&&this.blur(this.focusedWindow.getId())),this.focusedWindow=e,this.focusedWindow&&this.focus(this.focusedWindow.getId())}},Dialog={dialogId:null,onCompleteFunc:null,callFunc:null,parameters:null,confirm:function(t,e){if(t&&"string"!=typeof t)return void Dialog._runAjaxRequest(t,e,Dialog.confirm);t=t||"",e=e||{};var i=e.okLabel?e.okLabel:"Ok",s=e.cancelLabel?e.cancelLabel:"Cancel";e=Object.extend(e,e.windowParameters||{}),e.windowParameters=e.windowParameters||{},e.className=e.className||"alert";var o="class ='"+(e.buttonClass?e.buttonClass+" ":"")+" ok_button'",n="class ='"+(e.buttonClass?e.buttonClass+" ":"")+" cancel_button'",t="      <div class='"+e.className+"_message'>"+t+"</div>        <div class='"+e.className+"_buttons'>          <button type='button' title='"+i+"' onclick='Dialog.okCallback()' "+o+"><span><span><span>"+i+"</span></span></span></button>          <button type='button' title='"+s+"' onclick='Dialog.cancelCallback()' "+n+"><span><span><span>"+s+"</span></span></span></button>        </div>    ";return this._openDialog(t,e)},alert:function(t,e){if(t&&"string"!=typeof t)return void Dialog._runAjaxRequest(t,e,Dialog.alert);t=t||"",e=e||{};var i=e.okLabel?e.okLabel:"Ok";e=Object.extend(e,e.windowParameters||{}),e.windowParameters=e.windowParameters||{},e.className=e.className||"alert";var s="class ='"+(e.buttonClass?e.buttonClass+" ":"")+" ok_button'",t="      <div class='"+e.className+"_message'>"+t+"</div>        <div class='"+e.className+"_buttons'>          <button type='button' title='"+i+"' onclick='Dialog.okCallback()' "+s+"><span><span><span>"+i+"</span></span></span></button>        </div>";
return this._openDialog(t,e)},info:function(t,e){if(t&&"string"!=typeof t)return void Dialog._runAjaxRequest(t,e,Dialog.info);t=t||"",e=e||{},e=Object.extend(e,e.windowParameters||{}),e.windowParameters=e.windowParameters||{},e.className=e.className||"alert";var t="<div id='modal_dialog_message' class='"+e.className+"_message'>"+t+"</div>";return e.showProgress&&(t+="<div id='modal_dialog_progress' class='"+e.className+"_progress'>  </div>"),e.ok=null,e.cancel=null,this._openDialog(t,e)},setInfoMessage:function(t){$("modal_dialog_message").update(t)},closeInfo:function(){Windows.close(this.dialogId)},_openDialog:function(t,e){var i=e.className;if(e.height||e.width||(e.width=WindowUtilities.getPageSize(e.options.parent||document.body).pageWidth/2),e.id)this.dialogId=e.id;else{var s=new Date;this.dialogId="modal_dialog_"+s.getTime(),e.id=this.dialogId}if(!e.height||!e.width){var o=WindowUtilities._computeSize(t,this.dialogId,e.width,e.height,5,i);e.height?e.width=o+5:e.height=o+5}e.effectOptions=e.effectOptions,e.resizable=e.resizable||!1,e.minimizable=e.minimizable||!1,e.maximizable=e.maximizable||!1,e.draggable=e.draggable||!1,e.closable=e.closable||!1;var n=new Window(e);return n.getContent().innerHTML=t,n.showCenter(!0,e.top,e.left),n.setDestroyOnClose(),n.cancelCallback=e.onCancel||e.cancel,n.okCallback=e.onOk||e.ok,n},_getAjaxContent:function(t){Dialog.callFunc(t.responseText,Dialog.parameters)},_runAjaxRequest:function(t,e,i){null==t.options&&(t.options={}),Dialog.onCompleteFunc=t.options.onComplete,Dialog.parameters=e,Dialog.callFunc=i,t.options.onComplete=Dialog._getAjaxContent,new Ajax.Request(t.url,t.options)},okCallback:function(){var t=Windows.focusedWindow;(!t.okCallback||t.okCallback(t))&&($$("#"+t.getId()+" input").each(function(t){t.onclick=null}),t.close())},cancelCallback:function(){var t=Windows.focusedWindow;$$("#"+t.getId()+" input").each(function(t){t.onclick=null}),t.close(),t.cancelCallback&&t.cancelCallback(t)}};if(Prototype.Browser.WebKit){var array=navigator.userAgent.match(new RegExp(/AppleWebKit\/([\d\.\+]*)/));Prototype.Browser.WebKitVersion=parseFloat(array[1])}var WindowUtilities={getWindowScroll:function(parent){var T,L,W,H;if(parent=parent||document.body,parent!=document.body)T=parent.scrollTop,L=parent.scrollLeft,W=parent.scrollWidth,H=parent.scrollHeight;else{var w=window;with(w.document)w.document.documentElement&&documentElement.scrollTop?(T=documentElement.scrollTop,L=documentElement.scrollLeft):w.document.body&&(T=body.scrollTop,L=body.scrollLeft),w.innerWidth?(W=w.innerWidth,H=w.innerHeight):w.document.documentElement&&documentElement.clientWidth?(W=documentElement.clientWidth,H=documentElement.clientHeight):(W=body.offsetWidth,H=body.offsetHeight)}return{top:T,left:L,width:W,height:H}},getPageSize:function(t){t=t||document.body;var e,i,s,o;if(t!=document.body)e=t.getWidth(),i=t.getHeight(),o=t.scrollWidth,s=t.scrollHeight;else{var n,h;window.innerHeight&&window.scrollMaxY?(n=document.body.scrollWidth,h=window.innerHeight+window.scrollMaxY):document.body.scrollHeight>document.body.offsetHeight?(n=document.body.scrollWidth,h=document.body.scrollHeight):(n=document.body.offsetWidth,h=document.body.offsetHeight),self.innerHeight?(e=document.documentElement.clientWidth,i=self.innerHeight):document.documentElement&&document.documentElement.clientHeight?(e=document.documentElement.clientWidth,i=document.documentElement.clientHeight):document.body&&(e=document.body.clientWidth,i=document.body.clientHeight),s=i>h?i:h,o=e>n?e:n}return{pageWidth:o,pageHeight:s,windowWidth:e,windowHeight:i}},disableScreen:function(t,e,i,s,o){WindowUtilities.initLightbox(e,t,function(){this._disableScreen(t,e,i,s)}.bind(this),o||document.body)},_disableScreen:function(t,e,i,s){var o=$(e),n=WindowUtilities.getPageSize(o.parentNode);s&&Prototype.Browser.IE&&(WindowUtilities._hideSelect(),WindowUtilities._showSelect(s)),o.style.height=n.pageHeight+"px",o.style.display="none","overlay_modal"==e&&Window.hasEffectLib&&Windows.overlayShowEffectOptions?(o.overlayOpacity=i,new Effect.Appear(o,Object.extend({from:0,to:i},Windows.overlayShowEffectOptions))):o.style.display="block"},enableScreen:function(t){t=t||"overlay_modal";var e=$(t);e&&("overlay_modal"==t&&Window.hasEffectLib&&Windows.overlayHideEffectOptions?new Effect.Fade(e,Object.extend({from:e.overlayOpacity,to:0},Windows.overlayHideEffectOptions)):(e.style.display="none",e.parentNode.removeChild(e)),"__invisible__"!=t&&WindowUtilities._showSelect())},_hideSelect:function(t){Prototype.Browser.IE&&(t=null==t?"":"#"+t+" ",$$(t+"select").each(function(t){WindowUtilities.isDefined(t.oldVisibility)||(t.oldVisibility=t.style.visibility?t.style.visibility:"visible",t.style.visibility="hidden")}))},_showSelect:function(t){Prototype.Browser.IE&&(t=null==t?"":"#"+t+" ",$$(t+"select").each(function(t){if(WindowUtilities.isDefined(t.oldVisibility)){try{t.style.visibility=t.oldVisibility}catch(e){t.style.visibility="visible"}t.oldVisibility=null}else t.style.visibility&&(t.style.visibility="visible")}))},isDefined:function(t){return"undefined"!=typeof t&&null!=t},initLightbox:function(t,e,i,s){if($(t))Element.setStyle(t,{zIndex:Windows.maxZIndex+1}),Windows.maxZIndex++,i();else{var o=document.createElement("div");o.setAttribute("id",t),o.className="overlay_"+e,o.style.display="none",o.style.position="absolute",o.style.top="0",o.style.left="0",o.style.zIndex=Windows.maxZIndex+1,Windows.maxZIndex++,o.style.width="100%",s.insertBefore(o,s.firstChild),Prototype.Browser.WebKit&&"overlay_modal"==t?setTimeout(function(){i()},10):i()}},setCookie:function(t,e){document.cookie=e[0]+"="+escape(t)+(e[1]?"; expires="+e[1].toGMTString():"")+(e[2]?"; path="+e[2]:"")+(e[3]?"; domain="+e[3]:"")+(e[4]?"; secure":"")},getCookie:function(t){var e=document.cookie,i=t+"=",s=e.indexOf("; "+i);if(-1==s){if(s=e.indexOf(i),0!=s)return null}else s+=2;var o=document.cookie.indexOf(";",s);return-1==o&&(o=e.length),unescape(e.substring(s+i.length,o))},_computeSize:function(t,e,i,s,o,n){var h=document.body,l=document.createElement("div");l.setAttribute("id",e),l.className=n+"_content",s?l.style.height=s+"px":l.style.width=i+"px",l.style.position="absolute",l.style.top="0",l.style.left="0",l.style.display="none",l.innerHTML=t,h.insertBefore(l,h.firstChild);var r;return r=s?$(l).getDimensions().width+o:$(l).getDimensions().height+o,h.removeChild(l),r}};