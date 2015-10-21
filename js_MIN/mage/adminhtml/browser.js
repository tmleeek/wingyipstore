MediabrowserUtility={openDialog:function(e,t,n,i,s){return $("browser_window")&&"undefined"!=typeof Windows?void Windows.focus("browser_window"):(this.dialogWindow=Dialog.info(null,Object.extend({closable:!0,resizable:!1,draggable:!0,className:"magento",windowClassName:"popup-window",title:i||"Insert File...",top:50,width:t||950,height:n||600,zIndex:s&&s.zIndex||1e3,recenterAuto:!1,hideEffect:Element.hide,showEffect:Element.show,id:"browser_window",onClose:this.closeDialog.bind(this)},s||{})),void new Ajax.Updater("modal_dialog_message",e,{evalScripts:!0}))},closeDialog:function(e){e||(e=this.dialogWindow),e&&(WindowUtilities._showSelect(),e.close())}},Mediabrowser=Class.create(),Mediabrowser.prototype={targetElementId:null,contentsUrl:null,onInsertUrl:null,newFolderUrl:null,deleteFolderUrl:null,deleteFilesUrl:null,headerText:null,tree:null,currentNode:null,storeId:null,initialize:function(e){this.newFolderPrompt=e.newFolderPrompt,this.deleteFolderConfirmationMessage=e.deleteFolderConfirmationMessage,this.deleteFileConfirmationMessage=e.deleteFileConfirmationMessage,this.targetElementId=e.targetElementId,this.contentsUrl=e.contentsUrl,this.onInsertUrl=e.onInsertUrl,this.newFolderUrl=e.newFolderUrl,this.deleteFolderUrl=e.deleteFolderUrl,this.deleteFilesUrl=e.deleteFilesUrl,this.headerText=e.headerText},setTree:function(e){this.tree=e,this.currentNode=e.getRootNode()},getTree:function(){return this.tree},selectFolder:function(e){this.currentNode=e,this.hideFileButtons(),this.activateBlock("contents"),"root"==e.id?this.hideElement("button_delete_folder"):this.showElement("button_delete_folder"),this.updateHeader(this.currentNode),this.drawBreadcrumbs(this.currentNode),this.showElement("loading-mask"),new Ajax.Request(this.contentsUrl,{parameters:{node:this.currentNode.id},evalJS:!0,onSuccess:function(e){try{this.currentNode.select(),this.onAjaxSuccess(e),this.hideElement("loading-mask"),void 0!=$("contents")&&($("contents").update(e.responseText),$$("div.filecnt").each(function(e){Event.observe(e.id,"click",this.selectFile.bind(this)),Event.observe(e.id,"dblclick",this.insert.bind(this))}.bind(this)))}catch(t){alert(t.message)}}.bind(this)})},selectFolderById:function(e){var t=this.tree.getNodeById(e);t.id&&this.selectFolder(t)},selectFile:function(e){var t=Event.findElement(e,"DIV");$$('div.filecnt.selected[id!="'+t.id+'"]').each(function(e){e.removeClassName("selected")}),t.toggleClassName("selected"),t.hasClassName("selected")?this.showFileButtons():this.hideFileButtons()},showFileButtons:function(){this.showElement("button_delete_files"),this.showElement("button_insert_files")},hideFileButtons:function(){this.hideElement("button_delete_files"),this.hideElement("button_insert_files")},handleUploadComplete:function(){$$('div[class*="file-row complete"]').each(function(e){$(e.id).remove()}),this.selectFolder(this.currentNode)},insert:function(e){var t;if(void 0!=e?t=Event.findElement(e,"DIV"):$$("div.selected").each(function(e){t=$(e.id)}),void 0==$(t.id))return!1;var n=this.getTargetElement();if(!n)return alert("Target element not found for content update"),void Windows.close("browser_window");var i={filename:t.id,node:this.currentNode.id,store:this.storeId};"textarea"==n.tagName.toLowerCase()&&(i.as_is=1),new Ajax.Request(this.onInsertUrl,{parameters:i,onSuccess:function(e){try{this.onAjaxSuccess(e),this.getMediaBrowserOpener()&&self.blur(),Windows.close("browser_window"),"input"==n.tagName.toLowerCase()?n.value=e.responseText:(updateElementAtCursor(n,e.responseText),varienGlobalEvents&&varienGlobalEvents.fireEvent("tinymceChange"))}catch(t){alert(t.message)}}.bind(this)})},getTargetElement:function(){if("undefined"!=typeof tinyMCE&&tinyMCE.get(this.targetElementId)){if(opener=this.getMediaBrowserOpener()){var e=tinyMceEditors.get(this.targetElementId).getMediaBrowserTargetElementId();return opener.document.getElementById(e)}return null}return document.getElementById(this.targetElementId)},getMediaBrowserOpener:function(){return"undefined"!=typeof tinyMCE&&tinyMCE.get(this.targetElementId)&&"undefined"!=typeof tinyMceEditors&&!tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener().closed?tinyMceEditors.get(this.targetElementId).getMediaBrowserOpener():null},newFolder:function(){var e=prompt(this.newFolderPrompt);return e?void new Ajax.Request(this.newFolderUrl,{parameters:{name:e},onSuccess:function(e){try{if(this.onAjaxSuccess(e),e.responseText.isJSON()){var t=e.responseText.evalJSON(),n=new Ext.tree.AsyncTreeNode({text:t.short_name,draggable:!1,id:t.id,expanded:!0}),i=this.currentNode.appendChild(n);this.tree.expandPath(i.getPath(),"",function(e,t){this.selectFolder(t)}.bind(this))}}catch(s){alert(s.message)}}.bind(this)}):!1},deleteFolder:function(){return confirm(this.deleteFolderConfirmationMessage)?void new Ajax.Request(this.deleteFolderUrl,{onSuccess:function(e){try{this.onAjaxSuccess(e);var t=this.currentNode.parentNode;t.removeChild(this.currentNode),this.selectFolder(t)}catch(n){alert(n.message)}}.bind(this)}):!1},deleteFiles:function(){if(!confirm(this.deleteFileConfirmationMessage))return!1;var e=[],t=0;$$("div.selected").each(function(n){e[t]=n.id,t++}),new Ajax.Request(this.deleteFilesUrl,{parameters:{files:Object.toJSON(e)},onSuccess:function(e){try{this.onAjaxSuccess(e),this.selectFolder(this.currentNode)}catch(t){alert(t.message)}}.bind(this)})},drawBreadcrumbs:function(e){if(void 0!=$("breadcrumbs")&&$("breadcrumbs").remove(),"root"!=e.id){for(var t=e.getPath().split("/"),n="",i=0,s=t.length;s>i;i++)if(""!=t[i]){var r=this.tree.getNodeById(t[i]);r.id&&(n+="<li>",n+='<a href="#" onclick="MediabrowserInstance.selectFolderById(\''+r.id+"');\">"+r.text+"</a>",s-1>i&&(n+=" <span>/</span>"),n+="</li>")}""!=n&&(n='<ul class="breadcrumbs" id="breadcrumbs">'+n+"</ul>",$("content_header").insert({after:n}))}},updateHeader:function(e){var t="root"==e.id?this.headerText:e.text;void 0!=$("content_header_text")&&($("content_header_text").innerHTML=t)},activateBlock:function(e){this.showElement(e)},hideElement:function(e){void 0!=$(e)&&($(e).addClassName("no-display"),$(e).hide())},showElement:function(e){void 0!=$(e)&&($(e).removeClassName("no-display"),$(e).show())},onAjaxSuccess:function(e){if(e.responseText.isJSON()){var t=e.responseText.evalJSON();if(t.error)throw t;t.ajaxExpired&&t.ajaxRedirect&&setLocation(t.ajaxRedirect)}}};