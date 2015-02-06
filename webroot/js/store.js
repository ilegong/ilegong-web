function setCoverImg(model,imgsrc){
    $('#'+model+'Coverimg').val(imgsrc);
    $('#'+model+'CoverimgPreview').attr('src',imgsrc);
}

function deleteImg(element){
    var me = $(element);
    $.getJSON(me.data('url'), function () {
        if(me.parents('div.ui-upload-filelist').length>0){
            me.parents('div.ui-upload-filelist').remove();
        }
        if(me.parents('li.upload-fileitem').length>0){
            me.parents('li.upload-fileitem').remove();
        }
    });
}
