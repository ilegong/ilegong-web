(function()
{
	var tmpid = 'portlet-'+(new Date().getTime());
	
	function loadValue( portletNode,paramMap ){
		// pre记录前缀。
		var form = $('#'+tmpid).find('form:first');
		var pre,name;
		var dealparam = function(paramMap,pre){
			for ( var i in paramMap){
				if(typeof paramMap[i] =="string"){
					//alert(i+" - "+paramMap[i]+" - "+pre);
					
					if(typeof pre!="undefined"){
						name = pre+'['+i+']';
					}
					else{
						name = i;
					}
					var obj = form.find("[name='"+name+"']:input");
					if(obj.size()<1){
						if(i.match(/^\d*$/) && typeof pre!="undefined"){
							obj = form.find("[name='"+pre+"[]']:input");
							if(obj.size()<1){
								continue;
							}
						}
						continue;
					}
					var tagname = obj.get(0).tagName.toLowerCase();
					if(tagname == 'textarea'){
						obj.val(paramMap[i]);obj.text(paramMap[i]);
					}
					else if(tagname == 'checkbox'){
						obj.find('[value='+paramMap[i]+']').attr("checked",true);
					}
					else{
						obj.val(paramMap[i]);
					}					
				}
				else{
					if(typeof pre!="undefined"){
						if(i.match(/^\d*$/) && form.find("[name^='"+pre+"[]']:input").size()>0){
							pre+='[]'
						}
						else{
							pre+='['+i+']'
						}
					}
					else{
						pre=i;
					}
					dealparam(paramMap[i],pre);
				}
			}
		};
		dealparam(paramMap,pre);
	}

	CKEDITOR.dialog.add( 'portlet', function( editor )
	{
		/*仅第一次加载时，调用onload通过ajax加载表单内容；关闭重新打开dialog时，只调用onShow事件*/
		return {
			title : editor.lang.flash.title,
			minWidth : 860,
			minHeight : 310,
			onShow : function()
			{
				$('#'+tmpid).html('loading...');//每次都重新加载内容
				this.fakeImage = this.portletNode = this.portletCallback =  null;
				previewPreloader = new CKEDITOR.dom.element( 'embed', editor.document );
				var fakeImage = this.getSelectedElement();
				if ( fakeImage && fakeImage.data( 'cke-real-element-type' ) && fakeImage.data( 'cke-real-element-type' ) == 'portlet' )
				{
					this.fakeImage = fakeImage;
					var realElement = editor.restoreRealElement( fakeImage ),
						portletNode = realElement,paramMap = {};
					var info = portletNode.getAttribute( 'info' );
					var postdata = info.replace(/&amp;/g,'&').replace(/%5B/g,'[').replace(/%5D/g,']');
					this.portletNode = portletNode;
					$.ajax({
                		type: "POST",
                		url:ADMIN_BASEURL+'/admin/regions/getDialog', 
                		data:postdata,
                		success:function(data) {
                			$('#'+tmpid).html(data);
                			$('#'+tmpid).find('.submit').hide();		                					
                		}
                	});
				}
				else{
					$.ajax({
                		type: "GET",
                		url:ADMIN_BASEURL+'/admin/regions/getDialog', 
                		success:function(data) {
                			$('#'+tmpid).html(data);
                			$('#'+tmpid).find('.submit').hide();		                					
                		}
                	});
				}
			},
			onOk : function()
			{
				var portletNode = null,
					paramMap = null;
				if ( !this.fakeImage ){
					portletNode = CKEDITOR.dom.element.createFromHtml( '<portlet></portlet>', editor.document );
				}
				else{
					portletNode = this.portletNode;
				}
				var attributes = {
					info :  $("#"+tmpid+" form:first").serialize().replace(/&amp;/g,'&').replace(/%5B/g,'[').replace(/%5D/g,']')
				};
				portletNode.setAttributes( attributes );
				// Refresh the fake image.
				var newFakeImage = editor.createFakeElement( portletNode, 'cke_portlet', 'portlet', true );
				if ( this.fakeImage ){
					newFakeImage.replace( this.fakeImage );
					editor.getSelection().selectElement( newFakeImage );
				}
				else
					editor.insertElement( newFakeImage );
			},
			contents : [
				{
					id : 'portlet-content',
					label : editor.lang.common.generalTab,
					title: '',
		            elements: [
		                {
		                    type: 'html',
		                    html: '<div id="'+tmpid+'">loading...</div>',
		                    onLoad:function(){
		                    	/*
		                    	var postdata = '';
		                    	var dialog = this.getDialog();
		                    	var fakeImage = dialog.getSelectedElement();
                				if ( fakeImage && fakeImage.data( 'cke-real-element-type' ) && fakeImage.data( 'cke-real-element-type' ) == 'portlet' )
                				{
                					this.fakeImage = fakeImage;
                					var realElement = editor.restoreRealElement( fakeImage ),
                						portletNode = realElement,paramMap = {};
                					var info = portletNode.getAttribute( 'info' );
                					postdata = info.replace(/&amp;/g,'&').replace(/%5B/g,'[').replace(/%5D/g,']');
                				}
		                    	$.ajax({
		                    		type: "POST",
		                    		url:ADMIN_BASEURL+'/admin/regions/getDialog', 
		                    		data:postdata,
		                    		success:function(data) {
		                    			$('#'+tmpid).html(data);
		                    			$('#'+tmpid).find('.submit').hide();		                					
		                    		}
		                    	});*/
		                    }
		                    	
		                }
		            ]
				}
			]
		};
	} );
})();
