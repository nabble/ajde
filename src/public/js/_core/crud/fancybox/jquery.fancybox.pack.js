(function(e,t,n,r){"use strict";var i=n(e),s=n(t),o=n.fancybox=function(){o.open.apply(this,arguments)},u=!1,a=t.createTouch!==r,f=function(e){return e&&e.hasOwnProperty&&e instanceof n},l=function(e){return e&&n.type(e)==="string"},c=function(e){return l(e)&&e.indexOf("%")>0},h=function(e){return e&&(!e.style.overflow||e.style.overflow!=="hidden")&&(e.clientWidth&&e.scrollWidth>e.clientWidth||e.clientHeight&&e.scrollHeight>e.clientHeight)},p=function(e,t){return t&&c(e)&&(e=o.getViewport()[t]/100*parseInt(e,10)),Math.ceil(e)},d=function(e,t){return p(e,t)+"px"};n.extend(o,{version:"2.0.6",defaults:{padding:15,margin:20,width:800,height:600,minWidth:100,minHeight:100,maxWidth:9999,maxHeight:9999,autoSize:!0,autoHeight:!1,autoWidth:!1,autoResize:!a,autoCenter:!a,fitToView:!0,aspectRatio:!1,topRatio:.5,fixed:!1,scrolling:"auto",wrapCSS:"",arrows:!0,closeBtn:!0,closeClick:!1,nextClick:!1,mouseWheel:!0,autoPlay:!1,playSpeed:3e3,preload:3,modal:!1,loop:!0,ajax:{dataType:"html",headers:{"X-fancyBox":!0}},iframe:{scrolling:"auto",preload:!0},swf:{wmode:"transparent",allowfullscreen:"true",allowscriptaccess:"always"},keys:{next:{13:"right",34:"down",39:"right",40:"down"},prev:{8:"left",33:"up",37:"left",38:"up"},close:[27],play:[32],toggle:[70]},index:0,type:null,href:null,content:null,title:null,tpl:{wrap:'<div class="fancybox-wrap"><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>',image:'<img class="fancybox-image" src="{href}" alt="" />',iframe:'<iframe id="fancybox-frame{rnd}" name="fancybox-frame{rnd}" class="fancybox-iframe" frameborder="0" vspace="0" hspace="0"'+(n.browser.msie?' allowtransparency="true"':"")+"></iframe>",error:'<p class="fancybox-error">The requested content cannot be loaded.<br/>Please try again later.</p>',closeBtn:'<div title="Close" class="fancybox-item fancybox-close"></div>',next:'<a title="Next" class="fancybox-nav fancybox-next"><span></span></a>',prev:'<a title="Previous" class="fancybox-nav fancybox-prev"><span></span></a>'},openEffect:"fade",openSpeed:250,openEasing:"swing",openOpacity:!0,openMethod:"zoomIn",closeEffect:"fade",closeSpeed:250,closeEasing:"swing",closeOpacity:!0,closeMethod:"zoomOut",nextEffect:"elastic",nextSpeed:250,nextEasing:"swing",nextMethod:"changeIn",prevEffect:"elastic",prevSpeed:250,prevEasing:"swing",prevMethod:"changeOut",helpers:{overlay:{speedIn:0,speedOut:250,opacity:.8,css:{cursor:"pointer"},closeClick:!0},title:{type:"float"}},onCancel:n.noop,beforeLoad:n.noop,afterLoad:n.noop,beforeShow:n.noop,afterShow:n.noop,beforeChange:n.noop,beforeClose:n.noop,afterClose:n.noop},group:{},opts:{},previous:null,coming:null,current:null,isActive:!1,isOpen:!1,isOpened:!1,wrap:null,skin:null,outer:null,inner:null,player:{timer:null,isActive:!1},ajaxLoad:null,imgPreload:null,transitions:{},helpers:{},open:function(e,t){if(!e)return;return t=n.isPlainObject(t)?t:{},o.close(!0),n.isArray(e)||(e=f(e)?n(e).get():[e]),n.each(e,function(i,s){var u={},a,c,h,p,d,v,m;n.type(s)==="object"&&(s.nodeType&&(s=n(s)),f(s)?(u={href:s.attr("href"),title:s.attr("title"),isDom:!0,element:s},n.metadata&&n.extend(!0,u,s.metadata())):u=s),a=t.href||u.href||(l(s)?s:null),c=t.title!==r?t.title:u.title||"",h=t.content||u.content,p=h?"html":t.type||u.type,!p&&u.isDom&&(p=s.data("fancybox-type"),p||(d=s.prop("class").match(/fancybox\.(\w+)/),p=d?d[1]:null)),l(a)&&(p||(o.isImage(a)?p="image":o.isSWF(a)?p="swf":a.match(/^#/)?p="inline":l(s)&&(p="html",h=s)),p==="ajax"&&(v=a.split(/\s+/,2),a=v.shift(),m=v.shift())),h||(p==="inline"?a?h=n(l(a)?a.replace(/.*(?=#[^\s]+$)/,""):a):u.isDom&&(h=s):p==="html"?h=a:!p&&!a&&u.isDom&&(p="inline",h=s)),n.extend(u,{href:a,type:p,content:h,title:c,selector:m}),e[i]=u}),o.opts=n.extend(!0,{},o.defaults,t),t.keys!==r&&(o.opts.keys=t.keys?n.extend({},o.defaults.keys,t.keys):!1),o.group=e,o._start(o.opts.index||0)},cancel:function(){var e=o.coming;if(!e||!1===o.trigger("onCancel"))return;o.hideLoading(),e.wrap&&e.wrap.stop().trigger("onReset").remove(),o.coming=null,o.ajaxLoad&&o.ajaxLoad.abort(),o.ajaxLoad=null,o.imgPreload&&(o.imgPreload.onload=o.imgPreload.onerror=null)},close:function(e){o.cancel();if(!o.current||!1===o.trigger("beforeClose"))return;o.unbindEvents(),!o.isOpen||e===!0?(n(".fancybox-wrap").stop().trigger("onReset").remove(),o._afterZoomOut()):(o.isOpen=o.isOpened=!1,o.isClosing=!0,n(".fancybox-item, .fancybox-nav").remove(),o.wrap.stop(!0).removeClass("fancybox-opened"),o.wrap.css("position")==="fixed"&&o.wrap.css(o._getPosition(!0)),o.transitions[o.current.closeMethod]())},play:function(e){var t=function(){clearTimeout(o.player.timer)},r=function(){t(),o.current&&o.player.isActive&&(o.player.timer=setTimeout(o.next,o.current.playSpeed))},i=function(){t(),n("body").unbind(".player"),o.player.isActive=!1,o.trigger("onPlayEnd")},s=function(){o.current&&(o.current.loop||o.current.index<o.group.length-1)&&(o.player.isActive=!0,n("body").bind({"afterShow.player onUpdate.player":r,"onCancel.player beforeClose.player":i,"beforeLoad.player":t}),r(),o.trigger("onPlayStart"))};e===!0||!o.player.isActive&&e!==!1?s():i()},next:function(e){l(e)||(e="down"),o.current&&o.jumpto(o.current.index+1,e,"next")},prev:function(e){l(e)||(e="up"),o.current&&o.jumpto(o.current.index-1,e,"prev")},jumpto:function(e,t,n){var i=o.current;if(!i)return;e=parseInt(e,10),o.direction=t||(e>i.index?"right":"left"),o.router=n||"jumpto",i.loop&&(e<0&&(e=i.group.length+e%i.group.length),e%=i.group.length),i.group[e]!==r&&(o.cancel(),o._start(e))},reposition:function(e,t){var n;o.isOpen&&(n=o._getPosition(t),e&&e.type==="scroll"?(delete n.position,o.wrap.stop(!0,!0).animate(n,200)):o.wrap.css(n))},update:function(e){var t=!e||e&&e.type==="orientationchange",n=e&&e.type==="scroll";t&&(clearTimeout(u),u=null);if(!o.isOpen||u)return;t&&a&&(o.wrap.removeAttr("style").addClass("fancybox-tmp"),o.trigger("onUpdate")),u=setTimeout(function(){var r=o.current;u=null;if(!r)return;o.wrap.removeClass("fancybox-tmp");if(r.autoResize&&!n||t)o._setDimension(),o.trigger("onUpdate");(r.autoCenter&&(!n||!r.canShrink)||t)&&o.reposition(e),o.trigger("onUpdate")},t?20:300)},toggle:function(e){o.isOpen&&(o.current.fitToView=n.type(e)==="boolean"?e:!o.current.fitToView,o.update())},hideLoading:function(){s.unbind("keypress.fb"),n("#fancybox-loading").remove()},showLoading:function(){var e,t;o.hideLoading(),s.bind("keypress.fb",function(e){(e.which||e.keyCode)===27&&(e.preventDefault(),o.cancel())}),e=n('<div id="fancybox-loading"><div></div></div>').click(o.cancel).appendTo("body"),o.coming&&!o.coming.fixed&&(t=o.getViewport(),e.css({position:"absolute",top:t.h*.5+t.y,left:t.w*.5+t.x}))},getViewport:function(){return{x:i.scrollLeft(),y:i.scrollTop(),w:a&&e.innerWidth?e.innerWidth:i.width(),h:a&&e.innerHeight?e.innerHeight:i.height()}},unbindEvents:function(){o.wrap&&o.wrap.unbind(".fb"),s.unbind(".fb"),i.unbind(".fb")},bindEvents:function(){var e=o.current,t;if(!e)return;i.bind("resize.fb orientationchange.fb"+(e.autoCenter&&!e.fixed?" scroll.fb":""),o.update),t=e.keys,t&&s.bind("keydown.fb",function(i){var s=i.which||i.keyCode,u=i.target||i.srcElement;!i.ctrlKey&&!i.altKey&&!i.shiftKey&&!i.metaKey&&(!u||!u.type&&!n(u).is("[contenteditable]"))&&n.each(t,function(t,u){if(e.group.length>1&&u[s]!==r)return o[t](u[s]),i.preventDefault(),!1;if(n.inArray(s,u)>-1)return o[t](),i.preventDefault(),!1})}),n.fn.mousewheel&&e.mouseWheel&&o.wrap.bind("mousewheel.fb",function(t,r,i,s){var u=t.target||null,a=n(u),f=!1;while(a.length){if(f||a.is(".fancybox-skin")||a.is(".fancybox-wrap"))break;f=h(a[0]),a=n(a).parent()}r!==0&&!f&&(o.group.length>1&&!e.canShrink?(s>0||i>0?o.prev(s>0?"up":"left"):(s<0||i<0)&&o.next(s<0?"down":"right"),t.preventDefault()):o.wrap.css("position")==="fixed"&&t.preventDefault())})},trigger:function(e,t){var r,i=t||o[n.inArray(e,["onCancel","beforeLoad","afterLoad"])>-1?"coming":"current"];if(!i)return;n.isFunction(i[e])&&(r=i[e].apply(i,Array.prototype.slice.call(arguments,1)));if(r===!1)return!1;i.helpers&&n.each(i.helpers,function(t,r){r&&o.helpers[t]&&n.isFunction(o.helpers[t][e])&&o.helpers[t][e](r,i)}),n.event.trigger(e+".fb")},isImage:function(e){return l(e)&&e.match(/\.(jp(e|g|eg)|gif|png|bmp|webp)((\?|#).*)?$/i)},isSWF:function(e){return l(e)&&e.match(/\.(swf)((\?|#).*)?$/i)},_start:function(e){var t={},r=o.group[e]||null,i,s,u;if(!r)return!1;t=n.extend(!0,{},o.opts,r),u=t.margin,n.type(u)==="number"&&(t.margin=[u,u,u,u]),t.modal&&n.extend(!0,t,{closeBtn:!1,closeClick:!1,nextClick:!1,arrows:!1,mouseWheel:!1,keys:null,helpers:{overlay:{css:{cursor:"auto"},closeClick:!1}}}),t.autoSize&&(t.autoWidth=t.autoHeight=!0),t.width==="auto"&&(t.autoWidth=!0),t.height==="auto"&&(t.autoHeight=!0),t.group=o.group,t.index=e,o.coming=t;if(!1===o.trigger("beforeLoad")){o.coming=null;return}s=t.type,i=t.href;if(!s)return o.coming=null,o.current&&o.router&&o.router!=="jumpto"?(o.current.index=e,o[o.router](o.direction)):!1;o.isActive=!0;if(s==="image"||s==="swf")t.autoHeight=t.autoWidth=!1,t.scrolling="visible";s==="image"&&(t.aspectRatio=!0),s==="iframe"&&a&&(t.scrolling="scroll"),t.wrap=n(t.tpl.wrap).addClass("fancybox-"+(a?"mobile":"desktop")+" fancybox-type-"+s+" fancybox-tmp "+t.wrapCSS).appendTo(t.parent),n.extend(t,{skin:n(".fancybox-skin",t.wrap).css("padding",d(t.padding)),outer:n(".fancybox-outer",t.wrap),inner:n(".fancybox-inner",t.wrap)});if(s==="inline"||s==="html"){if(!t.content||!t.content.length)return o._error("content")}else if(!i)return o._error("href");s==="image"?o._loadImage():s==="ajax"?o._loadAjax():s==="iframe"?o._loadIframe():o._afterLoad()},_error:function(e){n.extend(o.coming,{type:"html",autoWidth:!0,autoHeight:!0,minWidth:0,minHeight:0,scrolling:"no",hasError:e,content:o.coming.tpl.error}),o._afterLoad()},_loadImage:function(){var e=o.imgPreload=new Image;e.onload=function(){this.onload=this.onerror=null,o.coming.width=this.width,o.coming.height=this.height,o._afterLoad()},e.onerror=function(){this.onload=this.onerror=null,o._error("image")},e.src=o.coming.href,(e.complete===r||!e.complete)&&o.showLoading()},_loadAjax:function(){var e=o.coming;o.showLoading(),o.ajaxLoad=n.ajax(n.extend({},e.ajax,{url:e.href,error:function(e,t){o.coming&&t!=="abort"?o._error("ajax",e):o.hideLoading()},success:function(t,n){n==="success"&&(e.content=t,o._afterLoad())}}))},_loadIframe:function(){var e=o.coming,t=n(e.tpl.iframe.replace(/\{rnd\}/g,(new Date).getTime())).attr("scrolling",a?"auto":e.iframe.scrolling).attr("src",e.href);n(e.wrap).bind("onReset",function(){try{t.hide().parent().empty()}catch(e){}}),e.iframe.preload&&(o.showLoading(),t.bind("load",function(){n(this).unbind().bind("load.fb",o.update).data("ready",1),o.coming.wrap.removeClass("fancybox-tmp").show(),o._afterLoad()})),e.content=t.appendTo(e.inner),e.iframe.preload||o._afterLoad()},_preloadImages:function(){var e=o.group,t=o.current,n=e.length,r=t.preload?Math.min(t.preload,n-1):0,i,s;for(s=1;s<=r;s+=1)i=e[(t.index+s)%n],i.type==="image"&&i.href&&((new Image).src=i.href)},_afterLoad:function(){var e=o.coming,t=o.current,r,i,s,u,a,l;o.hideLoading();if(!e||!1===o.trigger("afterLoad",e,t)){o.coming.wrap.stop().trigger("onReset").remove(),o.coming=null;return}t&&(o.trigger("beforeChange",t),t.wrap.stop(!0).removeClass("fancybox-opened").find(".fancybox-item, .fancybox-nav").remove(),t.wrap.css("position")==="fixed"&&t.wrap.css(o._getPosition(!0))),o.unbindEvents(),r=e,i=e.content,s=e.type,u=e.scrolling,n.extend(o,{wrap:r.wrap,skin:r.skin,outer:r.outer,inner:r.inner,current:r,previous:t}),a=r.href;switch(s){case"inline":case"ajax":case"html":r.selector?i=n("<div>").html(i).find(r.selector):f(i)&&(i=i.show().detach(),r.wrap.bind("onReset",function(){n(this).find(".fancybox-inner").children().appendTo(r.parent).hide()}));break;case"image":i=r.tpl.image.replace("{href}",a);break;case"swf":i='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%"><param name="movie" value="'+a+'"></param>',l="",n.each(r.swf,function(e,t){i+='<param name="'+e+'" value="'+t+'"></param>',l+=" "+e+'="'+t+'"'}),i+='<embed src="'+a+'" type="application/x-shockwave-flash" width="100%" height="100%"'+l+"></embed></object>"}(r.type!=="iframe"||!r.iframe.preload)&&r.inner.append(i),o.trigger("beforeShow"),o._setDimension(),r.wrap.removeClass("fancybox-tmp"),r.inner.css("overflow",u==="yes"?"scroll":u==="no"?"hidden":u),r.pos=n.extend({},r.dim,o._getPosition(!0)),o.isOpen=!1,o.coming=null,o.bindEvents(),o.isOpened?t.prevMethod&&o.transitions[t.prevMethod]():n(".fancybox-wrap").not(r.wrap).stop().trigger("onReset").remove(),o.transitions[o.isOpened?r.nextMethod:r.openMethod](),o._preloadImages()},_setDimension:function(){var e=o.getViewport(),t=0,r=!1,i=!1,s=o.wrap,u=o.skin,a=o.inner,f=o.current,l=f.width,h=f.height,v=f.minWidth,m=f.minHeight,g=f.maxWidth,y=f.maxHeight,b=f.scrolling,w=f.scrollOutside,E=f.margin,S=E[1]+E[3],x=E[0]+E[2],T,N,C,k,L,A,O,M,_,D,P,H,B,j,I;s.add(u).add(a).width("auto").height("auto"),T=u.outerWidth(!0)-u.width(),N=u.outerHeight(!0)-u.height(),C=S+T,k=x+N,L=c(l)?(e.w-C)*parseFloat(l)/100:l,A=c(h)?(e.h-k)*parseFloat(h)/100:h;if(f.type==="iframe"){j=f.content;if(f.autoHeight&&j.data("ready")===1)try{j[0].contentWindow.document.location&&(a.width(L).height(9999),I=j.contents().find("body"),w&&I.css("overflow-x","hidden"),A=I.height())}catch(q){}}else if(f.autoWidth||f.autoHeight)a.addClass("fancybox-tmp"),f.autoWidth&&(L=a.width()),f.autoHeight&&(A=a.height()),a.removeClass("fancybox-tmp");l=p(L),h=p(A),_=L/A,v=p(c(v)?p(v,"w")-C:v),g=p(c(g)?p(g,"w")-C:g),m=p(c(m)?p(m,"h")-k:m),y=p(c(y)?p(y,"h")-k:y),O=g,M=y,H=e.w-S,B=e.h-x,f.aspectRatio?(l>g&&(l=g,h=l/_),h>y&&(h=y,l=h*_),l<v&&(l=v,h=l/_),h<m&&(h=m,l=h*_)):(l=Math.max(v,Math.min(l,g)),h=Math.max(m,Math.min(h,y)));if(f.fitToView){g=Math.min(e.w-C,g),y=Math.min(e.h-k,y),a.width(p(l)).height(p(h)),s.width(p(l+T)),D=s.width(),P=s.height();if(f.aspectRatio)while((D>H||P>B)&&l>v&&h>m){if(t++>49)break;h=Math.max(m,Math.min(y,h-10)),l=h*_,l<v&&(l=v,h=l/_),a.width(p(l)).height(p(h)),s.width(p(l+T)),D=s.width(),P=s.height()}else l=Math.max(v,Math.min(l,l-(D-H))),h=Math.max(m,Math.min(h,h-(P-B)))}w&&b==="auto"&&h<A&&l+T+w<H&&(l+=w),a.width(p(l)).height(p(h)),s.width(p(l+T)),D=s.width(),P=s.height(),r=(D>H||P>B)&&l>v&&h>m,i=f.aspectRatio?l<O&&h<M&&l<L&&h<A:(l<O||h<M)&&(l<L||h<A),n.extend(f,{dim:{width:d(D),height:d(P)},origWidth:L,origHeight:A,canShrink:r,canExpand:i,wPadding:T,hPadding:N,wrapSpace:P-u.outerHeight(!0),skinSpace:u.height()-h}),!j&&f.autoHeight&&h>m&&h<y&&!i&&a.height("auto")},_getPosition:function(e){var t=o.current,n=o.getViewport(),r=t.margin,i=o.wrap.width()+r[1]+r[3],s=o.wrap.height()+r[0]+r[2],u={position:"absolute",top:r[0]+n.y,left:r[3]+n.x};return t.autoCenter&&t.fixed&&!e&&s<=n.h&&i<=n.w&&(u={position:"fixed",top:r[0],left:r[3]}),u.top=d(Math.max(u.top,u.top+(n.h-s)*t.topRatio)),u.left=d(Math.max(u.left,u.left+(n.w-i)*.5)),u},_afterZoomIn:function(){var e=o.current;if(!e)return;o.isOpen=o.isOpened=!0,o.wrap.addClass("fancybox-opened").css("overflow","visible"),o.reposition(),(e.closeClick||e.nextClick)&&o.inner.css("cursor","pointer").bind("click.fb",function(t){!n(t.target).is("a")&&!n(t.target).parent().is("a")&&o[e.closeClick?"close":"next"]()}),e.closeBtn&&n(e.tpl.closeBtn).appendTo(o.skin).bind("click.fb",o.close),e.arrows&&o.group.length>1&&((e.loop||e.index>0)&&n(e.tpl.prev).appendTo(o.outer).bind("click.fb",o.prev),(e.loop||e.index<o.group.length-1)&&n(e.tpl.next).appendTo(o.outer).bind("click.fb",o.next)),o.trigger("afterShow"),o.opts.autoPlay&&!o.player.isActive&&(o.opts.autoPlay=!1,o.play())},_afterZoomOut:function(){var e=o.current;o.wrap.trigger("onReset").remove(),n.extend(o,{group:{},opts:{},router:!1,current:null,isActive:!1,isOpened:!1,isOpen:!1,isClosing:!1,wrap:null,skin:null,outer:null,inner:null}),o.trigger("afterClose",e)}}),o.transitions={getOrigPosition:function(){var e=o.current,t=e.element,r=n(e.orig),i={},s=50,u=50,a=e.hPadding,f=e.wPadding,l;return!r.length&&e.isDom&&t.is(":visible")&&(r=t.find("img:first"),r.length||(r=t)),r.length?(i=r.offset(),r.is("img")&&(s=r.outerWidth(),u=r.outerHeight())):(l=o.getViewport(),i.top=l.y+(l.h-u)*.5,i.left=l.x+(l.w-s)*.5),i={top:d(i.top-a*.5),left:d(i.left-f*.5),width:d(s+f),height:d(u+a)},i},step:function(e,t){var n,r,i,s=t.prop,u=o.current,a=u.wrapSpace,f=u.skinSpace;if(s==="width"||s==="height")n=t.end===t.start?1:(e-t.start)/(t.end-t.start),o.isClosing&&(n=1-n),r=s==="width"?u.wPadding:u.hPadding,i=e-r,o.skin[s](p(s==="width"?i:i-a*n)),o.inner[s](p(s==="width"?i:i-a*n-f*n))},zoomIn:function(){var e=o.current,t=e.pos,r=e.openEffect,i=r==="elastic",s=n.extend({opacity:1},t);delete s.position,i?(t=this.getOrigPosition(),e.openOpacity&&(t.opacity=.1)):r==="fade"&&(t.opacity=.1),o.wrap.css(t).animate(s,{duration:r==="none"?0:e.openSpeed,easing:e.openEasing,step:i?this.step:null,complete:o._afterZoomIn})},zoomOut:function(){var e=o.current,t=e.closeEffect,n=t==="elastic",r={opacity:.1};n&&(r=this.getOrigPosition(),e.closeOpacity&&(r.opacity=.1)),o.wrap.animate(r,{duration:t==="none"?0:e.closeSpeed,easing:e.closeEasing,step:n?this.step:null,complete:o._afterZoomOut})},changeIn:function(){var e=o.current,t=e.nextEffect,r=e.pos,i={opacity:1},s=o.direction,u=200,a;r.opacity=.1,t==="elastic"&&(o.origOverflow=o.origOverflow||n("body").css("overflowX"),n("body").css({overflowX:"hidden"}),a=s==="down"||s==="up"?"top":"left",s==="up"||s==="left"?(r[a]=d(parseInt(r[a],10)-u),i[a]="+="+u+"px"):(r[a]=d(parseInt(r[a],10)+u),i[a]="-="+u+"px")),o.wrap.css(r).animate(i,{duration:t==="none"?0:e.nextSpeed,easing:e.nextEasing,complete:function(){setTimeout(o._afterZoomIn,10),t==="elastic"&&n("body").css({overflowX:o.origOverflow})}})},changeOut:function(){var e=o.previous,t=e.prevEffect,r={opacity:.1},i=o.direction,s=200;t==="elastic"&&(r[i==="down"||i==="up"?"top":"left"]=(i==="down"||i==="right"?"-":"+")+"="+s+"px"),e.wrap.animate(r,{duration:t==="none"?0:e.prevSpeed,easing:e.prevEasing,complete:function(){n(this).trigger("onReset").remove()}})}},o.helpers.overlay={overlay:null,update:function(){var e,r,o;this.overlay.width("100%").height("100%"),n.browser.msie||a?(r=Math.max(t.documentElement.scrollWidth,t.body.scrollWidth),o=Math.max(t.documentElement.offsetWidth,t.body.offsetWidth),e=r<o?i.width():r):e=s.width(),this.overlay.width(e).height(s.height())},beforeShow:function(e){var t;if(this.overlay)return;e=n.extend(!0,{},o.defaults.helpers.overlay,e),t=this.overlay=n('<div id="fancybox-overlay"></div>').css(e.css).appendTo("body").bind("mousewheel",function(e){(!o.wrap||o.wrap.css("position")==="fixed"||o.wrap.is(":animated"))&&e.preventDefault()}),e.closeClick&&t.bind("click.fb",o.close),o.opts.fixed&&!a?t.addClass("overlay-fixed"):(this.update(),this.onUpdate=function(){this.update()}),t.fadeTo(e.speedIn,e.opacity)},afterClose:function(e){this.overlay&&this.overlay.fadeOut(e.speedOut||0,function(){n(this).remove()}),this.overlay=null}},o.helpers.title={beforeShow:function(e){var t=o.current.title,r=e.type,i;t&&(i=n('<div class="fancybox-title fancybox-title-'+r+'-wrap">'+t+"</div>").appendTo("body"),r==="float"&&(i.width(i.width()).wrapInner('<span class="child"></span>'),o.current.margin[2]+=Math.abs(parseInt(i.css("margin-bottom"),10))),i.appendTo(r==="over"?o.inner:r==="outside"?o.wrap:o.skin))}},n.fn.fancybox=function(e){var t,r=n(this),i=this.selector||"",u=function(s){var u=this,a=t,f,l;!(s.ctrlKey||s.altKey||s.shiftKey||s.metaKey)&&!n(u).is(".fancybox-wrap")&&(f=e.groupAttr||"data-fancybox-group",l=n(u).attr(f),l||(f="rel",l=u[f]),l&&l!==""&&l!=="nofollow"&&(u=i.length?n(i):r,u=u.filter("["+f+'="'+l+'"]'),a=u.index(this)),e.index=a,o.open(u,e)!==!1&&s.preventDefault())};return e=e||{},t=e.index||0,!i||e.live===!1?r.unbind("click.fb-start").bind("click.fb-start",u):s.undelegate(i,"click.fb-start").delegate(i+":not('.fancybox-item, .fancybox-nav')","click.fb-start",u),this},n.scrollbarWidth||(n.scrollbarWidth=function(){var e,t,r;return e=n('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo("body"),t=e.children(),r=t.innerWidth()-t.height(99).innerWidth(),e.remove(),r}),s.ready(function(){n.extend(o.defaults,{scrollOutside:n.scrollbarWidth(),fixed:n.support.fixedPosition||!(n.browser.msie&&n.browser.version<=6||a),parent:n("body")})})})(window,document,jQuery)