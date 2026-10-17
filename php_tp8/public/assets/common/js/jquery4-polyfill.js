/**
 * jQuery 4 兼容垫片：恢复 jQuery 4 已移除的 API，
 * 使旧插件（jquery.form、layer 等）在 jQuery 4 下继续可用。
 * 必须在 jquery.min.js 之后、其余插件之前加载。
 */
(function ($, undefined) {
    'use strict';

    // $.trim 已在 jQuery 4 移除
    if (typeof $.trim !== 'function') {
        $.trim = function (value) {
            return value == null ? '' : String(value).trim();
        };
    }

    // $.fn.size() 已在 jQuery 4 移除
    if (typeof $.fn.size !== 'function') {
        $.fn.size = function () { return this.length; };
    }

    // $.browser 已在 jQuery 4 移除，提供最小实现（jquery.form 等旧插件依赖）
    if (typeof $.browser === 'undefined') {
        var ua = (window.navigator && window.navigator.userAgent || '').toLowerCase();
        $.browser = {
            msie: /msie/.test(ua) && !/opera/.test(ua),
            mozilla: /mozilla/.test(ua) && !/webkit|compatible/.test(ua),
            webkit: /webkit/.test(ua),
            opera: /opera/.test(ua),
            version: (ua.match(/.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/) || [])[1] || '0'
        };
    }

    // 事件简写方法在 jQuery 4 被移除：.bind/.unbind/.delegate/.live/.die
    var map = {
        bind: 'on', unbind: 'off',
        delegate: 'on', undelegate: 'off',
        live: 'on', die: 'off'
    };
    Object.keys(map).forEach(function (name) {
        if (typeof $.fn[name] !== 'function') {
            $.fn[name] = function () { return $.fn[map[name]].apply(this, arguments); };
        }
    });

    // .toggle(event) 事件用法也已移除，降级为 .on('click')
    if (typeof $.fn.toggle !== 'function') {
        $.fn.toggle = function () {
            return $.fn.on.apply(this, ['click'].concat([].slice.call(arguments)));
        };
    }

    // 类型判断工具已在 jQuery 4 移除（webuploader 依赖 $.isFunction / $.type）
    if (typeof $.isFunction !== 'function') {
        $.isFunction = function (value) { return typeof value === 'function'; };
    }
    if (typeof $.isArray !== 'function') {
        $.isArray = function (value) { return Array.isArray(value); };
    }
    if (typeof $.isWindow !== 'function') {
        $.isWindow = function (value) { return value != null && value === value.window; };
    }
    if (typeof $.parseJSON !== 'function') {
        $.parseJSON = function (data) { return JSON.parse(String(data)); };
    }
    if (typeof $.now !== 'function') {
        $.now = function () { return Date.now(); };
    }
    if (typeof $.type !== 'function') {
        var class2type = {};
        ('Boolean Number String Function Array Date RegExp Object Error Symbol').split(' ').forEach(function (name) {
            class2type['[object ' + name + ']'] = name.toLowerCase();
        });
        $.type = function (obj) {
            if (obj == null) {
                return String(obj);
            }
            return typeof obj === 'object' || typeof obj === 'function'
                ? (class2type[Object.prototype.toString.call(obj)] || 'object')
                : typeof obj;
        };
    }
    if (typeof $.proxy !== 'function') {
        $.proxy = function (fn, context) {
            var slice = Array.prototype.slice;
            var args = slice.call(arguments, 2);
            return function () {
                return fn.apply(context, args.concat(slice.call(arguments)));
            };
        };
    }
})(jQuery);
