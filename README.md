
# 微信分享模块

@(微信分享)[微信|手百]

社交类的项目中，需要将活动页面（从微信或手机百度app）分享给微信好友或微信朋友圈。
其中微信app上的网页分享需要计算签名，并且计算签名用到的接口还有访问次数限制，敏感信息又不能放在前端。
所以将该模块提炼出来，以确保运营人员和开发能快速实现可靠的微信分享功能。

----------

模块涉及到三个部分：
- **公众号** - 需要运营人员对公众号进行配置，确保网站能正常运行微信的js_sdk和访问微信的认证接口。
- **后端** - backend文件夹，使用**php**语言，通过发送请求带上参数`backend/?url=xxxx`获取当下最新签名。
- **前端** - fontend文件夹，主要在html头部配置参数，会自动根据不同的环境调用`share.js`中的不同初始化方法。


## 使用教程

### 配置公众号
1.运营人员登陆微信公众号后台。

![](http://source.joekeikun.cc/git/wxshare_1.png)
2.在**公众号设置**栏目中设置**JS接口安全域名**，以确保jsSDK正常运行。(网站的域名必须备案合法，同时需要开发人员将一个认证用的txt文件放置在服务器访问根目录文件夹"/"下)

![](http://source.joekeikun.cc/git/wxshare_2.png)
3.在**基本配置**栏目中设置**IP白名单**。（只有ip白名单中的服务器才可以访问微信accessToken获取接口，需要将开发测试的机器也配置进去，便于本地测试）

4.将图3中的**开发者ID**和**开发者密码**提供给开发人员（记得保密，切勿泄漏）。


### 代码部署
1. 将`backend`文件夹放置到服务器上，可修改成任意接口名字（下文继续称**backend**）。

2. 在**backend**文件夹下新建一个 `config.php` 文件，内容是（为了确保信息安全，头部加入exit()）：
```
<?php exit();?>{"appid":"微信开发者ID","appsecret": "微信开发者密码"}
```

3. 将`frontend`文件夹拷贝到自己的前端项目中。（具体内容可以查看代码注释）
	- `share.js`文件是分享配置函数，如果需要在分享成功后，执行回调函数，可以修改该文件。
	- `index.html`文件包含需要引入的sdk文件和主要处理逻辑，其中`APPCONF`是分享会使用到的一些配置参数（基本上只用修改分享时会用到的`shareTitle`、`shareDesc`、`shareImgUrl`），其他的代码可以不用关心。
	- `initWXShare('/backend')`的`'/backend'`对应后端接口路径。

4. 开发人员将开发机的服务器访问地址修改成运营人员配置的**安全域名**，以确保分享功能正常运行。

5. 在开发机上下载[微信开发工具](https://developers.weixin.qq.com/miniprogram/dev/devtools/download.html "微信开发工具")，在本机上验证分享是否OK。

6. 部署到线上环境。


## 反馈
- Email: <247716557@qq.com>
