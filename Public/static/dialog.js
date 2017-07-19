var dialog={
    // 错误弹出层
    error:function (message) {
        layer.open({
            content:message,
            icon:2,
            title:'错误提示',
        });
    },
    //成功弹出层
    success : function(message,url) {
        layer.open({
            content : message,
            icon : 1,
            yes : function(){
                location.href=url;
            },
        });
    },

    // 确认弹出层
    confirm : function(message, url) {
        layer.open({
            content : message,
            icon:3,
            btn : ['是','否'],
            yes : function(){
                location.href=url;
            },
        });
    },

    //无需跳转到指定页面的确认弹出层
    toconfirm : function(message) {
        layer.open({
            content : message,
            icon:3,
            btn : ['确定'],
        });
    },
    //下落式弹出层
    fall : function(title, content) {
        layer.open({
            type: 1 //Page层类型
            ,area: ['88%', '25%']
            ,title: title
            ,shade: 0.6 //遮罩透明度
            ,maxmin: true //允许全屏最小化
            ,anim: 1 //0-6的动画形式，-1不开启
            ,content: content
        });
    },
}