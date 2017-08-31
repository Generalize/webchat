var login = {};
login.getUserInfoSuccess = function(f){
	const app = getApp();
	wx.request({
		url: 'https://wx.loranda.cn/index/index/login',
		data:{
			iv:f.iv,
			encryptedData:f.encryptedData,
			code:app.globalData.userInfo.code,
			action:'login'
		},
		success(g){
			 
			 
			 
			const app = getApp();
			f.userInfo.token = g.data.token;
			login.userInfo = f.userInfo;
			 
			 
		}
	})
};
login.getUserInfoFail = function(){
wx.getSetting({
		success(res){
			var socpe = res.authSetting['scope.userInfo'];
			if(!socpe){
				wx.showModal({
					title:'授权提醒',
					content:'您拒绝了授权,您将不能发送和接收消息,点击确定打开授权设置界面.',
					success(c){
						if(c.confirm){
							wx.openSetting({
								success(rr){
									if(rr.authSetting['scope.userInfo']){
										wx.getUserInfo({
											withCredentials:true,
											lang:"zh_CN ",
											success:login.getUserInfoSuccess,
										})
										wx.showToast({
											title: '授权成功',
										});
									}
								}
							})
						}
					}
				});
			}
		}
	})
}
wx.login({
	success(e){
		const app = getApp();
		login.code =  e.code;
		wx.getUserInfo({
			withCredentials:true,
			lang:"zh_CN ",
			success:login.getUserInfoSuccess,
			fail:login.getUserInfoFail
		});
	}
});