//app.js
Array.prototype.each = function(callback,msg){
  for (var i = 0; i < this.length; i++) {
    if (this[i].image) {
      this[i].image = JSON.parse(this[i].image);
    }
    callback(this[i]);
  }
	return this;
}
App({
  onLaunch: function () {
    //调用API从本地缓存中获取数据
    
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)
  },
  globalData:{
    peopleId: new Date().getTime(),
	  userInfo:{},
    p:100
  },
  getUserInfo(cb){
	  
  },
  onShow:function(){
    
  }
  
});