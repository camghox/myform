String.prototype.format = function(){
   var args1 = arguments;
   return this.replace(/\{(\d+)\}/g, function(){
		var args2 = arguments;
		return args1[args2[1]];
   });
}
String.prototype.isEmpty = function(){
    var str = this.replace(/(^\s*)|(\s*$)/g, "");
    if(str === '' || str.length === 0) return true;
    return false;
}
String.prototype.isMobile = function() 
   { 
       if(this.length==0) 
       {
          return false; 
       }     
       if(this.length!=11) 
       {
           return false;
       } 
        
       var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/; 
       if(!myreg.test(this)) 
       { 
           return false; 
       }
       return true;
   }
/**
 * var time1 = new Date().Format(date, "yyyy-MM-dd");
 * var time2 = new Date().Format(date, "yyyy-MM-dd HH:mm:ss"); 
 */
Date.prototype.Format = function (fmt) { //author: meizz 
    var o = {
        "M+": this.getMonth() + 1, //月份 
        "d+": this.getDate(), //日 
        "h+": this.getHours(), //小时 
        "m+": this.getMinutes(), //分 
        "s+": this.getSeconds(), //秒 
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
        "S": this.getMilliseconds() //毫秒 
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}
Date.prototype.Tomorrow = function () { //author: meizz 
    this.setDate(this.getDate() + 1);
    return this;
}