var paymentForm=Class.create();paymentForm.prototype={initialize:function(e){this.formId=e,this.validator=new Validation(this.formId);for(var t=Form.getElements(e),a=null,r=0;r<t.length;r++)"payment[method]"==t[r].name?t[r].checked&&(a=t[r].value):t[r].type&&"submit"!=t[r].type.toLowerCase()&&(t[r].disabled=!0),t[r].setAttribute("autocomplete","off");a&&this.switchMethod(a)},switchMethod:function(e){if(this.currentMethod&&$("payment_form_"+this.currentMethod)){var t=$("payment_form_"+this.currentMethod);t.style.display="none";for(var a=t.getElementsByTagName("input"),r=0;r<a.length;r++)a[r].disabled=!0;for(var a=t.getElementsByTagName("select"),r=0;r<a.length;r++)a[r].disabled=!0}if($("payment_form_"+e)){var t=$("payment_form_"+e);t.style.display="";for(var a=t.getElementsByTagName("input"),r=0;r<a.length;r++)a[r].disabled=!1;for(var a=t.getElementsByTagName("select"),r=0;r<a.length;r++)a[r].disabled=!1;this.currentMethod=e}}};