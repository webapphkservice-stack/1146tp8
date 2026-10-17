/**
 * jquery全局函数封装
 */
(function ($) {
    /**
     * Jquery类方法
     */
    $.fn.extend({

        /**
         * 删除元素
         */
        delete: function (index, url, msg) {
            $(this).click(function () {
                var param = {};
                param[index] = $(this).attr('data-id');
                layer.confirm(msg ? msg : '确定要删除吗？', {title: '友情提示'}
                    , function (index) {
                        $.post(url, param, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    }
                );
            });
        },

    });

    /**
     * Jquery全局函数
     */
    $.extend({

        /**
         * 对象转URL
         */
        urlEncode: function (data) {
            var _result = [];
            for (var key in data) {
                var value = null;
                if (data.hasOwnProperty(key)) value = data[key];
                if (value.constructor === Array) {
                    value.forEach(function (_value) {
                        _result.push(key + "=" + _value);
                    });
                } else {
                    _result.push(key + '=' + value);
                }
            }
            return _result.join('&');
        },

        /**
         * 操作成功弹框提示
         * @param msg
         * @param url
         */
        show_success: function (msg, url) {
            layer.msg(msg, {
                icon: 1
                , time: 1200
                // , anim: 1
                , shade: 0.5
                , end: function () {
                    (url !== undefined && url.length > 0) ? window.location = url : window.location.reload();
                }
            });
        },

        /**
         * 操作失败弹框提示
         * @param msg
         * @param reload
         */
        show_error: function (msg, reload) {
            var time = reload ? 1200 : 0;
            layer.alert(msg, {
                title: '提示'
                , icon: 2
                , time: time
                , anim: 6
                , end: function () {
                    reload && window.location.reload();
                }
            });
        }

    });

})(jQuery);

