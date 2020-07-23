{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <title>跳转提示</title>
    <link rel="stylesheet" href="/static/layuiadmin/layui/css/layui.css">
    <style type="text/css">
        .content {
            text-align: center
        }
        .content > p > a {
            color: #5c90d2;
        }
        .content > p > a:hover {
            color: #3169b1;
        }
        .message {
            margin-bottom: 15px;
        }
        .message-success-icon {
            font-size: 30px;
            position:relative;
            top: 3px;
        }
        .message-error-icon {
            font-size: 30px;
            position:relative;
            top: 4px;
        }
        .message-success {
            color: #46c37b;
            font-size: 24px;
        }
        .message-error {
            color: #ff6b6b;
            font-size: 24px;
        }
        .jump {
            color: #646464;
        }
        .layui-layer-btn {
            padding: 0 !important;
        }
    </style>
</head>
<body>
    <script src="/static/layuiadmin/layui/layui.js"></script>
    <script type="text/javascript">
        (function(){
            layui.use('layer', function() {
                var code = {$code},
                    msg  = "{$msg|strip_tags}",
                    wait = {$wait},
                    href = "{$url}",
                    msg_html = '';
              
                switch(code) {
                     case 1:
                        msg_html =  '<span class="message-success"><i class="layui-icon layui-icon-ok-circle message-success-icon"></i> ' + msg + '</span>';
                        break;
                     case 0:
                        msg_html =  '<span class="message-error"><i class="layui-icon layui-icon-close-fill message-error-icon"></i> ' + msg + '</span>';
                        break;
                } 

                var content =   '<div class="content">' + 
                                    '<p class="message">' + msg_html + '</p>' +
                                    '<p class="jump">页面自动 <a target="_top" href="' + href + '">跳转</a> 等待时间： <b id="wait">' + wait + '</b>秒</p>' +
                                '</div>';

                layer.open({
                    content: content,
                    btn: [],
                    title: false,
                    closeBtn: false,
                    time: 0
                });

                var interval = setInterval(function(){
                    var time = --document.getElementById('wait').innerHTML;
                    if(time <= 0) {
                        top.location.href = href;
                        clearInterval(interval);
                    };
                }, 1000);
            });
        })();
    </script>
</body>
</html>