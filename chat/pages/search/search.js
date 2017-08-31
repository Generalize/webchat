// search.js
let search = '';
let current = '';
let token = '';
Page({

  /**
   * 页面的初始数据
   */
  data: {
  
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    token = options.token;
    let _this = this;
      search = options.search;
      wx.request({
        url: 'https://wx.loranda.cn/index/index/searchRoom',
        data:{
          search:search,
          token:token
        },
        success:function(res){
          _this.setData({
            list:res.data,
            search:search
          })
        }
      })
  },
  search_input: function (e) {
    search = e.detail.value;
  },
  searchroom: function () {
    let _this = this;
    if (search) {
      wx.request({
        url: 'https://wx.loranda.cn/index/index/searchRoom',
        data: {
          search: search,
          token:token
        },
        success: function (res) {
          _this.setData({
            list: res.data
          })
        }
      })
    }
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  },
  joinRoom:function(res){
    let id = res.currentTarget.dataset.id;
    wx.showModal({
      title: '你确定要加入这个房间吗',
      success:function(res){
        if (res.confirm){
          wx.request({
            url: 'https://wx.loranda.cn/index/index/joinRoom',
            data:{
              room:id,
              token:token
            },
            success:function(res){
              if(res.data.m == 'success'){
                wx.reLaunch({
                  url: '/pages/index/index'
                })
              }
            }
          })
        }
      }
    })
  }
})