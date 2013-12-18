CKEDITOR.dialog.add('flvPlayer',　function(editor){
　　　　
　　　　var　escape　=　function(value){
　　　　　　　　return　value;
　　　　};
　　　　return　{
			title:　'插入Flv视频',
			resizable:　CKEDITOR.DIALOG_RESIZE_BOTH,
			minWidth: 350,
			minHeight: 300,
			contents:　[{
				id: 'info',  
				label: '常规',
				accessKey: 'P',
				elements:[{
					type: 'hbox',
					widths : ['80%', '20%'],
					children:[{
						id: 'src',
						type: 'text',
						label: '源文件'
					},
					{
						type: 'button',
						id: 'browse',
						filebrowser: 'info:src',
						align: 'center',
						style : 'display:inline-block;margin-top:10px;',
						label: '浏览服务器'
					}]
				},{
					type: 'hbox',
					widths : ['80%', '20%'],
					children:[{
						id: 'thumb',
						type: 'text',
						label: '视频缩略图'
					},
					{
						type: 'button',
						id: 'browsethumb',
						filebrowser: 'info:thumb',
						align: 'center',
						style : 'display:inline-block;margin-top:10px;',
						label: '浏览服务器'
					}]
				},
				{
					type: 'hbox',
					widths : ['35%', '35%', '30%' ],
					children:[
						{
							type:　'text',
							label:　'视频宽度',
							id:　'mywidth',
							'default':　'470px',
							style:　'width:50px'
						},
						{
							type:　'text',
							label:　'视频高度',
							id:　'myheight',
							'default':　'320px',
							style:　'width:50px'
						},{
							type:　'select',
							label:　'自动播放',
							id:　'myloop',
							required:　true,
							'default':　'false',
							items:　[['是',　'true'],　['否',　'false']]
						}
					]// children finish
				},
				{
					type:　'textarea',
					style:　'width:300px;height:220px',
					label:　'预览',
					id:　'code'
				}]
			},{
                id: 'Upload',
                hidden: true,
                filebrowser: 'videoUploadButton',
                label: '上传视频',
                elements: [{
                    type: 'file',
                    id: 'upload',
                    label: '上传视频',
                    size: 38
                },
                {
                    type: 'fileButton',
                    id: 'videoUploadButton',
                    label: '发送到服务器',
                    filebrowser: 'info:src',
                    'for': ['Upload', 'upload']//'page_id', 'element_id' 
                }]
			}],
			onOk:　function(){
			　　　　mywidth　=　this.getValueOf('info',　'mywidth');
			　　　　myheight　=　this.getValueOf('info',　'myheight');
			　　　　myloop　=　this.getValueOf('info',　'myloop');
			　　　　mysrc　=　this.getValueOf('info',　'src');
				 mythumb　=　this.getValueOf('info',　'thumb');
			　　　　html　=　''　+　escape(mysrc)　+　'';
				 var vars = "file="　+　html　+　"&image="+mythumb;
				 var fhtml ="<embed height="　+　myheight　+　" width="　+　mywidth　+　" autostart="　+　myloop　+　" flashvars=\""+vars+"\" allowfullscreen=\"true\" allowscriptaccess=\"always\" bgcolor=\"#ffffff\" src=\""+ BASEURL +"/js/ckeditor/plugins/flvPlayer/jwplayer.swf\"></embed>";
				 editor.insertHtml(fhtml);
			},
			onLoad:　function(){
			}
　　　　};
});
