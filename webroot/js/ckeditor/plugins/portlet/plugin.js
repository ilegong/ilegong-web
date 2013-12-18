/*插入portlet */
(function(){
    function createFakeElement( editor, realElement )
	{
		return editor.createFakeParserElement( realElement, 'cke_portlet', 'portlet', true );
	}

    var pluginName = 'portlet';
    CKEDITOR.plugins.add(pluginName, {
        init : function( editor ){
        	editor.addCommand( pluginName, new CKEDITOR.dialogCommand(pluginName) );
        	editor.ui.addButton( 'Portlet',{
                label     : $.jslanguage.insert_portlet,
                command   : pluginName
            });
        	CKEDITOR.dialog.add(pluginName, this.path + 'dialogs/'+pluginName+'.js' );
            CKEDITOR.addCss(
    				'img.cke_portlet' +
    				'{' +
    					'background-image: url(' + CKEDITOR.getUrl( this.path + 'images/placeholder.png' ) + ');' +
    					'background-position: center center;' +
    					'background-repeat: no-repeat;' +
    					'border: 1px solid #a9a9a9;' +
    					'width: 100%;' +
    					'height: 120px;' +
    				'}'
    				+'.cke_reset_all input,.cke_reset_all textarea,.cke_reset_all select,.cke_reset_all .help-inline,.cke_reset_all .uneditable-input, .cke_reset_all .input-prepend,.cke_reset_all .input-append{border:1px solid #CCCCCC;}'
    				+'.cke_reset_all, .cke_reset_all * {white-space: inherit;border:1px solid #CCCCCC;}'
    		);
            editor.on( 'doubleclick', function( evt ){
    			var element = evt.data.element;
    			if ( element.is( 'img' ) && element.data( 'cke-real-element-type' ) == pluginName )
    				evt.data.dialog = pluginName;
    		});
            
        },

		afterInit : function( editor )
		{
			var dataProcessor = editor.dataProcessor,
				dataFilter = dataProcessor && dataProcessor.dataFilter;

			if ( dataFilter )
			{
				dataFilter.addRules(
					{
						elements :
						{
							'portlet' : function( element )
							{
								return createFakeElement( editor, element );
							}
						}
					},
					5);
			}
		},
        requires : [ 'fakeobjects' ]
    });
})();