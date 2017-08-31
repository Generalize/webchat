const app = getApp();
import { formatTime } from '../../utils/util'
import { socket } from '../../utils/socket.js'
let userInfo = {};
let r = '';
let roomlist = {};
let animation = {};
let search = '';
var a = 100000;
let config = {
  data: {
    list: [],
    animation:{}
  },
  login:function(userInfo){
    wx.login({
      success(e) {
        wx.getUserInfo({
          withCredentials: true,
          lang: "zh_CN ",
          success(f) {
            userInfo = f.userInfo;
            wx.request({
              url: 'https://wx.loranda.cn/index/index/login',
              data: {
                iv: f.iv,
                encryptedData: f.encryptedData,
                code: e.code,
                action: 'login'
              },
              success(g) {
                userInfo.token = g.data.token;
                wx.setStorage({
                  key: 'userInfo',
                  data: userInfo,
                })
              }
            })
          },
          fail() {
            wx.getSetting({
              success(res) {
                var socpe = res.authSetting['scope.userInfo'];
                if (!socpe) {
                  wx.showModal({
                    title: '授权提醒',
                    content: '您拒绝了授权,您将不能发送和接收消息,点击确定打开授权设置界面.',
                    success(c) {
                      if (c.confirm) {
                        wx.openSetting({
                          success(rr) {
                            if (rr.authSetting['scope.userInfo']) {
                              wx.getUserInfo({
                                withCredentials: true,
                                lang: "zh_CN ",
                                success(f) {
                                  wx.request({
                                    url: 'https://wx.loranda.cn/index/index/login',
                                    data: {
                                      iv: f.iv,
                                      encryptedData: f.encryptedData,
                                      code: e.code,
                                      action: 'login'
                                    },
                                    success(g) {
                                      f.userInfo.token = g.data.token;
                                      wx.setStorage({
                                        key: 'userInfo',
                                        data: f.userInfo,
                                      })

                                    }
                                  })
                                },
                              })
                              wx.showToast({
                                title: '授权成功',
                              });
                            }else{
                              wx.reLaunch({
                                url: '/pages/index/index',
                              })
                            }
                          }
                        })
                      } else if (c.cancel) {
                        wx.reLaunch({
                          url: '/pages/index/index',
                        })
                      }
                    }
                  });
                }
              }
            })
          }
        });
      }
    });
  },
  onShow:function(){
    this.login(userInfo);
  },
  onLoad() {
    this.login(userInfo);
    var _this = this;
    var clock = setInterval(function () {
      userInfo = wx.getStorageSync('userInfo');
      if (userInfo.token) {
        wx.request({
          url: 'https://wx.loranda.cn/index/index/roomlist',
          data: {
            token: userInfo.token,
          },
          success(res) {
            clearInterval(clock);
            res.data.forEach(function (d) {
              d.updated = formatTime(d.updated);            
            });
            roomlist = res.data;
            _this.setData({
              list: roomlist
            })
            // wx.setEnableDebug({
            //   enableDebug:true
            // });
            // if(!wx.getStorageSync('first')){
            //   wx.setStorageSync('first', 100);
            //   wx.setEnableDebug({
            //     enableDebug: true,
            //   })
            // }
            
          }
        })
      }else{
        return;
      }
    }, 500);

  },
  onShow() {

  },
  goInRoom: function (e) {
     
    let name = e.currentTarget.dataset.name;
    r = e.currentTarget.dataset.id;
    roomlist.forEach(function (d) {
      if (d.r == r){
        d.count = 0;
      }
    })
    this.setData({
      list:roomlist
    });
    userInfo.r = r;
    wx.setStorageSync('userInfo', userInfo);
    socket.onMessage(function (data) {
      if (data.m == 'room success') {
        wx.navigateTo({  //进入房间
          url: '../message/message?r=' + r + '&rn=' + name,
        })
      }
    })

    socket.sendMessage({
      token: userInfo.token,
      r: r,
      a: 'room',
    })
  },
  onHide: function () {

  },
  onShow: function (e) {  //退出房间
    if (!wx.getStorageSync(r)) {
      return;
    }

    socket.onMessage(function (data) {
      if (data.m == 'exit success') {
        r = '';
      }
    })
    socket.sendMessage({
      token: userInfo.token,
      r: r,
      a: 'exitRoom'
    })
    wx.removeStorageSync(r);
  },
  cencel_sub:function(){
    let add_display = "display:none";
    this.setData({
      add_display: add_display,
      roomname: '',
    })
  },
  addroom:function(){
    let  add_display = "display:block";
    this.setData({
      add_display: add_display
    })
  },
  formSubmit:function(res){
    let _this = this;
    let add_display = "display:none";
    this.setData({
      add_display: add_display,
      roomname:'',
    })
    let name = res.detail.value.name;
    wx.request({
      url: 'https://wx.loranda.cn/index/index/addRoom',
      data:{
        name:name,
        token:userInfo.token
      },
      success:function(res){
         
        if(res.data.m == 'success'){
          wx.showToast({
            title: '添加成功',
            icon:'success',
            duration:1000,
            success:function(){
              wx.request({
                url: 'https://wx.loranda.cn/index/index/roomlist',
                data:{
                  token:userInfo.token
                },
                success:function(res){
                  roomlist = res.data;
                  _this.setData({
                    list: roomlist
                  });
                }
              })
            }
          })
        }
      }
    })
  },
  search_input:function(e){
      search = e.detail.value;
  },
  searchroom:function(){
    if(search){
      wx.navigateTo({
        url: '/pages/search/search?search=' + search + '&token=' + userInfo.token,
      })
    }
  }
};
let pageClock = setInterval(function(){
    if(userInfo){
      clearInterval(pageClock);
    }
    userInfo = wx.getStorageInfoSync('userInfo');
},200)

Page(config);