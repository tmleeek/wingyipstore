!function(e){e(window).ready(function(){function s(){e(document).width()>991?(e("#ves-mainnav").css("position","relative"),e(".header-container").css("margin-bottom",0)):parseInt(e(document).scrollTop())>=r?(e("#ves-mainnav").css("position","fixed"),e("#ves-mainnav").css("width","100%"),e("#ves-mainnav").css("top",0),e("#ves-mainnav").css("left",0),e("#ves-mainnav").css("z-index",100),e(".header-container").css("margin-bottom",d)):(e("#ves-mainnav").css("position","relative"),e(".header-container").css("margin-bottom",0))}function a(){var s=e("#verticalmenu .verticalmenu").html();if(s){s=s.replace('<b class="caret"></b>',""),s=s.replace('class="dropdown-toggle" data-toggle="dropdown"',"");{e("#verticalmenu .mega-col-inner").html()}e(".categories-home").html("<ul>"+s+"</ul>"),e(".ch .dropdown-menu").remove(),e(".ch .parent").removeClass("parent"),e(".ch .dropdown").removeClass("dropdown")}}if(e("body").hasClass("layout-boxed-md")||e("body").hasClass("layout-boxed-lg"));else if(e("body").hasClass("keep-header")){var o=parseInt(e("#header-main").css("margin-bottom")),n=e("#topbar").height()+o+o,i=e("#header").height()+o,c=function(){var s=e(window).scrollTop();s>=n?(e("#page").css("padding-top",i),e("#header").addClass("hide-bar"),e("#header").addClass("navbar navbar-fixed-top")):e("#header").removeClass("hide-bar")};e(window).scroll(function(){c()})}var t=e("#ves-mainnav").offset(),r=t.top,d=e("#ves-mainnav").height();e(document).scroll(function(){s()}),e(document).resize(function(){s(),a()});var l=!1;e(".quick-access").on("click",function(){e(this).hasClass("quick-access-show-scrolled")&&(l=!0,e(this).removeClass("quick-access-show-scrolled")),0==l?(e(this).addClass("quick-access-show"),l=!0):(e(this).removeClass("quick-access-show"),l=!1)}),a()})}(jQuery);