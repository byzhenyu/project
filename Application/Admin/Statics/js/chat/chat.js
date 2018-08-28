//-- 聊天代码 -- by zhaojiping QQ: 17620286 --//


WebIM.Emoji = {
    path: _EMOJI_PATH,
    map: {
        '[):]': 'ee_1.png',
        '[:D]': 'ee_2.png',
        '[;)]': 'ee_3.png',
        '[:-o]': 'ee_4.png',
        '[:p]': 'ee_5.png',
        '[(H)]': 'ee_6.png',
        '[:@]': 'ee_7.png',
        '[:s]': 'ee_8.png',
        '[:$]': 'ee_9.png',
        '[:(]': 'ee_10.png',
        '[:\'(]': 'ee_11.png',
        '[:|]': 'ee_12.png',
        '[(a)]': 'ee_13.png',
        '[8o|]': 'ee_14.png',
        '[8-|]': 'ee_15.png',
        '[+o(]': 'ee_16.png',
        '[<o)]': 'ee_17.png',
        '[|-)]': 'ee_18.png',
        '[*-)]': 'ee_19.png',
        '[:-#]': 'ee_20.png',
        '[:-*]': 'ee_21.png',
        '[^o)]': 'ee_22.png',
        '[8-)]': 'ee_23.png',
        '[(|)]': 'ee_24.png',
        '[(u)]': 'ee_25.png',
        '[(S)]': 'ee_26.png',
        '[(*)]': 'ee_27.png',
        '[(#)]': 'ee_28.png',
        '[(R)]': 'ee_29.png',
        '[({)]': 'ee_30.png',
        '[(})]': 'ee_31.png',
        '[(k)]': 'ee_32.png',
        '[(F)]': 'ee_33.png',
        '[(W)]': 'ee_34.png',
        '[(D)]': 'ee_35.png'
    }
}


var conn = new WebIM.connection({
    https: WebIM.config.https,
    url: WebIM.config.xmppURL,
    isAutoLogin: WebIM.config.isAutoLogin,
    isMultiLoginSessions: WebIM.config.isMultiLoginSessions
});


var options = { 
    apiUrl: WebIM.config.apiURL,
    user: _MY_INFO['wechat_username'],
    pwd: _MY_INFO['wechat_password'],
    appKey: _wechat['org_name'] + "#" + _wechat['app_name']
};
conn.open(options);

conn.listen({
    onOpened: function ( message ) {          //连接成功回调
    //如果isAutoLogin设置为false，那么必须手动设置上线，否则无法收消息
        onOpened(message);
        conn.setPresence();             
    },  
    onClosed: function ( message ) { onClosed(message); },         //连接关闭回调
    onTextMessage: function ( message ) { onTextMessage(message); },    //收到文本消息
    onEmojiMessage: function ( message ) { onEmojiMessage(message); },   //收到表情消息
    onPictureMessage: function ( message ) { onPictureMessage(message);}, //收到图片消息
    onCmdMessage: function ( message ) { onOther(message); },     //收到命令消息
    onAudioMessage: function ( message ) { onOther(message); },   //收到音频消息
    onLocationMessage: function ( message ) { onOther(message); },//收到位置消息
    onFileMessage: function ( message ) { onOther(message); },    //收到文件消息
    onVideoMessage: function ( message ) { onOther(message); },   //收到视频消息
    onPresence: function ( message ) { onOther(message); },       //收到联系人订阅请求、处理群组、聊天室被踢解散等消息
    onRoster: function ( message ) { onOther(message); },         //处理好友申请
    onInviteMessage: function ( message ) { onOther(message); },  //处理群组邀请
    onOnline: function () { onOnline(); },                  //本机网络连接成功
    onOffline: function () { onOffline() ;},                 //本机网络掉线
    onError: function ( message ) { onError(message);}           //失败回调
});


function status(info){
    LL(info);
}

function LL(obj){
    console.log(obj);
}

function onOnline(){
    status('本机网络连接成功');
}

function onOffline(message){
    status('本机网络掉线');
}

function onError(message){
    LL(message);
}

function onOther(message){
    LL(message);
}

function encode(str) {
    if (!str || str.length === 0) {
        return '';
    }
    var s = '';
    s = str.replace(/&amp;/g, "&");
    s = s.replace(/<(?=[^o][^)])/g, "&lt;");
    s = s.replace(/>/g, "&gt;");
    s = s.replace(/\"/g, "&quot;");
    s = s.replace(/\n/g, "<br>");
    return s;
}


function onOpened(message){
    layer.closeAll();
    status("连接成功");
}

function onClosed(message){
    LL(message);
}

function getbigimg(obj) {
    // LL(obj);
    var html = $(obj).next().find('.message_image_big').css({
            'text-align': 'center',
            'line-height': '600px',
            'height': '600px',
            'width': '800px',
        }).parent().html();
    //LL(html);
    var index = parent.layer.open({
        type: 1,
        title: false,
        closeBtn: 1,
        area: ['800px', '600px'],
        shade: 0.3,
        shadeClose: true,
        content: html
    });

}

// 光标处插入内容
function insertText(obj,str) {
    if (document.selection) {
        var sel = document.selection.createRange();
        sel.text = str;
    } else if (typeof obj.selectionStart === 'number' && typeof obj.selectionEnd === 'number') {
        var startPos = obj.selectionStart,
            endPos = obj.selectionEnd,
            cursorPos = startPos,
            tmpStr = obj.value;
        obj.value = tmpStr.substring(0, startPos) + str + tmpStr.substring(endPos, tmpStr.length);
        cursorPos += str.length;
        obj.selectionStart = obj.selectionEnd = cursorPos;
    } else {
        obj.value += str;
    }
}

// 时间格式化
//格式化CST日期的字串
function formatCSTDate(strDate,format){
    return formatDate(new Date(strDate),format);
}

//格式化日期,
function formatDate(date,format){
    var paddNum = function(num){
        num += "";
        return num.replace(/^(\d)$/,"0$1");
    }
    //指定格式字符
    var cfg = {
        yyyy : date.getFullYear() //年 : 4位
        ,yy : date.getFullYear().toString().substring(2)//年 : 2位
        ,M  : date.getMonth() + 1  //月 : 如果1位的时候不补0
        ,MM : paddNum(date.getMonth() + 1) //月 : 如果1位的时候补0
        ,d  : date.getDate()   //日 : 如果1位的时候不补0
        ,dd : paddNum(date.getDate())//日 : 如果1位的时候补0
        ,hh : date.getHours()  //时
        ,mm : date.getMinutes() //分
        ,ss : date.getSeconds() //秒
    }
    format || (format = "yyyy-MM-dd hh:mm:ss");
    return format.replace(/([a-z])(\1)*/ig,function(m){return cfg[m];});
} 