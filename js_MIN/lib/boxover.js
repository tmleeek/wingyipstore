function init(){oDv.appendChild(dvHdr),oDv.appendChild(dvBdy),oDv.style.position="absolute",oDv.style.visibility="hidden",document.body.appendChild(oDv)}function defHdrStyle(){dvHdr.innerHTML='<img  style="vertical-align:middle"  src="info.gif">&nbsp;&nbsp;'+dvHdr.innerHTML,dvHdr.style.fontWeight="bold",dvHdr.style.width="150px",dvHdr.style.fontFamily="arial",dvHdr.style.border="1px solid #A5CFE9",dvHdr.style.padding="3",dvHdr.style.fontSize="11",dvHdr.style.color="#4B7A98",dvHdr.style.background="#D5EBF9",dvHdr.style.filter="alpha(opacity=85)",dvHdr.style.opacity="0.85"}function defBdyStyle(){dvBdy.style.borderBottom="1px solid #A5CFE9",dvBdy.style.borderLeft="1px solid #A5CFE9",dvBdy.style.borderRight="1px solid #A5CFE9",dvBdy.style.width="150px",dvBdy.style.fontFamily="arial",dvBdy.style.fontSize="11",dvBdy.style.padding="3",dvBdy.style.color="#1B4966",dvBdy.style.background="#FFFFFF",dvBdy.style.filter="alpha(opacity=85)",dvBdy.style.opacity="0.85"}function checkElemBO(e){return e&&"string"==typeof e&&e.indexOf("header")>-1&&e.indexOf("body")>-1&&e.indexOf("[")>-1&&e.indexOf("[")>-1?!0:!1}function scanBO(e){checkElemBO(e.title)?(e.boHDR=getParam("header",e.title),e.boBDY=getParam("body",e.title),e.boCSSBDY=getParam("cssbody",e.title),e.boCSSHDR=getParam("cssheader",e.title),e.IEbugfix="on"==getParam("hideselects",e.title)?!0:!1,e.fixX=parseInt(getParam("fixedrelx",e.title)),e.fixY=parseInt(getParam("fixedrely",e.title)),e.absX=parseInt(getParam("fixedabsx",e.title)),e.absY=parseInt(getParam("fixedabsy",e.title)),e.offY=""!=getParam("offsety",e.title)?parseInt(getParam("offsety",e.title)):10,e.offX=""!=getParam("offsetx",e.title)?parseInt(getParam("offsetx",e.title)):10,e.fade="on"==getParam("fade",e.title)?!0:!1,e.fadespeed=""!=getParam("fadespeed",e.title)?getParam("fadespeed",e.title):.04,e.delay=""!=getParam("delay",e.title)?parseInt(getParam("delay",e.title)):0,"on"==getParam("requireclick",e.title)?(e.requireclick=!0,document.all?e.attachEvent("onclick",showHideBox):e.addEventListener("click",showHideBox,!1),document.all?e.attachEvent("onmouseover",hideBox):e.addEventListener("mouseover",hideBox,!1)):("off"!=getParam("doubleclickstop",e.title)&&(document.all?e.attachEvent("ondblclick",pauseBox):e.addEventListener("dblclick",pauseBox,!1)),"on"==getParam("singleclickstop",e.title)&&(document.all?e.attachEvent("onclick",pauseBox):e.addEventListener("click",pauseBox,!1))),e.windowLock="off"==getParam("windowlock",e.title).toLowerCase()?!1:!0,e.title="",e.hasbox=1):e.hasbox=2}function getParam(e,t){var o=new RegExp("([^a-zA-Z]"+e+"|^"+e+")\\s*=\\s*\\[\\s*(((\\[\\[)|(\\]\\])|([^\\]\\[]))*)\\s*\\]"),l=o.exec(t);return l?l[2].replace("[[","[").replace("]]","]"):""}function Left(e){var t=0;if(e.calcLeft)return e.calcLeft;for(var o=e;e;)e.currentStyle&&!isNaN(parseInt(e.currentStyle.borderLeftWidth))&&0!=t&&(t+=parseInt(e.currentStyle.borderLeftWidth)),t+=e.offsetLeft,e=e.offsetParent;return o.calcLeft=t,t}function Top(e){var t=0;if(e.calcTop)return e.calcTop;for(var o=e;e;)e.currentStyle&&!isNaN(parseInt(e.currentStyle.borderTopWidth))&&0!=t&&(t+=parseInt(e.currentStyle.borderTopWidth)),t+=e.offsetTop,e=e.offsetParent;return o.calcTop=t,t}function applyStyles(){ab&&oDv.removeChild(dvBdy),ah&&oDv.removeChild(dvHdr),dvHdr=document.createElement("div"),dvBdy=document.createElement("div"),CBE.boCSSBDY?dvBdy.className=CBE.boCSSBDY:defBdyStyle(),CBE.boCSSHDR?dvHdr.className=CBE.boCSSHDR:defHdrStyle(),dvHdr.innerHTML=CBE.boHDR,dvBdy.innerHTML=CBE.boBDY,ah=!1,ab=!1,""!=CBE.boHDR&&(oDv.appendChild(dvHdr),ah=!0),""!=CBE.boBDY&&(oDv.appendChild(dvBdy),ab=!0)}function SHW(){return document.body&&0!=document.body.clientWidth&&(width=document.body.clientWidth,height=document.body.clientHeight),document.documentElement&&0!=document.documentElement.clientWidth&&document.body.clientWidth+20>=document.documentElement.clientWidth&&(width=document.documentElement.clientWidth,height=document.documentElement.clientHeight),[width,height]}function moveMouse(e){if(evt=e?e:event,CSE=evt.target?evt.target:evt.srcElement,!CSE.hasbox)for(iElem=CSE;iElem.parentNode&&!iElem.hasbox;)scanBO(iElem),iElem=iElem.parentNode;if(CSE==LSE||isChild(CSE,dvHdr)||isChild(CSE,dvBdy)){if((isChild(CSE,dvHdr)||isChild(CSE,dvBdy))&&boxMove){for(totalScrollLeft=0,totalScrollTop=0,iterElem=CSE;iterElem;)isNaN(parseInt(iterElem.scrollTop))||(totalScrollTop+=parseInt(iterElem.scrollTop)),isNaN(parseInt(iterElem.scrollLeft))||(totalScrollLeft+=parseInt(iterElem.scrollLeft)),iterElem=iterElem.parentNode;null!=CBE&&(boxLeft=Left(CBE)-totalScrollLeft,boxRight=parseInt(Left(CBE)+CBE.offsetWidth)-totalScrollLeft,boxTop=Top(CBE)-totalScrollTop,boxBottom=parseInt(Top(CBE)+CBE.offsetHeight)-totalScrollTop,doCheck())}}else{if(!CSE.boxItem){for(iterElem=CSE;2==iterElem.hasbox&&iterElem.parentNode;)iterElem=iterElem.parentNode;CSE.boxItem=iterElem}iterElem=CSE.boxItem,CSE.boxItem&&1==CSE.boxItem.hasbox?(LBE=CBE,CBE=iterElem,CBE!=LBE&&(applyStyles(),CBE.requireclick||(CBE.fade?(null!=ID&&clearTimeout(ID),ID=setTimeout("fadeIn("+CBE.fadespeed+")",CBE.delay)):(null!=ID&&clearTimeout(ID),COL=1,ID=setTimeout("oDv.style.visibility='visible';ID=null;",CBE.delay))),CBE.IEbugfix&&hideSelects(),fixposx=isNaN(CBE.fixX)?CBE.absX:Left(CBE)+CBE.fixX,fixposy=isNaN(CBE.fixY)?CBE.absY:Top(CBE)+CBE.fixY,lockX=0,lockY=0,boxMove=!0,ox=CBE.offX?CBE.offX:10,oy=CBE.offY?CBE.offY:10)):isChild(CSE,dvHdr)||isChild(CSE,dvBdy)||!boxMove||isChild(CBE,CSE)&&"TABLE"==CSE.tagName||(CBE=null,null!=ID&&clearTimeout(ID),fadeOut(),showSelects()),LSE=CSE}boxMove&&CBE&&(bodyScrollTop=document.documentElement&&document.documentElement.scrollTop?document.documentElement.scrollTop:document.body.scrollTop,bodyScrollLet=document.documentElement&&document.documentElement.scrollLeft?document.documentElement.scrollLeft:document.body.scrollLeft,mouseX=evt.pageX?evt.pageX-bodyScrollLet:evt.clientX-document.body.clientLeft,mouseY=evt.pageY?evt.pageY-bodyScrollTop:evt.clientY-document.body.clientTop,CBE&&CBE.windowLock&&(lockY=-oy>mouseY?-mouseY-oy:0,lockX=-ox>mouseX?-mouseX-ox:0,lockY=mouseY>SHW()[1]-oDv.offsetHeight-oy?-mouseY+SHW()[1]-oDv.offsetHeight-oy:lockY,lockX=mouseX>SHW()[0]-dvBdy.offsetWidth-ox?-mouseX-ox+SHW()[0]-dvBdy.offsetWidth:lockX),oDv.style.left=fixposx||0==fixposx?fixposx:bodyScrollLet+mouseX+ox+lockX+"px",oDv.style.top=fixposy||0==fixposy?fixposy:bodyScrollTop+mouseY+oy+lockY+"px")}function doCheck(){(boxLeft>mouseX||mouseX>boxRight||boxTop>mouseY||mouseY>boxBottom)&&(CBE.requireclick||fadeOut(),CBE.IEbugfix&&showSelects(),CBE=null)}function pauseBox(e){evt=e?e:event,boxMove=!1,evt.cancelBubble=!0}function showHideBox(){oDv.style.visibility="visible"!=oDv.style.visibility?"visible":"hidden"}function hideBox(){oDv.style.visibility="hidden"}function fadeIn(e){ID=null,COL=0,oDv.style.visibility="visible",fadeIn2(e)}function fadeIn2(e){COL+=e,COL=COL>1?1:COL,oDv.style.filter="alpha(opacity="+parseInt(100*COL)+")",oDv.style.opacity=COL,1>COL&&setTimeout("fadeIn2("+e+")",20)}function fadeOut(){oDv.style.visibility="hidden"}function isChild(e,t){for(;e;){if(e==t)return!0;e=e.parentNode}return!1}function checkMove(e){evt=e?e:event,cSrc=evt.target?evt.target:evt.srcElement,boxMove||isChild(cSrc,oDv)||(fadeOut(),CBE&&CBE.IEbugfix&&showSelects(),boxMove=!0,CBE=null)}function showSelects(){var e=document.getElementsByTagName("select");for(i=0;i<e.length;i++)e[i].style.visibility="visible"}function hideSelects(){var e=document.getElementsByTagName("select");for(i=0;i<e.length;i++)e[i].style.visibility="hidden"}"undefined"!=typeof document.attachEvent?(window.attachEvent("onload",init),document.attachEvent("onmousemove",moveMouse),document.attachEvent("onclick",checkMove)):(window.addEventListener("load",init,!1),document.addEventListener("mousemove",moveMouse,!1),document.addEventListener("click",checkMove,!1));var oDv=document.createElement("div"),dvHdr=document.createElement("div"),dvBdy=document.createElement("div"),windowlock,boxMove,fixposx,fixposy,lockX,lockY,fixx,fixy,ox,oy,boxLeft,boxRight,boxTop,boxBottom,evt,mouseX,mouseY,boxOpen,totalScrollTop,totalScrollLeft;boxOpen=!1,ox=10,oy=10,lockX=0,lockY=0;var ah,ab,CSE,iterElem,LSE,CBE,LBE,totalScrollLeft,totalScrollTop,width,height,ini=!1,ID=null,COL=0,stopfade=!1,cSrc;