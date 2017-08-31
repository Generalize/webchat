import { socket } from '../../utils/socket.js'
import { formatTime } from '../../utils/util'
const app = getApp()
let userInfo = {};
let thisPage = {};
let r = '';
let animation1 = {};
let animation2 = {};
let animation3 = {};
let animation4 = {};
let query = {};
let imgs = [];
let location = '';
let audio = '';
let textMsg = '';
let audioCtl = [];
let voice_path = '';
let voice = true;
app.getUserInfo()
let msgConfig = {
  data: {
    messages: [],
    plus: true,
    animation1: {},
    animation2: {},
    animation3: {},
    animation4: {},
    voice_src: '/images/voice.svg',
    voice: voice,
    voice_click: '按住说话',
  },
  onLoad(options) {
    wx.setNavigationBarTitle({
      title: options.rn,
    });
    r = options.r;
    let _this = this;
    userInfo = wx.getStorageSync('userInfo');
    thisPage.msg = [];
    thisPage.page = 0;
    socket.sendMessage(
      {
        token: userInfo.token,
        r: r,
        a: 'showMsg',
        page: 0
      }
    );
    socket.onMessage(function (data) {
      let scroolData = {};
      let length = 0;
      let height = 0;
      query = wx.createSelectorQuery()
      if (data instanceof Array) {
        length = thisPage.msg.length;
        data.each(function (d) {
          thisPage.msg.unshift(d);
        }, thisPage.msg);

        thisPage.page++;
        wx.setStorageSync(r, thisPage);
        if (!length) {
          scroolData = {
            messages: thisPage.msg,
            scroll_top: 10000
          }
        } else {
          scroolData = {
            messages: thisPage.msg,
            //scroll_top: 554
          }
        }
        _this.setData(scroolData);
        thisPage.msg.forEach(function (d) {
          if (d.audio) {
            let ctl = {
              src: d.audio,
              ctl: wx.createAudioContext(d.audio)
            }
            audioCtl.push(ctl);
          }
        })
        console.log(audioCtl)
      } else if (data.isMsg) {
        thisPage.msg.push(data);
        if (data.audio) {
          let ctl = {
            src: data.audio,
            ctl: wx.createAudioContext(data.audio)
          }
          audioCtl.push(ctl);
        }
          
        wx.setStorageSync(r, thisPage);
        _this.setData({
          messages: thisPage.msg,
          msg: '',
          scroll_top: 100000
        });
      }
    });
    animation1 = wx.createAnimation({
      duration: 100
    });
    animation2 = wx.createAnimation({
      duration: 100
    });
    animation3 = wx.createAnimation({
      duration: 100
    });
    animation4 = wx.createAnimation({
      duration: 100
    });
  },
  onReady() {
  },
  more: function () {

    animation1.height('65%').step();
    animation2.bottom('30%').step();
    animation3.bottom(0).step();
    this.setData({
      animation1: animation1.export(),
      animation2: animation2.export(),
      animation3: animation3.export(),
    })
  },
  nomore: function () {
    animation1.height('calc(100% - 150rpx)').step();
    animation2.bottom('10rpx').step();
    animation3.bottom("-30%").step();
    this.setData({
      animation1: animation1.export(),
      animation2: animation2.export(),
      animation3: animation3.export(),
    })
  },
  sendMsg: function () {
    if (!textMsg) {
      wx.showModal({
        title: '请输入文字或选择图片',
        content: '不能发送空内容',
        showCancel: false
      })
      return;
    }
    let msg = {
      a: 'send',
      token: userInfo.token,
      r: r,
      text: textMsg,
      image: '',
      location: '',
      audio: '',
    };
    textMsg = '';
    socket.sendMessage(msg);
    animation1.height('90%').step();
    animation2.bottom(0).step();
    animation3.bottom("-30%").step();
    this.setData({
      animation1: animation1.export(),
      animation2: animation2.export(),
      animation3: animation3.export(),
    })
  },
  getMore: function (e) {

    socket.sendMessage(
      {
        token: userInfo.token,
        r: r,
        a: 'showMsg',
        page: thisPage.page
      }
    );
  },
  getInput: function (e) {
    textMsg = e.detail.value
  },
  chooseImg: function (e) {
    let _this = this;
    wx.chooseImage({
      sizeType: 'original',
      success: function (res) {
        //res.tempFilePaths;
        let i = 0;
        let imgArr = res.tempFilePaths;
        let imgMsg = [];

        (function () {

          wx.uploadFile({
            url: 'https://wx.loranda.cn/index/index/upload',
            formData: {
              token: userInfo.token,
              r: r
            },
            filePath: imgArr[i],
            name: 'image',
            success: function (res) {
              imgMsg.push({ 'no': i, 'src': res.data })
              i++
              if (imgArr[i]) {
                arguments.callee();
              } else {
                wx.hideLoading()
                let msg = {
                  a: 'send',
                  token: userInfo.token,
                  r: r,
                  text: '',
                  image: imgMsg,
                  location: '',
                  audio: '',
                };
                // 
                socket.sendMessage(msg)
              }
            },
            fail: function () {
              i++;
              if (imgArr[i]) {
                arguments.callee();
              } else {
                wx.hideLoading()
              }
            }
          })
        })()
        wx.showLoading({
          title: '上传图片中...',
          mask: true
        });
        animation1.height('90%').step();
        animation2.bottom(0).step();
        animation3.bottom("-30%").step();
        _this.setData({
          animation1: animation1.export(),
          animation2: animation2.export(),
          animation3: animation3.export(),
        })
      },
    })
  },
  change_voice: function (e) {
    wx.authorize({
      scope: 'scope.record',
    })
    if (voice) {
      voice = false;
      this.setData({
        voice: voice,
        voice_src: '/images/keyboard.svg'
      })
    } else {
      voice = true;
      this.setData({
        voice: voice,
        voice_src: '/images/voice.svg'
      })
    }
  },
  voice_start: function () {
    console.log(audioCtl);
    wx.getSetting({
      success: function (res) {
        if (!res.authSetting['scope.record']) {
          wx.showModal({
            title: '没有权限',
            showCancel: false
          })
          return;
        }
      }
    })
    this.setData({
      voice_click: '松开结束'
    })
    wx.startRecord({
      success: function (res) {
        voice_path = res.tempFilePath;
      }
    })

  },
  voice_end: function () {
    this.setData({
      voice_click: '按住说话'
    })
    wx.stopRecord();
    this.setData({
      voice: true,
      voice_src: '../../images/voice.svg'
    })
    console.log(voice_path);
    let clock = setInterval(function () {
      console.log(10000);
      if (voice_path) {
        clearInterval(clock);
        console.log(voice_path)
        wx.uploadFile({
          url: 'https://wx.loranda.cn/index/index/upload',
          filePath: voice_path,
          name: 'audio',
          formData: {
            token: userInfo.token,
            r: r
          },
          success: function (res) {
            console.log(res.data);
            if (res.data) {
              let msg = {
                a: 'send',
                token: userInfo.token,
                r: r,
                text: '',
                image: '',
                location: '',
                audio: res.data,
              };
              socket.sendMessage(msg)
            }
          }
        })
      }
    }, 300)

  },
  play_voice:function(e){
    console.log(e);
    let cuttentPath = e.currentTarget.dataset.voice;
    if (e.currentTarget.id == 's'){
      audioCtl.forEach(function(d){
        if (d.src == cuttentPath){
          d.ctl.play();
        }
      })
    } else if(e.currentTarget.id == 'd'){
      console.log(3535435345);
      wx.stopBackgroundAudio()
    }
  },
  start_play:function(e){
    console.log(e);
    let cuttentPath = e.currentTarget.dataset.voice;
    xor(cuttentPath);
    this.setData({
      messages:thisPage.msg
    });
  },
  end_play:function(e){
    let cuttentPath = e.currentTarget.dataset.voice;
    xor(cuttentPath);
    this.setData({
      messages: thisPage.msg
    });
  },
  onFocus:function(){
    animation2.bottom("3%").step();
    this.setData({
      animation2: animation2.export(),
    });
  },
  onBlur:function(){
      animation1.height('calc(100% - 150rpx)').step();
      animation2.bottom('10rpx').step();
      animation3.bottom("-30%").step();
      this.setData({
        animation1: animation1.export(),
        animation2: animation2.export(),
        animation3: animation3.export(),
      })
  }
};
Page(msgConfig);

function xor(path) {
  thisPage.msg.forEach(function (d) {
    if (d.audio == path) {
      d.play = !d.play;
    }
  })
}