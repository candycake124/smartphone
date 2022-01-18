define(["underscore", "jquery", "ko", "uiComponent", "mage/translate", "Magento_Catalog/js/price-utils"], (function(t, $, e, r, n, i) {
    "use strict";
    return r.extend({
        defaults: {
            installmentprice: "",
            displayFull: !1,
            getPriceForCalc: "",
            isApply: !1,
            template: "O2TI_Installment/installment"
        },
        initObservable: function() {
            return this._super().observe({
                isApply: e.observable(!1),
                displayFull: e.observable(!1),
                installmentprice: e.observable(!1),
                getPriceForCalc: e.observable(!1),
                getInstallments: e.observable(!0),
                getInstall: e.observable(!0)
            }), this
        },
        initialize: function() {
            this._super();
            var t = this.index;
            this.applyFullInstallment && (this.displayFull = e.observable(!0));
            var r = $("[data-role=block-" + t + "]").attr("data-installment-for-price"),
                n = this.getInstallments(r);
            return this.maxInstallments = n.slice(-1), n.length > 1 && (this.isApply = e.observable(!0)), this.displayFull() && (this.ListAllInstallments = n), this
        },
        getInstall: function(e) {
            var r = parseFloat(e),
                n = this.getTypeInterest(),
                i = this.getInterest(),
                l = this.getLimitByPlotPrice(),
                s = this.getLimitByPortionNumber(),
                a = {},
                o = 0;
            a[1] = {
                parcela: r,
                juros: 0
            };
            var u = r / l;
            (u = parseInt(u)) > s ? u = s : u > 12 && (u = 12);
            var c = u;
            return t.each(i, (function(t, e) {
                if (o <= u)
                    if ((e = i[e]) > 0) {
                        var s = e / 100;
                        if ("compound" == n) var c = Math.pow(1 / (1 + s), o),
                            m = r * s / (1 - c);
                        else m = (r * s + r) / o;
                        var p = e;
                        m > 5 && m > l && (a[o] = {
                            parcela: m,
                            juros: p
                        })
                    } else r > 0 && o > 0 && (a[o] = {
                        parcela: r / o,
                        juros: 0
                    });
                o++
            })), t.each(a, (function(t, e) {
                t > c && delete a[t]
            })), a
        },
        getInstallments: function(e) {
            for (var r = this.getFormatCurrency(), l = this.getFormatCurrencySymbol(), s = t.map(this.getInstall(e), (function(t, e) {
                    if (0 == t.juros) var s = n(" sem juros");
                    else s = "*";
                    var a = parseFloat(t.parcela),
                        o = i.formatPrice(a, r),
                        u = l + l;
                    o = r.replace("%s", o).replace(u, l);
                    return {
                        qty: e,
                        installments: i.formatPrice(a, r),
                        info_interest: s,
                        text_format: n("%1x de %2%3").replace("%1", e).replace("%2", o).replace("%3", s)
                    }
                })), a = [], o = 0; o < s.length; o++) "undefined" != s[o].installments && null != s[o].installments && a.push(s[o]);
            return a
        },
        getFormatCurrency: function() {
            return window.installmentProviders.current_currency
        },
        getFormatCurrencySymbol: function() {
            return window.installmentProviders.current_currency_symbol
        },
        getInterest: function() {
            return window.installmentProviders.interest
        },
        getTypeInterest: function() {
            return window.installmentProviders.type_interest
        },
        getLimitByPlotPrice: function() {
            return window.installmentProviders.limite_by_price
        },
        getLimitByPortionNumber: function() {
            return window.installmentProviders.limite_by_installment
        }
    })
}));