var persistantGridControl=Class.create();persistantGridControl.prototype={initialize:function(t,e,i,n){this.grid=t,this.grid.persistantObject=this,this.targetInputId=e,this.mainFieldPrefix=i,this.changes=new Array,this.restoreValueCallBack=n,t.initCallback=this.initGrid,this.initGrid(this.grid)},logChange:function(t){var e=document.getElementById(t).value;for(i=0;i<this.changes.length;i++)if(this.changes[i][0]==t)return this.changes[i][1]=e,!0;return this.changes[this.changes.length]=new Array(t,e),!0},forceChange:function(t,e){var i=e;this.changes[this.changes.length]=new Array(t,i)},restoreChanges:function(){for(i=0;i<this.changes.length;i++)document.getElementById(this.changes[i][0])&&(document.getElementById(this.changes[i][0]).value=this.changes[i][1])},storeLogInTargetInput:function(){document.getElementById(this.targetInputId).value="";for(var t=0;t<this.changes.length;t++){var e=this.changes[t][0],i=this.changes[t][1];document.getElementById(this.targetInputId).value+=e+"="+i+";"}},initGrid:function(t){var e=t.persistantObject;e.restoreChanges();for(var i,n=e.getIds(),a=0;a<n.length;a++)i=n[a],e.restoreValueCallBack&&e.restoreValueCallBack(i)},getIds:function(){var t=new Array,e=document.getElementsByTagName("input");for(i=0;i<e.length;i++)if(e[i]&&null!=e[i].id&&-1!=e[i].id.indexOf(this.mainFieldPrefix)){var n=e[i].id.replace(this.mainFieldPrefix,"");t[t.length]=n}return t}};