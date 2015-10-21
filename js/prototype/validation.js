function removeDelimiters(e) {
    return e = e.replace(/\s/g, ""), e = e.replace(/\-/g, "")
}
function parseNumber(e) {
    if ("string" != typeof e)return parseFloat(e);
    var t = e.indexOf("."), a = e.indexOf(",");
    return -1 != t && -1 != a ? e = a > t ? e.replace(".", "").replace(",", ".") : e.replace(",", "") : -1 != a && (e = e.replace(",", ".")), parseFloat(e)
}
var Validator = Class.create();
Validator.prototype = {
    initialize: function (e, t, a, i) {
        "function" == typeof a ? (this.options = $H(i), this._test = a) : (this.options = $H(a), this._test = function () {
            return !0
        }), this.error = t || "Validation failed.", this.className = e
    }, test: function (e, t) {
        return this._test(e, t) && this.options.all(function (a) {
                return Validator.methods[a.key] ? Validator.methods[a.key](e, t, a.value) : !0
            })
    }
}, Validator.methods = {
    pattern: function (e, t, a) {
        return Validation.get("IsEmpty").test(e) || a.test(e)
    }, minLength: function (e, t, a) {
        return e.length >= a
    }, maxLength: function (e, t, a) {
        return e.length <= a
    }, min: function (e, t, a) {
        return e >= parseFloat(a)
    }, max: function (e, t, a) {
        return e <= parseFloat(a)
    }, notOneOf: function (e, t, a) {
        return $A(a).all(function (t) {
            return e != t
        })
    }, oneOf: function (e, t, a) {
        return $A(a).any(function (t) {
            return e == t
        })
    }, is: function (e, t, a) {
        return e == a
    }, isNot: function (e, t, a) {
        return e != a
    }, equalToField: function (e, t, a) {
        return e == $F(a)
    }, notEqualToField: function (e, t, a) {
        return e != $F(a)
    }, include: function (e, t, a) {
        return $A(a).all(function (a) {
            return Validation.get(a).test(e, t)
        })
    }
};
var Validation = Class.create();
Validation.defaultOptions = {
    onSubmit: !0,
    stopOnFirst: !1,
    immediate: !1,
    focusOnError: !0,
    useTitles: !1,
    addClassNameToContainer: !1,
    containerClassName: ".input-box",
    onFormValidate: function () {
    },
    onElementValidate: function () {
    }
}, Validation.prototype = {
    initialize: function (e, t) {
        this.form = $(e), this.form && (this.options = Object.extend({
            onSubmit: Validation.defaultOptions.onSubmit,
            stopOnFirst: Validation.defaultOptions.stopOnFirst,
            immediate: Validation.defaultOptions.immediate,
            focusOnError: Validation.defaultOptions.focusOnError,
            useTitles: Validation.defaultOptions.useTitles,
            onFormValidate: Validation.defaultOptions.onFormValidate,
            onElementValidate: Validation.defaultOptions.onElementValidate
        }, t || {}), this.options.onSubmit && Event.observe(this.form, "submit", this.onSubmit.bind(this), !1), this.options.immediate && Form.getElements(this.form).each(function (e) {
            "select" == e.tagName.toLowerCase() && Event.observe(e, "blur", this.onChange.bindAsEventListener(this)), "radio" == e.type.toLowerCase() || "checkbox" == e.type.toLowerCase() ? Event.observe(e, "click", this.onChange.bindAsEventListener(this)) : Event.observe(e, "change", this.onChange.bindAsEventListener(this))
        }, this))
    }, onChange: function (e) {
        Validation.isOnChange = !0, Validation.validate(Event.element(e), {
            useTitle: this.options.useTitles,
            onElementValidate: this.options.onElementValidate
        }), Validation.isOnChange = !1
    }, onSubmit: function (e) {
        this.validate() || Event.stop(e)
    }, validate: function () {
        var e = !1, t = this.options.useTitles, a = this.options.onElementValidate;
        try {
            e = this.options.stopOnFirst ? Form.getElements(this.form).all(function (e) {
                return e.hasClassName("local-validation") && !this.isElementInForm(e, this.form) ? !0 : Validation.validate(e, {
                    useTitle: t,
                    onElementValidate: a
                })
            }, this) : Form.getElements(this.form).collect(function (e) {
                return e.hasClassName("local-validation") && !this.isElementInForm(e, this.form) ? !0 : Validation.validate(e, {
                    useTitle: t,
                    onElementValidate: a
                })
            }, this).all()
        } catch (i) {
        }
        if (!e && this.options.focusOnError)try {
            Form.getElements(this.form).findAll(function (e) {
                return $(e).hasClassName("validation-failed")
            }).first().focus()
        } catch (i) {
        }
        return this.options.onFormValidate(e, this.form), e
    }, reset: function () {
        Form.getElements(this.form).each(Validation.reset)
    }, isElementInForm: function (e, t) {
        var a = e.up("form");
        return a == t ? !0 : !1
    }
}, Object.extend(Validation, {
    validate: function (e, t) {
        t = Object.extend({
            useTitle: !1, onElementValidate: function () {
            }
        }, t || {}), e = $(e);
        var a = $w(e.className);
        return result = a.all(function (a) {
            var i = Validation.test(a, e, t.useTitle);
            return t.onElementValidate(i, e), i
        })
    }, insertAdvice: function (e, t) {
        var a = $(e).up(".field-row");
        if (a)Element.insert(a, {after: t}); else if (e.up("td.value"))e.up("td.value").insert({bottom: t}); else if (e.advaiceContainer && $(e.advaiceContainer))$(e.advaiceContainer).update(t); else switch (e.type.toLowerCase()) {
            case"checkbox":
            case"radio":
                var i = e.parentNode;
                i ? Element.insert(i, {bottom: t}) : Element.insert(e, {after: t});
                break;
            default:
                Element.insert(e, {after: t})
        }
    }, showAdvice: function (e, t, a) {
        e.advices ? e.advices.each(function (a) {
            t && a.value.id == t.id || this.hideAdvice(e, a.value)
        }.bind(this)) : e.advices = new Hash, e.advices.set(a, t), "undefined" == typeof Effect ? t.style.display = "block" : t._adviceAbsolutize ? (Position.absolutize(t), t.show(), t.setStyle({
            top: t._adviceTop,
            left: t._adviceLeft,
            width: t._adviceWidth,
            "z-index": 1e3
        }), t.addClassName("advice-absolute")) : new Effect.Appear(t, {duration: 1})
    }, hideAdvice: function (e, t) {
        null != t && new Effect.Fade(t, {
            duration: 1, afterFinishInternal: function () {
                t.hide()
            }
        })
    }, updateCallback: function (elm, status) {
        "undefined" != typeof elm.callbackFunction && eval(elm.callbackFunction + "('" + elm.id + "','" + status + "')")
    }, ajaxError: function (e, t) {
        var a = "validate-ajax", i = Validation.getAdvice(a, e);
        if (null == i && (i = this.createAdvice(a, e, !1, t)), this.showAdvice(e, i, "validate-ajax"), this.updateCallback(e, "failed"), e.addClassName("validation-failed"), e.addClassName("validate-ajax"), Validation.defaultOptions.addClassNameToContainer && "" != Validation.defaultOptions.containerClassName) {
            var n = e.up(Validation.defaultOptions.containerClassName);
            n && this.allowContainerClassName(e) && (n.removeClassName("validation-passed"), n.addClassName("validation-error"))
        }
    }, allowContainerClassName: function (e) {
        return "radio" == e.type || "checkbox" == e.type ? e.hasClassName("change-container-classname") : !0
    }, test: function (e, t, a) {
        var i = Validation.get(e), n = "__advice" + e.camelize();
        try {
            if (Validation.isVisible(t) && !i.test($F(t), t)) {
                var r = Validation.getAdvice(e, t);
                if (null == r && (r = this.createAdvice(e, t, a)), this.showAdvice(t, r, e), this.updateCallback(t, "failed"), t[n] = 1, t.advaiceContainer || (t.removeClassName("validation-passed"), t.addClassName("validation-failed")), Validation.defaultOptions.addClassNameToContainer && "" != Validation.defaultOptions.containerClassName) {
                    var s = t.up(Validation.defaultOptions.containerClassName);
                    s && this.allowContainerClassName(t) && (s.removeClassName("validation-passed"), s.addClassName("validation-error"))
                }
                return !1
            }
            var r = Validation.getAdvice(e, t);
            if (this.hideAdvice(t, r), this.updateCallback(t, "passed"), t[n] = "", t.removeClassName("validation-failed"), t.addClassName("validation-passed"), Validation.defaultOptions.addClassNameToContainer && "" != Validation.defaultOptions.containerClassName) {
                var s = t.up(Validation.defaultOptions.containerClassName);
                s && !s.down(".validation-failed") && this.allowContainerClassName(t) && (Validation.get("IsEmpty").test(t.value) && this.isVisible(t) ? s.removeClassName("validation-passed") : s.addClassName("validation-passed"), s.removeClassName("validation-error"))
            }
            return !0
        } catch (o) {
            throw o
        }
    }, isVisible: function (e) {
        for (; "BODY" != e.tagName;) {
            if (!$(e).visible())return !1;
            e = e.parentNode
        }
        return !0
    }, getAdvice: function (e, t) {
        return $("advice-" + e + "-" + Validation.getElmID(t)) || $("advice-" + Validation.getElmID(t))
    }, createAdvice: function (e, t, a, i) {
        var n = Validation.get(e), r = a && t && t.title ? t.title : n.error;
        i && (r = i);
        try {
            Translator && (r = Translator.translate(r))
        } catch (s) {
        }
        if (advice = '<div class="validation-advice" id="advice-' + e + "-" + Validation.getElmID(t) + '" style="display:none">' + r + "</div>", Validation.insertAdvice(t, advice), advice = Validation.getAdvice(e, t), $(t).hasClassName("absolute-advice")) {
            var o = $(t).getDimensions(), l = Position.cumulativeOffset(t);
            advice._adviceTop = l[1] + o.height + "px", advice._adviceLeft = l[0] + "px", advice._adviceWidth = o.width + "px", advice._adviceAbsolutize = !0
        }
        return advice
    }, getElmID: function (e) {
        return e.id ? e.id : e.name
    }, reset: function (e) {
        e = $(e);
        var t = $w(e.className);
        t.each(function (t) {
            var a = "__advice" + t.camelize();
            if (e[a]) {
                var i = Validation.getAdvice(t, e);
                i && i.hide(), e[a] = ""
            }
            if (e.removeClassName("validation-failed"), e.removeClassName("validation-passed"), Validation.defaultOptions.addClassNameToContainer && "" != Validation.defaultOptions.containerClassName) {
                var n = e.up(Validation.defaultOptions.containerClassName);
                n && (n.removeClassName("validation-passed"), n.removeClassName("validation-error"))
            }
        })
    }, add: function (e, t, a, i) {
        var n = {};
        n[e] = new Validator(e, t, a, i), Object.extend(Validation.methods, n)
    }, addAllThese: function (e) {
        var t = {};
        $A(e).each(function (e) {
            t[e[0]] = new Validator(e[0], e[1], e[2], e.length > 3 ? e[3] : {})
        }), Object.extend(Validation.methods, t)
    }, get: function (e) {
        return Validation.methods[e] ? Validation.methods[e] : Validation.methods._LikeNoIDIEverSaw_
    }, methods: {_LikeNoIDIEverSaw_: new Validator("_LikeNoIDIEverSaw_", "", {})}
}), Validation.add("IsEmpty", "", function (e) {
    return "" == e || null == e || 0 == e.length || /^\s+$/.test(e)
}), Validation.addAllThese([["validate-no-html-tags", "HTML tags are not allowed", function (e) {
    return !/<(\/)?\w+/.test(e)
}], ["validate-select", "Please select an option.", function (e) {
    return "none" != e && null != e && 0 != e.length
}], ["required-entry", "This is a required field.", function (e) {
    return !Validation.get("IsEmpty").test(e)
}], ["validate-number", "Please enter a valid number in this field.", function (e) {
    return Validation.get("IsEmpty").test(e) || !isNaN(parseNumber(e)) && /^\s*-?\d*(\.\d*)?\s*$/.test(e)
}], ["validate-number-range", "The value is not within the specified range.", function (e, t) {
    if (Validation.get("IsEmpty").test(e))return !0;
    var a = parseNumber(e);
    if (isNaN(a))return !1;
    var i = /^number-range-(-?[\d.,]+)?-(-?[\d.,]+)?$/, n = !0;
    return $w(t.className).each(function (e) {
        var t = i.exec(e);
        t && (n = n && (null == t[1] || "" == t[1] || a >= parseNumber(t[1])) && (null == t[2] || "" == t[2] || a <= parseNumber(t[2])))
    }), n
}], ["validate-digits", "Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.", function (e) {
    return Validation.get("IsEmpty").test(e) || !/[^\d]/.test(e)
}], ["validate-digits-range", "The value is not within the specified range.", function (e, t) {
    if (Validation.get("IsEmpty").test(e))return !0;
    var a = parseNumber(e);
    if (isNaN(a))return !1;
    var i = /^digits-range-(-?\d+)?-(-?\d+)?$/, n = !0;
    return $w(t.className).each(function (e) {
        var t = i.exec(e);
        t && (n = n && (null == t[1] || "" == t[1] || a >= parseNumber(t[1])) && (null == t[2] || "" == t[2] || a <= parseNumber(t[2])))
    }), n
}], ["validate-alpha", "Please use letters only (a-z or A-Z) in this field.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^[a-zA-Z]+$/.test(e)
}], ["validate-code", "Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^[a-z]+[a-z0-9_]+$/.test(e)
}], ["validate-alphanum", "Please use only letters (a-z or A-Z) or numbers (0-9) only in this field. No spaces or other characters are allowed.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^[a-zA-Z0-9]+$/.test(e)
}], ["validate-alphanum-with-spaces", "Please use only letters (a-z or A-Z), numbers (0-9) or spaces only in this field.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^[a-zA-Z0-9 ]+$/.test(e)
}], ["validate-street", "Please use only letters (a-z or A-Z) or numbers (0-9) or spaces and # only in this field.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^[ \w]{3,}([A-Za-z]\.)?([ \w]*\#\d+)?(\r\n| )[ \w]{3,}/.test(e)
}], ["validate-phoneStrict", "Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(e)
}], ["validate-phoneLax", "Please enter a valid phone number. For example (123) 456-7890 or 123-456-7890.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^((\d[-. ]?)?((\(\d{3}\))|\d{3}))?[-. ]?\d{3}[-. ]?\d{4}$/.test(e)
}], ["validate-fax", "Please enter a valid fax number. For example (123) 456-7890 or 123-456-7890.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^(\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4}$/.test(e)
}], ["validate-date", "Please enter a valid date.", function (e) {
    var t = new Date(e);
    return Validation.get("IsEmpty").test(e) || !isNaN(t)
}], ["validate-date-range", "The From Date value should be less than or equal to the To Date value.", function (e, t) {
    var a = /\bdate-range-(\w+)-(\w+)\b/.exec(t.className);
    if (!a || "to" == a[2] || Validation.get("IsEmpty").test(e))return !0;
    var i = (new Date).getFullYear() + "", n = function (e) {
        return e = e.split(/[.\/]/), e[2] && e[2].length < 4 && (e[2] = i.substr(0, e[2].length) + e[2]), new Date(e.join("/")).getTime()
    }, r = Element.select(t.form, ".validate-date-range.date-range-" + a[1] + "-to");
    return !r.length || Validation.get("IsEmpty").test(r[0].value) || n(e) <= n(r[0].value)
}], ["validate-email", "Please enter a valid email address. For example johndoe@domain.com.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(e)
}], ["validate-emailSender", "Please use only visible characters and spaces.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^[\S ]+$/.test(e)
}], ["validate-password", "Please enter 6 or more characters. Leading or trailing spaces will be ignored.", function (e) {
    var t = e.strip();
    return !(t.length > 0 && t.length < 6)
}], ["validate-admin-password", "Please enter 7 or more characters. Password should contain both numeric and alphabetic characters.", function (e) {
    var t = e.strip();
    return 0 == t.length ? !0 : /[a-z]/i.test(e) && /[0-9]/.test(e) ? !(t.length < 7) : !1
}], ["validate-cpassword", "Please make sure your passwords match.", function () {
    var e = $("confirmation") ? $("confirmation") : $$(".validate-cpassword")[0], t = !1;
    $("password") && (t = $("password"));
    for (var a = $$(".validate-password"), i = 0; i < a.size(); i++) {
        var n = a[i];
        n.up("form").id == e.up("form").id && (t = n)
    }
    return $$(".validate-admin-password").size() && (t = $$(".validate-admin-password")[0]), t.value == e.value
}], ["validate-both-passwords", "Please make sure your passwords match.", function (e, t) {
    var a = $(t.form["password" == t.name ? "confirmation" : "password"]), i = t.value == a.value;
    return i && a.hasClassName("validation-failed") && Validation.test(this.className, a), "" == a.value || i
}], ["validate-url", "Please enter a valid URL. Protocol is required (http://, https:// or ftp://)", function (e) {
    return e = (e || "").replace(/^\s+/, "").replace(/\s+$/, ""), Validation.get("IsEmpty").test(e) || /^(http|https|ftp):\/\/(([A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))(\.[A-Z0-9]([A-Z0-9_-]*[A-Z0-9]|))*)(:(\d+))?(\/[A-Z0-9~](([A-Z0-9_~-]|\.)*[A-Z0-9~]|))*\/?(.*)?$/i.test(e)
}], ["validate-clean-url", "Please enter a valid URL. For example http://www.example.com or www.example.com", function (e) {
    return Validation.get("IsEmpty").test(e) || /^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(e) || /^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i.test(e)
}], ["validate-identifier", 'Please enter a valid URL Key. For example "example-page", "example-page.html" or "anotherlevel/example-page".', function (e) {
    return Validation.get("IsEmpty").test(e) || /^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/.test(e)
}], ["validate-xml-identifier", "Please enter a valid XML-identifier. For example something_1, block5, id-4.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^[A-Z][A-Z0-9_\/-]*$/i.test(e)
}], ["validate-ssn", "Please enter a valid social security number. For example 123-45-6789.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^\d{3}-?\d{2}-?\d{4}$/.test(e)
}], ["validate-zip", "Please enter a valid zip code. For example 90602 or 90602-1234.", function (e) {
    return Validation.get("IsEmpty").test(e) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(e)
}], ["validate-zip-countrygp", "Please enter a valid zip code. For example LE19 3LY or LE193LY.", function (e) {
    return Validation.get("IsEmpty").test(e) || /(^[A-Z]{1,2}[0-9R][0-9A-Z]?[\s]?[0-9][ABD-HJLNP-UW-Z]{2}$)/i.test(e)
}], ["validate-zip-international", "Please enter a valid zip code.", function () {
    return !0
}], ["validate-date-au", "Please use this date format: dd/mm/yyyy. For example 17/03/2006 for the 17th of March, 2006.", function (e) {
    if (Validation.get("IsEmpty").test(e))return !0;
    var t = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if (!t.test(e))return !1;
    var a = new Date(e.replace(t, "$2/$1/$3"));
    return parseInt(RegExp.$2, 10) == 1 + a.getMonth() && parseInt(RegExp.$1, 10) == a.getDate() && parseInt(RegExp.$3, 10) == a.getFullYear()
}], ["validate-currency-dollar", "Please enter a valid $ amount. For example $100.00.", function (e) {
    return Validation.get("IsEmpty").test(e) || /^\$?\-?([1-9]{1}[0-9]{0,2}(\,[0-9]{3})*(\.[0-9]{0,2})?|[1-9]{1}\d*(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|(\.[0-9]{1,2})?)$/.test(e)
}], ["validate-one-required", "Please select one of the above options.", function (e, t) {
    var a = t.parentNode, i = a.getElementsByTagName("INPUT");
    return $A(i).any(function (e) {
        return $F(e)
    })
}], ["validate-one-required-by-name", "Please select one of the options.", function (e, t) {
    for (var a = $$('input[name="' + t.name.replace(/([\\"])/g, "\\$1") + '"]'), i = 1, n = 0; n < a.length; n++)"checkbox" != a[n].type && "radio" != a[n].type || 1 != a[n].checked || (i = 0), !Validation.isOnChange || "checkbox" != a[n].type && "radio" != a[n].type || Validation.reset(a[n]);
    return 0 == i ? !0 : !1
}], ["validate-not-negative-number", "Please enter a number 0 or greater in this field.", function (e) {
    return Validation.get("IsEmpty").test(e) ? !0 : (e = parseNumber(e), !isNaN(e) && e >= 0)
}], ["validate-zero-or-greater", "Please enter a number 0 or greater in this field.", function (e) {
    return Validation.get("validate-not-negative-number").test(e)
}], ["validate-greater-than-zero", "Please enter a number greater than 0 in this field.", function (e) {
    return Validation.get("IsEmpty").test(e) ? !0 : (e = parseNumber(e), !isNaN(e) && e > 0)
}], ["validate-state", "Please select State/Province.", function (e) {
    return 0 != e || "" == e
}], ["validate-new-password", "Please enter 6 or more characters. Leading or trailing spaces will be ignored.", function (e) {
    return Validation.get("validate-password").test(e) ? Validation.get("IsEmpty").test(e) && "" != e ? !1 : !0 : !1
}], ["validate-cc-number", "Please enter a valid credit card number.", function (e, t) {
    var a = $(t.id.substr(0, t.id.indexOf("_cc_number")) + "_cc_type");
    return a && "undefined" != typeof Validation.creditCartTypes.get(a.value) && 0 == Validation.creditCartTypes.get(a.value)[2] ? !Validation.get("IsEmpty").test(e) && Validation.get("validate-digits").test(e) ? !0 : !1 : validateCreditCard(e)
}], ["validate-cc-type", "Credit card number does not match credit card type.", function (e, t) {
    t.value = removeDelimiters(t.value), e = removeDelimiters(e);
    var a = $(t.id.substr(0, t.id.indexOf("_cc_number")) + "_cc_type");
    if (!a)return !0;
    var i = a.value;
    if ("undefined" == typeof Validation.creditCartTypes.get(i))return !1;
    if (0 == Validation.creditCartTypes.get(i)[0])return !0;
    var n = !1;
    return Validation.creditCartTypes.each(function (t) {
        if (t.key == i)throw t.value[0] && !e.match(t.value[0]) && (n = !0), $break
    }), n ? !1 : (a.hasClassName("validation-failed") && Validation.isOnChange && Validation.validate(a), !0)
}], ["validate-cc-type-select", "Card type does not match credit card number.", function (e, t) {
    var a = $(t.id.substr(0, t.id.indexOf("_cc_type")) + "_cc_number");
    return Validation.isOnChange && Validation.get("IsEmpty").test(a.value) ? !0 : (Validation.get("validate-cc-type").test(a.value, a) && Validation.validate(a), Validation.get("validate-cc-type").test(a.value, a))
}], ["validate-cc-exp", "Incorrect credit card expiration date.", function (e, t) {
    var a = e, i = $(t.id.substr(0, t.id.indexOf("_expiration")) + "_expiration_yr").value, n = new Date, r = n.getMonth() + 1, s = n.getFullYear();
    return r > a && i == s ? !1 : !0
}], ["validate-cc-cvn", "Please enter a valid credit card verification number.", function (e, t) {
    var a = $(t.id.substr(0, t.id.indexOf("_cc_cid")) + "_cc_type");
    if (!a)return !0;
    var i = a.value;
    if ("undefined" == typeof Validation.creditCartTypes.get(i))return !1;
    var n = Validation.creditCartTypes.get(i)[1];
    return e.match(n) ? !0 : !1
}], ["validate-ajax", "", function () {
    return !0
}], ["validate-data", "Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.", function (e) {
    return "" != e && e ? /^[A-Za-z]+[A-Za-z0-9_]+$/.test(e) : !0
}], ["validate-css-length", "Please input a valid CSS-length. For example 100px or 77pt or 20em or .5ex or 50%.", function (e) {
    return "" != e && e ? /^[0-9\.]+(px|pt|em|ex|%)?$/.test(e) && !/\..*\./.test(e) && !/\.$/.test(e) : !0
}], ["validate-length", "Text length does not satisfy specified text range.", function (e, t) {
    var a = new RegExp(/^maximum-length-[0-9]+$/), i = new RegExp(/^minimum-length-[0-9]+$/), n = !0;
    return $w(t.className).each(function (t) {
        if (t.match(a) && n) {
            var r = t.split("-")[2];
            n = e.length <= r
        }
        if (t.match(i) && n && !Validation.get("IsEmpty").test(e)) {
            var r = t.split("-")[2];
            n = e.length >= r
        }
    }), n
}], ["validate-percents", "Please enter a number lower than 100.", {max: 100}], ["required-file", "Please select a file", function (e, t) {
    var a = !Validation.get("IsEmpty").test(e);
    return a === !1 && (ovId = t.id + "_value", $(ovId) && (a = !Validation.get("IsEmpty").test($(ovId).value))), a
}], ["validate-cc-ukss", "Please enter issue number or start date for switch/solo card type.", function (e, t) {
    var a;
    a = t.id.indexOf(t.id.match(/(.)+_cc_issue$/) ? "_cc_issue" : t.id.match(/(.)+_start_month$/) ? "_start_month" : "_start_year");
    var i = t.id.substr(0, a), n = $(i + "_cc_type");
    if (!n)return !0;
    var r = n.value;
    if (-1 == ["SS", "SM", "SO"].indexOf(r))return !0;
    $(i + "_cc_issue").advaiceContainer = $(i + "_start_month").advaiceContainer = $(i + "_start_year").advaiceContainer = $(i + "_cc_type_ss_div").down("ul li.adv-container");
    var s = $(i + "_cc_issue").value, o = $(i + "_start_month").value, l = $(i + "_start_year").value, d = o && l ? !0 : !1;
    return d || s ? !0 : !1
}]]), Validation.creditCartTypes = $H({
    SO: [new RegExp("^(6334[5-9]([0-9]{11}|[0-9]{13,14}))|(6767([0-9]{12}|[0-9]{14,15}))$"), new RegExp("^([0-9]{3}|[0-9]{4})?$"), !0],
    VI: [new RegExp("^4[0-9]{12}([0-9]{3})?$"), new RegExp("^[0-9]{3}$"), !0],
    MC: [new RegExp("^5[1-5][0-9]{14}$"), new RegExp("^[0-9]{3}$"), !0],
    AE: [new RegExp("^3[47][0-9]{13}$"), new RegExp("^[0-9]{4}$"), !0],
    DI: [new RegExp("^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}|5[0-9]{14}))$"), new RegExp("^[0-9]{3}$"), !0],
    JCB: [new RegExp("^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}|5[0-9]{14}))$"), new RegExp("^[0-9]{3,4}$"), !0],
    DICL: [new RegExp("^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}|5[0-9]{14}))$"), new RegExp("^[0-9]{3}$"), !0],
    SM: [new RegExp("(^(5[0678])[0-9]{11,18}$)|(^(6[^05])[0-9]{11,18}$)|(^(601)[^1][0-9]{9,16}$)|(^(6011)[0-9]{9,11}$)|(^(6011)[0-9]{13,16}$)|(^(65)[0-9]{11,13}$)|(^(65)[0-9]{15,18}$)|(^(49030)[2-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49033)[5-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49110)[1-2]([0-9]{10}$|[0-9]{12,13}$))|(^(49117)[4-9]([0-9]{10}$|[0-9]{12,13}$))|(^(49118)[0-2]([0-9]{10}$|[0-9]{12,13}$))|(^(4936)([0-9]{12}$|[0-9]{14,15}$))"), new RegExp("^([0-9]{3}|[0-9]{4})?$"), !0],
    OT: [!1, new RegExp("^([0-9]{3}|[0-9]{4})?$"), !1]
});