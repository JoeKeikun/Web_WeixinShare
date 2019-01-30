/**
 * @file share.js 全局函数，实现微信分享
 */

/* global wx */

function httpGetAsync(url, callback, errorCallback) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState === 4) {
            if (xmlHttp.status === 200) {
                typeof callback === 'function' && callback(xmlHttp.responseText);
            } else {
                typeof errorCallback === 'function' && errorCallback(xmlHttp.responseText);
            }
        }
    };
    xmlHttp.open('GET', url, true); // true for asynchronous
    xmlHttp.send(null);
}

/**
 * 微信分享
 */
function initWXShare(baseUrl) {
    httpGetAsync(baseUrl + '/?url=' + encodeURIComponent(location.href.split('#')[0]), function (rspText) {
        var data = {};

        // 转换数据
        try {
            data = JSON.parse(rspText);
        } catch (e) {
            console.error(e);
        }

        // 没有数据则终止
        if (!data || !data.signature) {
            return;
        }

        wx.config({
            debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: data.appId, // 必填，公众号的唯一标识
            timestamp: data.timestamp, // 必填，生成签名的时间戳
            nonceStr: data.nonceStr, // 必填，生成签名的随机串
            signature: data.signature, // 必填，签名
            jsApiList: [
                'showOptionMenu',
                'updateAppMessageShareData',
                'updateTimelineShareData',
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ'
            ]
        });

        wx.ready(function () {
            wx.showOptionMenu();

            // ----- 1.4.0版 -----
            // “分享给朋友”及“分享到QQ”
            wx.updateAppMessageShareData({
                title: APPCONF.shareTitle,
                desc: APPCONF.shareDesc,
                link: APPCONF.url,
                imgUrl: APPCONF.shareImgUrl,
                success: function () {},
                cancel: function () {}
            });
            // “分享到朋友圈”及“分享到QQ空间”
            wx.updateTimelineShareData({
                title: APPCONF.shareTitle,
                link: APPCONF.url,
                imgUrl: APPCONF.shareImgUrl,
                success: function () {
                    // 设置成功
                }
            });

            // ----- 旧版 -----
            // 分享到朋友圈
            wx.onMenuShareTimeline({
                title: APPCONF.shareTitle,
                desc: APPCONF.shareDesc,
                link: APPCONF.url,
                imgUrl: APPCONF.shareImgUrl,
                success: function () {},
                cancel: function () {}
            });
            // 分享给好友
            wx.onMenuShareAppMessage({
                title: APPCONF.shareTitle,
                desc: APPCONF.shareDesc,
                link: APPCONF.url,
                imgUrl: APPCONF.shareImgUrl,
                success: function () {},
                cancel: function () {}
            });
            // 分享到qq
            wx.onMenuShareQQ({
                title: APPCONF.shareTitle,
                desc: APPCONF.shareDesc,
                link: APPCONF.url,
                imgUrl: APPCONF.shareImgUrl,
                success: function () {},
                cancel: function () {}
            });
        });

        // 微信api发生错误
        wx.error(function (res) {});
    });
}

/**
 * 手百分享
 */
function initBoxShare() {
    var data = {
        type: 'url',
        mediaType: 'all',
        title: APPCONF.shareTitle, // 分享的标题
        content: APPCONF.shareDesc, // 分享的副标题（描述）
        iconUrl: APPCONF.shareImgUrl, // 分享的图标
        linkUrl: APPCONF.url, // 分享的链接
        source: 'test' // 自定义一个字符串，用统计分享的来源，保证不和其他来源重复
    };
    window['__BdboxShare_success__'] = function () {};
    window['__BdboxShare_fail__'] = function () {};
    window.BoxShareData = {
        options: data,
        successcallback: '__BdboxShare_success__',
        errorcallback: '__BdboxShare_fail__'
    };
}