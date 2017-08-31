let userInfo = {};
let url = '';
let socketClock = '';
class Socket {
  constructor(host) {
    this.host = host
    this.connected = false
    wx.connectSocket({
      url: this.host,
    })
    
    // 监听连接成功
    wx.onSocketOpen((res) => {
       
      this.connected = true
    })

    wx.onSocketMessage(function(res){
      let data = JSON.parse(res.data);
      if(data.reason === false){
          userInfo.close = false;
      }
    })

    // 监听连接断开
    wx.onSocketError((res) => {
       
       
      this.connected = false
       
      if (userInfo.close) {
        userInfo.closeBy = 'self';
        wx.closeSocket({});
        let ui = wx.getStorageSync('userInfo')
        wx.connectSocket({
          url: + '&r=' + ui.r,
          complete() {
            userInfo.closeBy = 'noself';
             
            
             
            
            if (ui.r) {
               
              let login = {
                token: ui.token,
                r: ui.r,
                a: 'room',
              };
              wx.sendSocketMessage({
                data: JSON.stringify(login),
                fail: function (res) {
                   
                }
              })
            }
          }
        })
      }
    })

    // 监听连接关闭
    wx.onSocketClose((res) => {
       
       
       
      this.connected = false
       
       
      if (userInfo.close && userInfo.closeBy != 'self'){
        userInfo.closeBy = 'self';
        wx.closeSocket({});
        let ui = wx.getStorageSync('userInfo')
        wx.connectSocket({
          url: this.host + '&r=' + ui.r,
          complete(){
             
            userInfo.closeBy = 'noself';
             
            let ui = wx.getStorageSync('userInfo')
             
            
            if (ui.r) {
               
              let login = {
                token: ui.token,
                r: ui.r,
                a: 'room',
              };
              wx.sendSocketMessage({
                data: JSON.stringify(login),
               fail:function(res){
                   
               }
              })
            }
          }
        })
      }
    })

  }

  sendMessage(data) {
    if(!this.connected){
       
      return
    }
    wx.sendSocketMessage({
      data: JSON.stringify(data)
    })
  }

  test(cb){
    cb(100);
  }

  onMessage(callback) {
    if(typeof(callback) != 'function')
      return
    // 监听服务器消息
    wx.onSocketMessage((res) => {
       
      const data = JSON.parse(res.data)
      callback(data)
    })
  }
}

socketClock = setInterval(function(){
  userInfo = wx.getStorageSync('userInfo');
  if (userInfo) {
    userInfo.close = true;
    url = 'wss://wx.loranda.cn:8989' + '/?token=' + userInfo.token;
    exports.socket = new Socket(url)
     
    clearInterval(socketClock);
  }
},300);

