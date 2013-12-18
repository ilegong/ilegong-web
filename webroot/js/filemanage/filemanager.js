/**
 * Filemanager JS core
 * 
 * filemanager.js
 * 
 * @license MIT License
 * @author Jason Huck - Core Five Labs
 * @author Simon Georget <simon (at) linea21 (dot) com>
 * @copyright Authors
 */

(function($) {

	// function to retrieve GET params
	$.urlParam = function(name) {
		var results = new RegExp('[\\?&]' + name + '=([^&#]*)')
				.exec(window.location.href);
		if (results)
			return results[1];
		else
			return 0;
	}

	/*---------------------------------------------------------
	 Setup, Layout, and Status Functions
	 ---------------------------------------------------------*/

	// Sets paths to connectors based on language selection.
	var fileConnector = ADMIN_BASEURL+'/admin/uploadfiles/filemanage';

	var capabilities = new Array('select', 'download', 'rename', 'delete');

	// Get localized messages from file
	// through culture var or from URL
	if ($.urlParam('langCode') != 0
			&& file_exists(BASEURL+'/js/filemanage/languages/' + $.urlParam('langCode')
					+ '.js'))
		culture = $.urlParam('langCode');
	var lg = [];
	$.ajax({
		url : BASEURL+'/js/filemanage/languages/' + culture + '.js',
		async : false,
		dataType : 'json',
		success : function(json) {
			lg = json;
		}
	});

	// Forces columns to fill the layout vertically.
	// Called on initial page load and on resize.
	var setDimensions = function() {
		var newH = $(window).height() - $('#uploader').height() - 30;
		$('#splitter, #filetree, .vsplitbar').height(newH);
		$('#fileinfo').height(newH-35);
	}

	// Display Min Path
	var displayPath = function(path) {

		if (showFullPath == false) {
			// if a "displayPathDecorator" function is defined, use it to
			// decorate path
			return 'function' === (typeof displayPathDecorator) ? displayPathDecorator(path)
					: path.replace(fileRoot, "/");
		} else {
			return path;
		}

	}

	// Set the view buttons state
	var setViewButtonsFor = function(viewMode) {
		if (viewMode == 'grid') {
			$('#grid').closest('li').addClass('active');
			$('#list').closest('li').removeClass('active');
		} else {
			$('#list').closest('li').addClass('active');
			$('#grid').closest('li').removeClass('active');
		}
	}

	// Test if a given url exists
	function file_exists(url) {
		var req = this.window.ActiveXObject ? new ActiveXObject(
				"Microsoft.XMLHTTP") : new XMLHttpRequest();
		if (!req) {
			throw new Error('XMLHttpRequest not supported');
		}
		// HEAD Results are usually shorter (faster) than GET
		req.open('HEAD', url, false);
		req.send(null);
		if (req.status == 200) {
			return true;
		}
		return false;
	}

	// preg_replace
	// Code from : http://xuxu.fr/2006/05/20/preg-replace-javascript/
	var preg_replace = function(array_pattern, array_pattern_replace, str) {
		var new_str = String(str);
		for (i = 0; i < array_pattern.length; i++) {
			var reg_exp = RegExp(array_pattern[i], "g");
			var val_to_replace = array_pattern_replace[i];
			new_str = new_str.replace(reg_exp, val_to_replace);
		}
		return new_str;
	}

	// cleanString (), on the same model as server side (connector)
	// cleanString
	var cleanString = function(str) {
		var cleaned = "";
		var p_search = new Array("Š", "š", "Đ", "đ", "Ž", "ž", "Č", "č", "Ć",
				"ć", "À", "Á", "Â", "Ã", "Ä", "Å", "Æ", "Ç", "È", "É", "Ê",
				"Ë", "Ì", "Í", "Î", "Ï", "Ñ", "Ò", "Ó", "Ô", "Õ", "Ö", "Ő",
				"Ø", "Ù", "Ú", "Û", "Ü", "Ý", "Þ", "ß", "à", "á", "â", "ã",
				"ä", "å", "æ", "ç", "è", "é", "ê", "ë", "ì", "í", "î", "ï",
				"ð", "ñ", "ò", "ó", "ô", "õ", "ö", "ő", "ø", "ù", "ú", "û",
				"ý", "ý", "þ", "ÿ", "Ŕ", "ŕ", " ", "'", "/");
		var p_replace = new Array("S", "s", "Dj", "dj", "Z", "z", "C", "c",
				"C", "c", "A", "A", "A", "A", "A", "A", "A", "C", "E", "E",
				"E", "E", "I", "I", "I", "I", "N", "O", "O", "O", "O", "O",
				"O", "O", "U", "U", "U", "U", "Y", "B", "Ss", "a", "a", "a",
				"a", "a", "a", "a", "c", "e", "e", "e", "e", "i", "i", "i",
				"i", "o", "n", "o", "o", "o", "o", "o", "o", "o", "u", "u",
				"u", "y", "y", "b", "y", "R", "r", "_", "_", "");

		cleaned = preg_replace(p_search, p_replace, str);
		cleaned = cleaned.replace(/[^_a-zA-Z0-9]/g, "");
		cleaned = cleaned.replace(/[_]+/g, "_");

		return cleaned;
	}

	var nameFormat = function(input) {
		filename = '';
		if (input.lastIndexOf('.') != -1) {
			filename = cleanString(input.substr(0, input.lastIndexOf('.')));
			filename += '.' + input.split('.').pop();
		} else {
			filename = cleanString(input);
		}
		return filename;
	}

	// Handle Error. Freeze interactive buttons and display
	// error message. Also called when auth() function return false (Code ==
	// "-1")
	var handleError = function(errMsg) {
		$('#fileinfo').html('<h1>' + errMsg + '</h1>');
		$('#newfile').attr("disabled", "disabled");
		$('#upload').attr("disabled", "disabled");
		$('#newfolder').attr("disabled", "disabled");
	}

	// Test if Data structure has the 'cap' capability
	// 'cap' is one of 'select', 'rename', 'delete', 'download'
	function has_capability(data, cap) {
		if (data['File Type'] == 'dir' && cap == 'download')
			return false;
		if (typeof (data['Capabilities']) == "undefined")
			return true;
		else
			return $.inArray(cap, data['Capabilities']) > -1;
	}

	// from http://phpjs.org/functions/basename:360
	var basename = function(path, suffix) {
		var b = path.replace(/^.*[\/\\]/g, '');
		if (typeof (suffix) == 'string'
				&& b.substr(b.length - suffix.length) == suffix) {
			b = b.substr(0, b.length - suffix.length);
		}
		return b;
	}

	// return filename extension
	var getExtension = function(filename) {
		if (filename.split('.').length == 1) {
			return "";
		}
		return filename.split('.').pop();
	}

	// return filename without extension {
	var getFilename = function(filename) {
		if (filename.lastIndexOf('.') != -1) {
			return filename.substring(0, filename.lastIndexOf('.'));
		} else {
			return filename;
		}
	}

	// Test if file is supported web video file
	var isVideoFile = function(filename) {
		if ($.inArray(getExtension(filename), videosExt) != -1) {
			return true;
		} else {
			return false;
		}
	}

	// Test if file is supported web audio file
	var isAudioFile = function(filename) {
		if ($.inArray(getExtension(filename), audiosExt) != -1) {
			return true;
		} else {
			return false;
		}
	}

	// Return HTML video player
	var getVideoPlayer = function(data) {
		var code = '<video width=' + videosPlayerWidth + ' height='
				+ videosPlayerHeight + ' src="' + data['Path']
				+ '" controls="controls">';
		code += '<img src="' + data['Preview'] + '" />';
		code += '</video>';

		$("#fileinfo img").remove();
		$('#fileinfo #preview h1').before(code);

	}

	// Return HTML audio player
	var getAudioPlayer = function(data) {
		var code = '<audio src="' + data['Path'] + '" controls="controls">';
		code += '<img src="' + data['Preview'] + '" />';
		code += '</audio>';

		$("#fileinfo img").remove();
		$('#fileinfo #preview h1').before(code);

	}

	// Sets the folder status, upload, and new folder functions
	// to the path specified. Called on initial page load and
	// whenever a new directory is selected.
	var setCurrentPathInfo = function(path) {
		$('#currentpath').val(path);
		$('#uploader h1').text(lg.current_folder + displayPath(path));		
	}

	// Converts bytes to kb, mb, or gb as needed for display.
	var formatBytes = function(bytes) {
		var n = parseFloat(bytes);
		var d = parseFloat(1024);
		var c = 0;
		var u = [ lg.bytes, lg.kb, lg.mb, lg.gb ];

		while (true) {
			if (n < d) {
				n = Math.round(n * 100) / 100;
				return n +''+ u[c];
			} else {
				n /= d;
				c += 1;
			}
		}
	}

	/*---------------------------------------------------------
	 Item Actions
	 ---------------------------------------------------------*/

	// Calls the SetUrl function for FCKEditor compatibility,
	// passes file path, dimensions, and alt text back to the
	// opening window. Triggered by clicking the "Select"
	// button in detail views or choosing the "Select"
	// NOTE: closes the window when finished.
	var selectItem = function(data) {
		var url = relPath + data['Path'];

		if (window.opener || window.tinyMCEPopup) {
			if (window.tinyMCEPopup) {
				// use TinyMCE > 3.0 integration method
				var win = tinyMCEPopup.getWindowArg("window");
				win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = url;
				if (typeof (win.ImageDialog) != "undefined") {
					// Update image dimensions
					if (win.ImageDialog.getImageData)
						win.ImageDialog.getImageData();

					// Preview if necessary
					if (win.ImageDialog.showPreviewImage)
						win.ImageDialog.showPreviewImage(url);
				}
				tinyMCEPopup.close();
				return;
			}
			if ($.urlParam('CKEditor')) {
				// use CKEditor 3.0 integration method
				window.opener.CKEDITOR.tools.callFunction($
						.urlParam('CKEditorFuncNum'), url);
			} else {
				// use FCKEditor 2.0 integration method
				if (data['Properties']['Width'] != '') {
					var p = url;
					var w = data['Properties']['Width'];
					var h = data['Properties']['Height'];
					window.opener.SetUrl(p, w, h);
				} else {
					window.opener.SetUrl(url);
				}
			}

			window.close();
		} else {
			showMessage(lg.fck_select_integration);
		}
	}

	/*---------------------------------------------------------
	 Functions to Update the File Tree
	 ---------------------------------------------------------*/
	// Updates the specified node with a new name. Called after
	// a successful rename operation.
	var updateNode = function(oldPath, newPath, newName) {
		var thisNode = $('#filetree').find('a[rel="' + oldPath + '"]');
		var parentNode = thisNode.parent().parent().prev('a');
		thisNode.attr('rel', newPath).text(newName);
		parentNode.click();

	}

	// Removes the specified node. Called after a successful
	// delete operation.
	var removeNode = function(path) {
		$('#filetree').find('a[rel="' + path + '"]').parent().fadeOut('slow',
				function() {
					$(this).remove();
				});
		// grid case
		if ($('#fileinfo').data('view') == 'grid') {
			$('#contents img[alt="' + path + '"]').parent().parent().fadeOut(
					'slow', function() {
						$(this).remove();
					});
		}
		// list case
		else {
			$('table#contents').find('td[title="' + path + '"]').parent()
					.fadeOut('slow', function() {
						$(this).remove();
					});
		}
		// remove fileinfo when item to remove is currently selected
		if ($('#preview').length) {
			getFolderInfo(path.substr(0, path.lastIndexOf('/') + 1));
		}
	}

	// Adds a new folder as the first item beneath the
	// specified parent node. Called after a new folder is
	// successfully created.
	var addFolder = function(parent, name) {
		var newNode = '<li class="directory collapsed"><a rel="'+ parent
				+ name+ '/" href="#">'+ name
				+ '</a><ul class="jqueryFileTree" style="display: block;"></ul></li>';
		var parentNode = $('#filetree').find('a[rel="' + parent + '"]');
		if (parent != fileRoot) {
			parentNode.next('ul').prepend(newNode).prev('a').click();
		} else {
			$('#filetree > ul').prepend(newNode);
			$('#filetree').find('li a[rel="' + parent + name + '/"]').unbind('click').click(function() {
				getFolderInfo(parent + name + '/');
			});
		}
		showMessage(lg.successful_added_folder);
	}
	
	function showMessage(text){
		$.jGrowl(text, { theme: 'success'});
	}

	/*---------------------------------------------------------
	 Functions to Retrieve File and Folder Details
	 ---------------------------------------------------------*/

	// Decides whether to retrieve file or folder info based on
	// the path provided.
	/**
	 * @todo. 点击显示图片信息。
	 */
	var getDetailView = function(path) {
		if (path.lastIndexOf('/') == path.length - 1) {
			getFolderInfo(path);
			$('#filetree').find('a[rel="' + path + '"]').click();
		} else {
			//getFileInfo(path);
			//var win = window.opener || window;
			//win.parent.CKEDITOR.tools.callFunction(2,path,'');
		}
	}

	// Retrieves data for all items within the given folder and
	// TODO: consider stylesheet switching to switch between grid
	// and list views with sorting options.
	var getFolderInfo = function(path) {
		// Update location for status, upload, & new folder functions.
		setCurrentPathInfo(path);
		// Display an activity indicator.
		$('#fileinfo').html('<img id="activity" src="'+BASEURL+'/img/ajax/wheel_throbber.gif" width="30" height="30" />');

		// Retrieve the data and generate the markup.
		var d = new Date(); // to prevent IE cache issues
		var url = fileConnector + '?path=' + encodeURIComponent(path)
				+ '&mode=getfiles&showThumbs=' + showThumbs + '&time='
				+ d.getMilliseconds();
		if ($.urlParam('type'))
			url += '&type=' + $.urlParam('type');
		$.getJSON(
			url,
			function(data) {
				var result = '';
				// Is there any error or user is unauthorized?
				if (data.Code == '-1') {
					handleError(data.Error);
					return;
				};

				if (data) {
					if ($('#fileinfo').data('view') == 'grid') {
						result += '<ul id="contents" class="grid">';

						for (key in data) {
							var props = data[key]['Properties'];
							var cap_classes = "";
							for (cap in capabilities) {
								if (has_capability(data[key],capabilities[cap])) {
									cap_classes += " cap_"+ capabilities[cap];
								}
							}

							var scaledWidth = 64;
							var actualWidth = props['Width'];
							if (actualWidth > 1&& actualWidth < scaledWidth)
								scaledWidth = actualWidth;

							result += '<li class="'+ cap_classes+ '"><div class="clip"><img src="'+ data[key]['Preview']
									+ '" width="' + scaledWidth+ '" alt="' + data[key]['Path']+ '" title="' + data[key]['Path']+ '" /></div><p>'
									+ data[key]['Filename']+ '</p>';
							if (props['Width']&& props['Width'] != '')
								result += '<span class="meta dimensions">'+ props['Width']+ 'x'+ props['Height']+ '</span>';
							if (props['Size']&& props['Size'] != '')
								result += '<span class="meta size">'+ props['Size'] + '</span>';
							if (props['Date Created']&& props['Date Created'] != '')
								result += '<span class="meta created">'+ props['Date Created']+ '</span>';
							if (props['Date Modified']&& props['Date Modified'] != '')
								result += '<span class="meta modified">'+ props['Date Modified']+ '</span>';
							result += '</li>';
						}
						result += '</ul>';
					} else {
						result += '<table id="contents" class="list">';
						result += '<thead><tr><th class="headerSortDown"><span>'+ lg.name+ '</span></th><th><span>'
								+ lg.dimensions+ '</span></th><th><span>'
								+ lg.size+ '</span></th><th><span>'+ lg.modified
								+ '</span></th></tr></thead>';
						result += '<tbody>';

						for (key in data) {
							var path = data[key]['Path'];
							var props = data[key]['Properties'];
							var cap_classes = "";
							for (cap in capabilities) {
								if (has_capability(data[key],capabilities[cap])) {
									cap_classes += " cap_"+ capabilities[cap];
								}
							}
							result += '<tr class="' + cap_classes+ '">';
							result += '<td title="' + path + '">'+ data[key]['Filename']+ '</td>';

							if (props['Width']&& props['Width'] != '') {
								result += ('<td>' + props['Width']+ 'x' + props['Height'] + '</td>');
							} else {
								result += '<td></td>';
							}

							if (props['Size']&& props['Size'] != '') {
								result += '<td><abbr title="'+ props['Size']+ '">'+ formatBytes(props['Size'])+ '</abbr></td>';
							} else {
								result += '<td></td>';
							}

							if (props['Date Modified']&& props['Date Modified'] != '') {
								result += '<td>'+ props['Date Modified']+ '</td>';
							} else {
								result += '<td></td>';
							}
							result += '</tr>';
						}
						result += '</tbody>';
						result += '</table>';
					}
				} else {
					result += '<h1>' + lg.could_not_retrieve_folder+ '</h1>';
				}

				// Add the new markup to the DOM.
				$('#fileinfo').html(result);

				// Bind click events to create detail views and add
				if ($('#fileinfo').data('view') == 'grid') {
					$('#fileinfo').find('#contents li').click(function() {
						var path = $(this).find('img').attr('alt');
						getDetailView(path);
					});
				} else {
					$('#fileinfo').find('td:first-child').each(function() {
						var path = $(this).attr('title');
						var treenode = $('#filetree').find('a[rel="' + path+ '"]').parent();
						$(this).css('background-image',treenode.css('background-image'));
					});

					$('#fileinfo tbody tr').click(function() {
						var path = $('td:first-child',this).attr('title');
						getDetailView(path);
					});

					$('#fileinfo').find('table').tablesorter({
						textExtraction : function(node) {
							if ($(node).find('abbr').size()) {
								return $(node).find('abbr').attr('title');
							} else {
								return node.innerHTML;
							}
						}
					});
				}
			});
	}
	window.getFolderInfo = getFolderInfo;

	// Retrieve data (file/folder listing) for jqueryFileTree and pass the data
	// back
	// to the callback function in jqueryFileTree
	var populateFileTree = function(path, callback) {
		var d = new Date(); // to prevent IE cache issues
		var url = fileConnector + '?path=' + encodeURIComponent(path)
				+ '&mode=getfolder&showThumbs=' + showThumbs + '&time='
				+ d.getMilliseconds();
		if ($.urlParam('type'))
			url += '&type=' + $.urlParam('type');
		$.getJSON(
				url,
				function(data) {
					var result = '';
					// Is there any error or user is unauthorized?
					if (data.Code == '-1') {handleError(data.Error);return;};				
					if (data) {
						result += "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
						for (key in data) {
							var cap_classes = "";
							for (cap in capabilities) {
								if (has_capability(data[key],capabilities[cap])) {
									cap_classes += " cap_"+ capabilities[cap];
								}
							}
							if (data[key]['File Type'] == 'dir') {
								result += "<li class=\"directory collapsed\"><a href=\"#\" class=\""+ cap_classes+ "\" rel=\""+ data[key]['Path']+ "\">"+ data[key]['Filename']+ "</a></li>";
							} else {
								result += "<li class=\"file ext_"+ data[key]['File Type'].toLowerCase()+ "\"><a href=\"#\" class=\""+ cap_classes + "\" rel=\""+ data[key]['Path'] + "\">"+ data[key]['Filename']+ "</a></li>";
							}
						}
						result += "</ul>";
					} else {
						result += '<h1>' + lg.could_not_retrieve_folder+ '</h1>';
					}
					callback(result);
			});
	}

	/*---------------------------------------------------------
	 Initialization
	 ---------------------------------------------------------*/

	$(function() {
		if (extra_js) {
			for ( var i = 0; i < extra_js.length; i++) {
				$.getScript(extra_js[i]);
			}
		}

		if ($.urlParam('expandedFolder') != 0) {
			expandedFolder = $.urlParam('expandedFolder');
			fullexpandedFolder = fileRoot + expandedFolder;
		} else {
			expandedFolder = '';
			fullexpandedFolder = null;
		}
		// Adjust layout.
		setDimensions();
		$(window).resize(setDimensions);
		
		$('#newfolder').unbind().click(function() {						
			$('#modal-create-folder').modal('show').on('shown',function(){
				$('#modal-create-folder').children('#fname').val('');
				$(this).find('.btn-primary').unbind('click').bind('click',function(){
					var fname = $('#modal-create-folder').find('#fname:first').val();
					if (typeof fname != 'undefined' && fname != '') {
						fname = cleanString(fname);
						var d = new Date(); // to prevent IE cache
						$.getJSON(fileConnector+ '?mode=addfolder&path='+ $('#currentpath').val()
								+ '&name=' + fname + '&time='+ d.getMilliseconds(), 
						  function(result) {
							if (result['Code'] == 0) {
								addFolder(result['Parent'],result['Name']);	// 左侧树中增加							
								getFolderInfo(result['Parent']);
								$('#modal-create-folder').modal('hide');
							} else {
								showMessage(result['Error']);//文件夹已存在或创建失败
							}
						});
						
					} else {
						showMessage(lg.no_foldername);
					}
					
				})
			});
		});

		// we finalize the FileManager UI initialization
		// with localized text if necessary
		if (autoload == true) {
			$('#newfolder').append(lg.new_folder);
			$('#grid').attr('title', lg.grid_view);
			$('#list').attr('title', lg.list_view);
			$('#fileinfo h1').append(lg.select_from_left);
			$('#itemOptions a[href$="#select"]').append(lg.select);
			$('#itemOptions a[href$="#download"]').append(lg.download);
			$('#itemOptions a[href$="#rename"]').append(lg.rename);
			$('#itemOptions a[href$="#delete"]').append(lg.del);
		}

		// Provides support for adjustible columns.
		$('#splitter').splitter({
			sizeLeft : 200
		});

		// cosmetic tweak for buttons
		$('button').wrapInner('<span></span>');

		// Set initial view state.
		$('#fileinfo').data('view', defaultViewMode);
		setViewButtonsFor(defaultViewMode);

		$('#home').click(function() {
			var currentViewMode = $('#fileinfo').data('view');
			$('#fileinfo').data('view', currentViewMode);
			$('#filetree>ul>li.expanded>a').trigger('click');
			getFolderInfo(fileRoot);
		});

		// Set buttons to switch between grid and list views.
		$('#grid').click(function() {
			setViewButtonsFor('grid');
			$('#fileinfo').data('view', 'grid');
			getFolderInfo($('#currentpath').val());
		});

		$('#list').click(function() {
			setViewButtonsFor('list');
			$('#fileinfo').data('view', 'list');
			getFolderInfo($('#currentpath').val());
		});

		// Provide initial values for upload form, status, etc.
		setCurrentPathInfo(fileRoot);

		// Creates file tree.
		$('#filetree').fileTree({
			root : fileRoot,
			datafunc : populateFileTree,
			multiFolder : false,
			folderCallback : function(path) {
				var cur_path = $('#currentpath').val();
				if(cur_path!=path){
					getFolderInfo(path);
				}
			},
			expandedFolder : fullexpandedFolder
		});
		// Disable select function if no window.opener
		if (!(window.opener || window.tinyMCEPopup))
			$('#itemOptions a[href$="#select"]').remove();
		// Keep only browseOnly features if needed
		if (browseOnly == true) {
			$('#newfile').remove();
			$('#upload').remove();
			$('#newfolder').remove();
			$('#toolbar').remove('#rename');
		}
		getDetailView(fileRoot + expandedFolder);
	});

})(jQuery);
