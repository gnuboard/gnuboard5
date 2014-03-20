/**************************************************************************************
 * jQuery Paging 0.1.7
 * by composite (ukjinplant@msn.com)
 * http://composite.tistory.com
 * This project licensed under a MIT License.
 **************************************************************************************/;
(function($){
	//default properties.
	var a=/a/i,defs={
		liitem:'li', item:'a',next:'[&gt;{5}]',prev:'[{4}&lt;]',format:'[{0}]',
		itemClass:'',appendhtml:'',sideClass:'paging-side',prevClass:'paging-side',
		itemCurrent:'active',length:10,max:1,current:1,append:false
		,href:'#{0}',event:true,first:'[1&lt;&lt;]',last:'[&gt;&gt;{6}]'
	},InStr=function(strSearch, charSearchFor) {
        return strSearch.indexOf(charSearchFor);
    },format=function(str){
		var arg=arguments;
		return str.replace(/\{(\d+)\}/g,function(m,d){
			if(+d<0) return m;
			else return arg[+d+1]||"";
		});
	},item,make=function(op,page,cls,str){
        var is_current = false;
        if( InStr( cls , op.itemCurrent) > -1 ){
            item=document.createElement("strong");
            is_current = true;
        } else {
            item=document.createElement(op.item);
        }
		item.className=cls;
		item.innerHTML=format(str,page,op.length,op.start,op.end,op.start-1,op.end+1,op.max);
		if(a.test(op.item)) item.href=format(op.href,page);
		if(op.event){
			$(item).bind('click',function(e){
				var fired=true;
				if($.isFunction(op.onclick)) fired=op.onclick.call(item,e,page,op);
				if(fired==undefined||fired)
					op.origin.paging($.extend({},op,{current:page}));
				return fired;
			});
            /*
			$liitem= $(document.createElement(op.liitem));
			$liitem.addClass(cls);
			$liitem.append($(item));
            */
            if(op.appendhtml){
                $(item).append(op.appendhtml);
            }
			$(item).appendTo(op.origin);
            if( is_current ){
                $(item).prepend('<span class="sound_only">열린</span>');
            } else {
                (op.origin).append('\n');
            }
			//bind event for each elements.
			var ev='on';
			switch(str){
				case op.prev:ev+='prev';break;
				case op.next:ev+='next';break;
				case op.first:ev+='first';break;
				case op.last:ev+='last';break;
				default:ev+='item';break;
			}
			if($.isFunction(op[ev])) op[ev].call(item,page,op);
		}
		return item;
	};

	$.fn.paging=function(op){
		op=$.extend({origin:this},defs,op||{});this.html('');
		if(op.max<1) op.max=1; if(op.current<1) op.current=1;
		op.start=Math.floor((op.current-1)/op.length)*op.length+1;
		op.end=op.start-1+op.length;
		if(op.end>op.max) op.end=op.max;
		if(!op.append) this.empty();
		//prev button
		if(op.current>op.length){
			//if(op.first!==false) make(op,1,op.sideClass,op.first);
			make(op,op.start-1,op.prevClass,op.prev);
		}
		//pages button
		for(var i=op.start;i<=op.end;i++){
			make(op,i,op.itemClass+(i==op.current?' '+op.itemCurrent:''),op.format);
		}
		//next button
		if(op.current<=Math.floor(op.max/op.length)*op.length){
			if(op.max > op.length && op.max > op.end ){ make(op,op.end+1,op.sideClass,op.next); }
			//if(op.last!==false) make(op,op.max,op.sideClass,op.last);
		}
			
		//last button
	};
})(jQuery);