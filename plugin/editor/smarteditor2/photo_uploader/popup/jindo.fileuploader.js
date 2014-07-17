/**
 * Jindo Component
 * @version 1.0.3
 * NHN_Library:Jindo_Component-1.0.3;JavaScript Components for Jindo;
 * @include Component, UIComponent, FileUploader
 */
jindo.Component = jindo.$Class({
    _htEventHandler: null,
    _htOption: null,
    $init: function () {
        var aInstance = this.constructor.getInstance();
        aInstance.push(this);
        this._htEventHandler = {};
        this._htOption = {};
        this._htOption._htSetter = {};
    },
    option: function (sName, vValue) {
        switch (typeof sName) {
        case "undefined":
            return this._htOption;
        case "string":
            if (typeof vValue != "undefined") {
                if (sName == "htCustomEventHandler") {
                    if (typeof this._htOption[sName] == "undefined") {
                        this.attach(vValue);
                    } else {
                        return this;
                    }
                }
                this._htOption[sName] = vValue;
                if (typeof this._htOption._htSetter[sName] == "function") {
                    this._htOption._htSetter[sName](vValue);
                }
            } else {
                return this._htOption[sName];
            }
            break;
        case "object":
            for (var sKey in sName) {
                if (sKey == "htCustomEventHandler") {
                    if (typeof this._htOption[sKey] == "undefined") {
                        this.attach(sName[sKey]);
                    } else {
                        continue;
                    }
                }
                this._htOption[sKey] = sName[sKey];
                if (typeof this._htOption._htSetter[sKey] == "function") {
                    this._htOption._htSetter[sKey](sName[sKey]);
                }
            }
            break;
        }
        return this;
    },
    optionSetter: function (sName, fSetter) {
        switch (typeof sName) {
        case "undefined":
            return this._htOption._htSetter;
        case "string":
            if (typeof fSetter != "undefined") {
                this._htOption._htSetter[sName] = jindo.$Fn(fSetter, this).bind();
            } else {
                return this._htOption._htSetter[sName];
            }
            break;
        case "object":
            for (var sKey in sName) {
                this._htOption._htSetter[sKey] = jindo.$Fn(sName[sKey], this).bind();
            }
            break;
        }
        return this;
    },
    fireEvent: function (sEvent, oEvent) {
        oEvent = oEvent || {};
        var fInlineHandler = this['on' + sEvent],
            aHandlerList = this._htEventHandler[sEvent] || [],
            bHasInlineHandler = typeof fInlineHandler == "function",
            bHasHandlerList = aHandlerList.length > 0;
        if (!bHasInlineHandler && !bHasHandlerList) {
            return true;
        }
        aHandlerList = aHandlerList.concat();
        oEvent.sType = sEvent;
        if (typeof oEvent._aExtend == 'undefined') {
            oEvent._aExtend = [];
            oEvent.stop = function () {
                if (oEvent._aExtend.length > 0) {
                    oEvent._aExtend[oEvent._aExtend.length - 1].bCanceled = true;
                }
            };
        }
        oEvent._aExtend.push({
            sType: sEvent,
            bCanceled: false
        });
        var aArg = [oEvent],
            i, nLen;
        for (i = 2, nLen = arguments.length; i < nLen; i++) {
            aArg.push(arguments[i]);
        }
        if (bHasInlineHandler) {
            fInlineHandler.apply(this, aArg);
        }
        if (bHasHandlerList) {
            var fHandler;
            for (i = 0, fHandler;
            (fHandler = aHandlerList[i]); i++) {
                fHandler.apply(this, aArg);
            }
        }
        return !oEvent._aExtend.pop().bCanceled;
    },
    attach: function (sEvent, fHandlerToAttach) {
        if (arguments.length == 1) {
            jindo.$H(arguments[0]).forEach(jindo.$Fn(function (fHandler, sEvent) {
                this.attach(sEvent, fHandler);
            }, this).bind());
            return this;
        }
        var aHandler = this._htEventHandler[sEvent];
        if (typeof aHandler == 'undefined') {
            aHandler = this._htEventHandler[sEvent] = [];
        }
        aHandler.push(fHandlerToAttach);
        return this;
    },
    detach: function (sEvent, fHandlerToDetach) {
        if (arguments.length == 1) {
            jindo.$H(arguments[0]).forEach(jindo.$Fn(function (fHandler, sEvent) {
                this.detach(sEvent, fHandler);
            }, this).bind());
            return this;
        }
        var aHandler = this._htEventHandler[sEvent];
        if (aHandler) {
            for (var i = 0, fHandler;
            (fHandler = aHandler[i]); i++) {
                if (fHandler === fHandlerToDetach) {
                    aHandler = aHandler.splice(i, 1);
                    break;
                }
            }
        }
        return this;
    },
    detachAll: function (sEvent) {
        var aHandler = this._htEventHandler;
        if (arguments.length) {
            if (typeof aHandler[sEvent] == 'undefined') {
                return this;
            }
            delete aHandler[sEvent];
            return this;
        }
        for (var o in aHandler) {
            delete aHandler[o];
        }
        return this;
    }
});
jindo.Component.factory = function (aObject, htOption) {
    var aReturn = [],
        oInstance;
    if (typeof htOption == "undefined") {
        htOption = {};
    }
    for (var i = 0, el;
    (el = aObject[i]); i++) {
        oInstance = new this(el, htOption);
        aReturn[aReturn.length] = oInstance;
    }
    return aReturn;
};
jindo.Component.getInstance = function () {
    if (typeof this._aInstance == "undefined") {
        this._aInstance = [];
    }
    return this._aInstance;
};
jindo.UIComponent = jindo.$Class({
    $init: function () {
        this._bIsActivating = false;
    },
    isActivating: function () {
        return this._bIsActivating;
    },
    activate: function () {
        if (this.isActivating()) {
            return this;
        }
        this._bIsActivating = true;
        if (arguments.length > 0) {
            this._onActivate.apply(this, arguments);
        } else {
            this._onActivate();
        }
        return this;
    },
    deactivate: function () {
        if (!this.isActivating()) {
            return this;
        }
        this._bIsActivating = false;
        if (arguments.length > 0) {
            this._onDeactivate.apply(this, arguments);
        } else {
            this._onDeactivate();
        }
        return this;
    }
}).extend(jindo.Component);
jindo.FileUploader = jindo.$Class({
    _bIsActivating: false,
    _aHiddenInput: [],
    $init: function (elFileSelect, htOption) {
        var htDefaultOption = {
            sUrl: '',
            sCallback: '',
            htData: {},
            sFiletype: '*',
            sMsgNotAllowedExt: "업로드가 허용되지 않는 파일형식입니다",
            bAutoUpload: false,
            bAutoReset: true,
            bActivateOnload: true
        };
        this.option(htDefaultOption);
        this.option(htOption || {});
        this._el = jindo.$(elFileSelect);
        this._wel = jindo.$Element(this._el);
        this._elForm = this._el.form;
        this._aHiddenInput = [];
        this.constructor._oCallback = {};
        this._wfChange = jindo.$Fn(this._onFileSelectChange, this);
        if (this.option("bActivateOnload")) {
            this.activate();
        }
    },
    _appendIframe: function () {
        var sIframeName = 'tmpFrame_' + this._makeUniqueId();
        this._welIframe = jindo.$Element(jindo.$('<iframe name="' + sIframeName + '" src="' + this.option("sCallback") + '?blank">')).css({
            width: '10px',
            border: '2px',
            height: '10px',
            left: '10px',
            top: '10px'
        });
        document.body.appendChild(this._welIframe.$value());
    },
    _removeIframe: function () {
        this._welIframe.leave();
    },
    getBaseElement: function () {
        return this.getFileSelect();
    },
    getFileSelect: function () {
        return this._el;
    },
    getFormElement: function () {
        return this._elForm;
    },
    upload: function () {
        this._appendIframe();
        var elForm = this.getFormElement(),
            welForm = jindo.$Element(elForm),
            sIframeName = this._welIframe.attr("name"),
            sFunctionName = sIframeName + '_func',
            sAction = this.option("sUrl");
        welForm.attr({
            target: sIframeName,
            action: sAction
        });
        this._aHiddenInput.push(this._createElement('input', {
            'type': 'hidden',
            'name': 'callback',
            'value': this.option("sCallback")
        }));
        this._aHiddenInput.push(this._createElement('input', {
            'type': 'hidden',
            'name': 'callback_func',
            'value': sFunctionName
        }));
        for (var k in this.option("htData")) {
            this._aHiddenInput.push(this._createElement('input', {
                'type': 'hidden',
                'name': k,
                'value': this.option("htData")[k]
            }));
        }
        for (var i = 0; i < this._aHiddenInput.length; i++) {
            elForm.appendChild(this._aHiddenInput[i]);
        }
        this.constructor._oCallback[sFunctionName + '_success'] = jindo.$Fn(function (oParameter) {
            this.fireEvent("success", {
                htResult: oParameter
            });
            delete this.constructor._oCallback[oParameter.callback_func + '_success'];
            delete this.constructor._oCallback[oParameter.callback_func + '_error'];
            for (var i = 0; i < this._aHiddenInput.length; i++) {
                jindo.$Element(this._aHiddenInput[i]).leave();
            }
            this._aHiddenInput.length = 0;
            this._removeIframe();
        }, this).bind();
        this.constructor._oCallback[sFunctionName + '_error'] = jindo.$Fn(function (oParameter) {
            this.fireEvent("error", {
                htResult: oParameter
            });
            delete this.constructor._oCallback[oParameter.callback_func + '_success'];
            delete this.constructor._oCallback[oParameter.callback_func + '_error'];
            for (var i = 0; i < this._aHiddenInput.length; i++) {
                jindo.$Element(this._aHiddenInput[i]).leave();
            }
            this._aHiddenInput.length = 0;
            this._removeIframe();
        }, this).bind();
        
        elForm.submit();
        if (this.option("bAutoReset")) {
            this.reset();
        }
    },
    reset: function () {
        var elWrapForm = jindo.$("<form>");
        this._wel.wrap(elWrapForm);
        elWrapForm.reset();
        jindo.$Element(elWrapForm).replace(this._el);
        var elForm = this.getFormElement(),
            welForm = jindo.$Element(elForm);
        welForm.attr({
            target: this._sPrevTarget,
            action: this._sAction
        });
        return this;
    },
    _onActivate: function () {
        var elForm = this.getFormElement(),
            welForm = jindo.$Element(elForm);
        this._sPrevTarget = welForm.attr("target");
        this._sAction = welForm.attr("action");
        this._el.value = "";
        this._wfChange.attach(this._el, "change");
    },
    _onDeactivate: function () {
        this._wfChange.detach(this._el, "change");
    },
    _makeUniqueId: function () {
        return new Date().getMilliseconds() + Math.floor(Math.random() * 100000);
    },
    _createElement: function (name, attributes) {
        var el = jindo.$("<" + name + ">");
        var wel = jindo.$Element(el);
        for (var k in attributes) {
            wel.attr(k, attributes[k]);
        }
        return el;
    },
    _checkExtension: function (sFile) {
        var aType = this.option("sFiletype").split(';');
        for (var i = 0, sType; i < aType.length; i++) {
            sType = (aType[i] == "*.*") ? "*" : aType[i];
            sType = sType.replace(/^\s+|\s+$/, '');
            sType = sType.replace(/\./g, '\\.');
            sType = sType.replace(/\*/g, '[^\\\/]+');
            if ((new RegExp(sType + '$', 'gi')).test(sFile)) {
                return true;
            }
        }
        return false;
    },
    _onFileSelectChange: function (we) {
        var sValue = we.element.value,
            bAllowed = this._checkExtension(sValue),
            htParam = {
                sValue: sValue,
                bAllowed: bAllowed,
                sMsgNotAllowedExt: this.option("sMsgNotAllowedExt")
            };
        if (sValue.length && this.fireEvent("select", htParam)) {
            if (bAllowed) {
                if (this.option("bAutoUpload")) {
                    this.upload();
                }
            } else {
                alert(htParam.sMsgNotAllowedExt);
            }
        }
    }
}).extend(jindo.UIComponent);