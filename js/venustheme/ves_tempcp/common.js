function click_delete_item() {
    return confirm(text_confirm_delete_item)
}

function getAjaxCart() {
    jQuery('#cboxClose').click();
    jQuery.ajax({
        url: minicart_url,
        dataType: "json",
        type: "get",
        success: function(t) {
            var a = jQuery("<div>");
            if (jQuery(a).html(t.html), jQuery("#cart-total").length > 0) {
                var e = text_cart_total.replace("%total%", t.summary_qty);
                e = e.replace("%price%", t.subtotal), jQuery("#cart-total").html(e)
            }
            jQuery(a).find(".block-cart").length > 0 && jQuery(".block-cart").html(jQuery(a).find(".block-cart").first().html()), jQuery(".block-cart .btn-remove").length > 0 && (jQuery(".block-cart .btn-remove").unbind("click"), jQuery(".block-cart .btn-remove").attr("onclick", ""), jQuery(".block-cart .btn-remove").off("click"), jQuery(".block-cart .btn-remove").on("click", function(t) {
                return t.preventDefault(), click_delete_item() && deleteItemCart(jQuery(this).attr("href")), !1
            }));
            jQuery('.inner-toggle .cart-top #cart .quick-access').addClass('quick-access-show');

        }

    })
}

function showMiniCart() {
    getAjaxCart(), jQuery("#cart").addClass("active"), setTimeout(function() {
        jQuery("#cart").removeClass("active")
    }, 5e3)
}

function deleteItemCart(t) {
    t += "&isAjax=1", jQuery.ajax({
        url: t,
        dataType: "json",
        type: "post",
        data: "isAjax=1",
        success: function() {
            showMiniCart()
        }
    })
}

function addToCart(t) {
    if (isQuote) {
        var a = confirm("By clicking OK below your current shopping basket will be emptied and replaced with the current item. If you do not wish for this to happen, click CANCEL.");
        if (0 == a) return !1
    }
    quantity = "undefined" != typeof quantity ? quantity : 1, t.match(/checkout\/cart/) ? jQuery.ajax({
        url: t,
        dataType: "json",
        type: "post",
        data: "isAjax=1",
        success: function(a) {
            jQuery(".success, .warning, .attention, .information, .error").remove(), "SUCCESS" == a.status ? (jQuery("#notification").html('<div class="success" style="display: none;">' + a.message + '<a class="close btn-remove" href="javascript:;" onclick="jQuery(\'.success, .warning, .attention, .information, .error\').remove()">X</a></div>'), jQuery(".success").fadeIn("slow").delay(5e3).hide(0), jQuery("#cart > .heading a").click(), jQuery("html, body").animate({
                scrollTop: 0
            }, "slow")) : setLocation(t)
        }
    }) : setLocation(t)
}
var text_confirm_delete_item = "",
    text_cart_total = "%total% item(s) - %price%",
    text_waiting = "Adding....";
! function(t) {
    ! function() {
        function a() {
            var t = !1;
            return function(a) {
                (/(android|ipad|playbook|silk|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4))) && (t = !0)
            }(navigator.userAgent || navigator.vendor || window.opera), t
        }

        function e() {
            t(document).ready(function() {
                var e = t("#mainmenutop .navbar .navbar-nav"),
                    n = 3,
                    o = t('<nav id="menu-offcanvas" class="offcanvas-menu offcanvas-effect-' + n + ' hidden-lg hidden-md"><div class="menu-offcanvas-inner"></div></nav>');
                t(".menu-offcanvas-inner", o).append(e.clone()), t("body").append(o), t(".navbar-nav", o).removeClass("navbar-nav").removeClass("nav").addClass("menu-offcanvas-content"), t(".menu-offcanvas-inner").append("<div class='button-close-menu'><i class='fa fa-times-circle-o'></i></div>");
                var i = t("#mainmenutop .navbar-toggle, .menu-offcanvas-inner .button-close-menu"),
                    r = a() ? "touchstart" : "click";
                t(i).bind(r, function(a) {
                    return t("#offcanvas-container").toggleClass("offcanvas-menu-open").addClass("offcanvas-effect-" + n), t("#page").bind(r, function() {
                        t("#offcanvas-container").toggleClass("offcanvas-menu-open"), t("#page").unbind(r)
                    }), a.stopPropagation(), !1
                }), t(document.body).on(r, '#menu-offcanvas [data-toggle="dropdown"]', function() {
                    !t(this).parent().hasClass("open") && this.href && "#" != this.href && (window.location.href = this.href)
                })
            })
        }
        e()
    }();
    t(document).ready(function() {
        t("#columns").hasClass("offcanvas-siderbars") && (t(".sidebar").parent().parent().find("section").addClass("main-column"), t(".sidebar").each(function() {
            t('[data-for="' + t(this).attr("id") + '"]').show(), t(this).parent().attr("id", "ves-" + t(this).attr("id")).addClass("offcanvas-sidebar")
        }), t(".offcanvas-sidebars-buttons button").bind("click", function() {
            t(".offcanvas-siderbars").removeClass("column-right" == t(this).data("for") ? "column-left-active" : "column-right-active"), t(".offcanvas-siderbars").toggleClass(t(this).data("for") + "-active"), t("#ves-" + t(this).data("for")).toggleClass("canvas-show")
        }))
    }), t(window).ready(function() {
        t(document.body).on("click", '#mainmenutop [data-toggle="dropdown"]', function() {
            !t(this).parent().hasClass("open") && this.href && "#" != this.href && (window.location.href = this.href)
        }), t('[data-toggle="tooltip"]').tooltip(), t(".quantity-adder .add-action").click(function() {
            var a = t(this).parent(".quantity-wrapper").parent(".quantity-adder").children(".quantity-number").children("input");
            t(this).hasClass("add-up") ? a.val(parseInt(a.val()) + 1) : parseInt(a.val()) > 1 && a.val(parseInt(a.val()) - 1)
        }), t(".box-heading").each(function() {
            if (t(this).children("span").length) {
                var a = /\s+/,
                    e = t(this).text().split(a);
                if (e.length > 1) {
                    var n = t(this).text().replace(e[0], "<span>" + e[0] + "</span>");
                    t(this).children("span").html(n)
                }
            }
        })
    }), t(window).ready(function() {
        jQuery(".scrollup").length > 0 && jQuery(document).ready(function() {
            jQuery(window).scroll(function() {
                jQuery(this).scrollTop() > 100 ? jQuery(".scrollup").fadeIn() : jQuery(".scrollup").fadeOut()
            }), jQuery(".scrollup").click(function() {
                return jQuery("html, body").animate({
                    scrollTop: 0
                }, 600), !1
            })
        }), t("#cart > .heading").on("click", "a", function(a) {
            t(this).attr("data-target") || (a.preventDefault(), jQuery("#cart").addClass("active"), getAjaxCart(), t(document).on("mouseleave", "#cart", function() {
                t(this).removeClass("active")
            }), t("body").not("#cart").click(function() {
                t(this).removeClass("active")
            }))
        }), t(".block-cart .btn-remove").length > 0 && (t(".block-cart .btn-remove").unbind("click"), t(".block-cart .btn-remove").attr("onclick", ""), t(".block-cart .btn-remove").on("click", function(a) {
            return a.preventDefault(), click_delete_item() && deleteItemCart(t(this).attr("href")), !1
        })), "undefined" != typeof productAddToCartForm && "undefined" != typeof ajaxCart && ajaxCart && (productAddToCartForm.submit = function(a, e) {
            if (isQuote) {
                var n = confirm("By clicking OK below your current shopping basket will be emptied and replaced with the current item. If you do not wish for this to happen, click CANCEL.");
                if (0 == n) return !1
            }
            if (this.validator.validate()) {
                var o = this.form,
                    i = o.action;
                e && (o.action = e);
                var r = null;
                e || (e = t("#product_addtocart_form").attr("action"));
                var c = t("#product_addtocart_form").serialize();
                c += "&isAjax=1";
                try {
                    t.ajax({
                        url: e,
                        dataType: "json",
                        type: "post",
                        data: c,
                        success: function(a) {
                            //jQuery(".success, .warning, .attention, .information, .error").remove();
								jQuery("#messages_product_view").html('');
							if(a.status == "SUCCESS"){
								jQuery("#notification").html('<div class="success" style="display: none;">' + a.message + '<a onclick="jQuery(\'.success, .warning, .attention, .information, .error\').remove()" class="close btn-remove" href="javascript:;">X</a></div>'); 
								jQuery(".success").fadeIn("slow").delay(5e3).hide(0), showMiniCart(), jQuery("html, body").animate({
									scrollTop: 0
								}, "slow");
								t(".cart-top .quick-access").addClass("quick-access-show");
								t(".cart-top .quick-access").addClass("quick-access-show-scrolled");
								jQuery("#messages_product_view").html('<ul class="messages"><li class="success-msg"><ul><li><span>'+a.message+'</span></li></ul></li></ul>');
							}else{
								jQuery("#messages_product_view").html('<ul class="messages"><li class="success-msg"><ul><li><span>'+a.message+'</span></li></ul></li></ul>');
                                jQuery("#Qty").val('1');
							}
							
                        }
                    })
                } catch (r) {}
                if (this.form.action = i, r) throw r
            }
            return !1
        }.bind(productAddToCartForm))
    })
}(jQuery);