function showPopup(o){oPopup=new Window({id:"popup_window",className:"magento",url:o,width:945,height:450,minimizable:!1,maximizable:!1,showEffectOptions:{duration:.4},hideEffectOptions:{duration:.4},destroyOnClose:!0}),oPopup.setZIndex(100),oPopup.showCenter(!0)}function closePopup(){Windows.close("popup_window"),Windows.destroy("popup_window")}