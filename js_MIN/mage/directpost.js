var directPost=Class.create();directPost.prototype={initialize:function(e,t,r,s,i,a){this.iframeId=t,this.controller=r,this.orderSaveUrl=s,this.nativeAction=a,this.cgiUrl=i,this.code=e,this.inputs=["cc_type","cc_number","expiration","expiration_yr","cc_cid"],this.headers=[],this.isValid=!0,this.paymentRequestSent=!1,this.isResponse=!1,this.orderIncrementId=!1,this.successUrl=!1,this.hasError=!1,this.tmpForm=!1,this.onSaveOnepageOrderSuccess=this.saveOnepageOrderSuccess.bindAsEventListener(this),this.onLoadIframe=this.loadIframe.bindAsEventListener(this),this.onLoadOrderIframe=this.loadOrderIframe.bindAsEventListener(this),this.onSubmitAdminOrder=this.submitAdminOrder.bindAsEventListener(this),this.preparePayment()},validate:function(){return this.isValid=!0,this.inputs.each(function(e){$(this.code+"_"+e)&&(Validation.validate($(this.code+"_"+e))||(this.isValid=!1))},this),this.isValid},changeInputOptions:function(e,t){this.inputs.each(function(r){$(this.code+"_"+r)&&$(this.code+"_"+r).writeAttribute(e,t)},this)},preparePayment:function(){if(this.changeInputOptions("autocomplete","off"),$(this.iframeId)){switch(this.controller){case"onepage":this.headers=$$("#"+checkout.accordion.container.readAttribute("id")+" .section");var e=$("review-buttons-container").down("button");e.writeAttribute("onclick",""),e.stopObserving("click"),e.observe("click",function(){$(this.iframeId)?this.validate()&&this.saveOnepageOrder():review.save()}.bind(this));break;case"sales_order_create":case"sales_order_edit":for(var t=document.getElementsByClassName("scalable save"),r=0;r<t.length;r++)t[r].writeAttribute("onclick",""),t[r].observe("click",this.onSubmitAdminOrder);$("order-"+this.iframeId).observe("load",this.onLoadOrderIframe)}$(this.iframeId).observe("load",this.onLoadIframe)}},loadIframe:function(){if(this.paymentRequestSent){switch(this.controller){case"onepage":this.paymentRequestSent=!1,this.hasError||this.returnQuote();break;case"sales_order_edit":case"sales_order_create":this.orderRequestSent||(this.paymentRequestSent=!1,this.hasError?(this.changeInputOptions("disabled",!1),toggleSelectsUnderBlock($("loading-mask"),!0),$("loading-mask").hide(),enableElements("save")):this.returnQuote())}this.tmpForm&&document.body.removeChild(this.tmpForm)}},loadOrderIframe:function(){if(this.orderRequestSent){$(this.iframeId).hide();var e=$("order-"+this.iframeId).contentWindow.document.body.innerHTML;this.saveAdminOrderSuccess(e),this.orderRequestSent=!1}},showError:function(e){this.hasError=!0,"onepage"==this.controller&&($(this.iframeId).hide(),this.resetLoadWaiting()),alert(e)},returnQuote:function(){var url=this.orderSaveUrl.replace("place","returnQuote");new Ajax.Request(url,{onSuccess:function(transport){try{response=eval("("+transport.responseText+")")}catch(e){response={}}switch(response.error_message&&alert(response.error_message),$(this.iframeId).show(),this.controller){case"onepage":this.resetLoadWaiting();break;case"sales_order_edit":case"sales_order_create":this.changeInputOptions("disabled",!1),toggleSelectsUnderBlock($("loading-mask"),!0),$("loading-mask").hide(),enableElements("save")}}.bind(this)})},setLoadWaiting:function(){this.headers.each(function(e){e.removeClassName("allow")}),checkout.setLoadWaiting("review")},resetLoadWaiting:function(){this.headers.each(function(e){e.addClassName("allow")}),checkout.setLoadWaiting(!1)},saveOnepageOrder:function(){this.hasError=!1,this.setLoadWaiting();var e=Form.serialize(payment.form);review.agreementsForm&&(e+="&"+Form.serialize(review.agreementsForm)),e+="&controller="+this.controller,new Ajax.Request(this.orderSaveUrl,{method:"post",parameters:e,onComplete:this.onSaveOnepageOrderSuccess,onFailure:function(e){this.resetLoadWaiting(),403==e.status&&checkout.ajaxFailure()}})},saveOnepageOrderSuccess:function(transport){403==transport.status&&checkout.ajaxFailure();try{response=eval("("+transport.responseText+")")}catch(e){response={}}if(response.success&&response.directpost){this.orderIncrementId=response.directpost.fields.x_invoice_num;var paymentData={};for(var key in response.directpost.fields)paymentData[key]=response.directpost.fields[key];var preparedData=this.preparePaymentRequest(paymentData);this.sendPaymentRequest(preparedData)}else{var msg=response.error_messages;"object"==typeof msg&&(msg=msg.join("\n")),msg&&alert(msg),response.update_section&&($("checkout-"+response.update_section.name+"-load").update(response.update_section.html),response.update_section.html.evalScripts()),response.goto_section&&(checkout.gotoSection(response.goto_section),checkout.reloadProgressBlock())}},submitAdminOrder:function(){if(editForm.validate()){var e=$(editForm.formId).getInputs("radio","payment[method]").find(function(e){return e.checked});this.hasError=!1,e.value==this.code?(toggleSelectsUnderBlock($("loading-mask"),!1),$("loading-mask").show(),setLoaderPosition(),this.changeInputOptions("disabled","disabled"),this.paymentRequestSent=!0,this.orderRequestSent=!0,$(editForm.formId).writeAttribute("action",this.orderSaveUrl),$(editForm.formId).writeAttribute("target",$("order-"+this.iframeId).readAttribute("name")),$(editForm.formId).appendChild(this.createHiddenElement("controller",this.controller)),disableElements("save"),$(editForm.formId).submit()):($(editForm.formId).writeAttribute("action",this.nativeAction),$(editForm.formId).writeAttribute("target","_top"),disableElements("save"),$(editForm.formId).submit())}},recollectQuote:function(){var e=["sidebar","items","shipping_method","billing_method","totals","giftmessage"];e=order.prepareArea(e);for(var t=order.loadBaseUrl+"block/"+e,r=$("order-items_grid").select("input","select","textarea"),s={},i=0;i<r.length;i++)r[i].disabled||"checkbox"==r[i].type&&!r[i].checked||(s[r[i].name]=r[i].getValue());s.reset_shipping=!0,s.update_items=!0,$("coupons:code")&&$F("coupons:code")&&(s["order[coupon][code]"]=$F("coupons:code")),s.json=!0,new Ajax.Request(t,{parameters:s,loaderArea:"html-body",onSuccess:function(){$(editForm.formId).submit()}.bind(this)})},saveAdminOrderSuccess:function(data){try{response=eval("("+data+")")}catch(e){response={}}if(response.directpost){this.orderIncrementId=response.directpost.fields.x_invoice_num;var paymentData={};for(var key in response.directpost.fields)paymentData[key]=response.directpost.fields[key];var preparedData=this.preparePaymentRequest(paymentData);this.sendPaymentRequest(preparedData)}else if(response.redirect&&(window.location=response.redirect),response.error_messages){var msg=response.error_messages;"object"==typeof msg&&(msg=msg.join("\n")),msg&&alert(msg)}},preparePaymentRequest:function(e){$(this.code+"_cc_cid")&&(e.x_card_code=$(this.code+"_cc_cid").value);var t=$(this.code+"_expiration_yr").value;t.length>2&&(t=t.substring(2));var r=parseInt($(this.code+"_expiration").value,10);return 10>r&&(r="0"+r),e.x_exp_date=r+"/"+t,e.x_card_num=$(this.code+"_cc_number").value,e},sendPaymentRequest:function(e){this.recreateIframe(),this.tmpForm=document.createElement("form"),this.tmpForm.style.display="none",this.tmpForm.enctype="application/x-www-form-urlencoded",this.tmpForm.method="POST",document.body.appendChild(this.tmpForm),this.tmpForm.action=this.cgiUrl,this.tmpForm.target=$(this.iframeId).readAttribute("name"),this.tmpForm.setAttribute("target",$(this.iframeId).readAttribute("name"));for(var t in e)this.tmpForm.appendChild(this.createHiddenElement(t,e[t]));this.paymentRequestSent=!0,this.tmpForm.submit()},createHiddenElement:function(e,t){var r;return isIE?(r=document.createElement("input"),r.setAttribute("type","hidden"),r.setAttribute("name",e),r.setAttribute("value",t)):(r=document.createElement("input"),r.type="hidden",r.name=e,r.value=t),r},recreateIframe:function(){if($(this.iframeId)){var e=$(this.iframeId).next(),t=$(this.iframeId).readAttribute("src"),r=$(this.iframeId).readAttribute("name");$(this.iframeId).stopObserving(),$(this.iframeId).remove();var s='<iframe id="'+this.iframeId+'" allowtransparency="true" frameborder="0"  name="'+r+'" style="display:none;width:100%;background-color:transparent" src="'+t+'" />';Element.insert(e,{before:s}),$(this.iframeId).observe("load",this.onLoadIframe)}}};