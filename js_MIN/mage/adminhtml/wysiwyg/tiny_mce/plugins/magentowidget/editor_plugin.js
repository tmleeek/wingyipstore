tinyMCE.addI18n({en:{magentowidget:{insert_widget:"Insert Widget"}}}),function(){tinymce.create("tinymce.plugins.MagentowidgetPlugin",{init:function(e,t){e.addCommand("mceMagentowidget",function(){widgetTools.openDialog(e.settings.magentowidget_url+"widget_target_id/"+e.getElement().id+"/")}),e.addButton("magentowidget",{title:"magentowidget.insert_widget",cmd:"mceMagentowidget",image:t+"/img/icon.gif"}),e.onNodeChange.add(function(e,t,n){if(t.setActive("magentowidget",!1),n.id&&"IMG"==n.nodeName){var i=Base64.idDecode(n.id);-1!=i.indexOf("{{widget")&&t.setActive("magentowidget",!0)}}),e.onDblClick.add(function(e,t){var n=t.target;if(n.id&&"IMG"==n.nodeName){var i=Base64.idDecode(n.id);-1!=i.indexOf("{{widget")&&e.execCommand("mceMagentowidget")}})},getInfo:function(){return{longname:"Magento Widget Manager Plugin for TinyMCE 3.x",author:"Magento Core Team",authorurl:"http://magentocommerce.com",infourl:"http://magentocommerce.com",version:"1.0"}}}),tinymce.PluginManager.add("magentowidget",tinymce.plugins.MagentowidgetPlugin)}();