$(function () {
    $('.product-ban').on('click', function () {
        if (confirm('确定下架?')) {
            window.location.href = '/shareManage/pool_product_item_ban/' + $(this).attr('data-upid') + "/" + $('#share-id-hidden').val();
        } else {
            console.log('手贱, 不好意思');
        }
    });
    $('#hidden-show-ban-item').click(function () {
        console.log('clicked....');
        $('.row.pool-product-item').each(function () {
            if ($(this).attr('status') == 1) {
                console.log("hidden item.");
                $(this).toggle();
            }
        });
    });
    $('input[name="data[Weshares][creator]"]').on('change', function () {
        var uid = $(this).val();
        if (uid == "") {
            alert('用户ID不应该为空');
        }

        $.ajax({
            url: '/share_manage/search_users?uid=' + uid,
            type: 'get',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                $('input[name="disable_data[PoolProduct][name]"]').val(data.nickname);
                $('input[name="disable_data[PoolProduct][mobilephone]"]').val(data.mobilephone);
            },
            error: function () {
                alert('用户信息错误, 请仔细检查');
            }
        });
    });
    $('#add-new-product').on("click", function () {
        var len = $('.pool-product-item').length;
        var newItem = $('.pool-product-item').eq(0).clone();
        newItem.find('input[type!=hidden]').each(function () {
            var tmp_name = $(this).attr('name');
            $(this).attr('name', tmp_name.replace(/(data\[[a-zA-Z_-]+\]\[)\d+(\]\[[a-zA-Z_-]+\])/g, '$1' + len + '$2'));
            $(this).attr('value', '');
        });

        $('#add-new-product').before(newItem);
    });

    $('.preview-image').on("click", function () {
        $('#image-preview').attr('src', $(this).attr('src-data'));
        $('#image-preview-modal').modal('show');
    });

    $('.delete-image').on("click", function () {
        var arr = $('#store-images-string').val().split('|');
        var idx = arr.splice(arr.indexOf($(this).attr('src-data')), 1);
        var nstring = arr.join('|');
        $('#store-images-string').val(nstring);
        $(this).parent('div').parent('div.image-area').remove();
    });

    $('.delete-m-banner-image').on("click", function () {
        $('#m-banner-images-string').val('');
        $(this).parent('div').parent('div.m-banner-image-area').remove();
    });

    $('.delete-banner-image').on("click", function () {
        $('#banner-images-string').val('');
        $(this).parent('div').parent('div.banner-image-area').remove();
    });

    $('#upload-image, #banner-upload-image, #m-banner-upload-image').on("click", function () {
        $('#uploader').click();
    });

    $('#upload-image-action').on("click", function () {
        var formData = new FormData($('#file-uploader').get(0));

        $.ajax({
            url: 'http://images.tongshijia.com/upload_images_to',
            type: 'post',
            data: formData,
            dataType: 'json',
            async: false,
            processData: false,
            contentType: false,
            success: function (data) {
                console.log(data);
                imgUrl = 'http://static.tongshijia.com/' + data.url[0];
                var obj = $('.image-area').eq(0).clone();
                obj.find('img').attr('src', imgUrl);
                obj.find('a').attr('src-data', imgUrl);
                $('.share-upload-btn').before(obj);
                $('.preview-image').on("click", function () {
                    $('#image-preview').attr('src', $(this).attr('src-data'));
                    $('#image-preview-modal').modal('show');
                });
                $('#store-images-string').val($('#store-images-string').val() + "|" + imgUrl);
            },
            error: function (data) {
            }
        });

    });
    $('#banner-upload-image-action').on("click", function () {
        var formData = new FormData($('#file-uploader').get(0));
        console.log(formData);

        $.ajax({
            url: 'http://images.tongshijia.com/upload_images_to',
            type: 'post',
            data: formData,
            dataType: 'json',
            async: false,
            processData: false,
            contentType: false,
            success: function (data) {
                imgUrl = 'http://static.tongshijia.com/' + data.url[0];
                var obj = $('.image-area').eq(0).clone();
                obj.removeClass('col-sm-2').addClass('col-sm-12 banner-image-area');
                obj.find('a.delete-image').removeClass('delete-image').addClass('delete-banner-image');
                obj.find('img').attr('src', imgUrl);
                obj.find('a').attr('src-data', imgUrl);
                $('.banner-image-area').remove();
                $('.banner-upload-btn').before(obj);
                $('.preview-image').on("click", function () {
                    $('#image-preview').attr('src', $(this).attr('src-data'));
                    $('#image-preview-modal').modal('show');
                });
                $('#banner-images-string').val(imgUrl);
            },
            error: function (data) {
            }
        });

    });
    $('#m-banner-upload-image-action').on("click", function () {
        var formData = new FormData($('#file-uploader').get(0));

        $.ajax({
            url: 'http://images.tongshijia.com/upload_images_to',
            type: 'post',
            data: formData,
            dataType: 'json',
            async: false,
            processData: false,
            contentType: false,
            success: function (data) {
                imgUrl = 'http://static.tongshijia.com/' + data.url[0];
                var obj = $('.image-area').eq(0).clone();
                obj.removeClass('col-sm-2').addClass('col-sm-12 m-banner-image-area');
                obj.find('a.delete-image').removeClass('delete-image').addClass('delete-m-banner-image');
                obj.find('img').attr('src', imgUrl);
                obj.find('a').attr('src-data', imgUrl);
                $('.m-banner-image-area').remove();
                $('.m-banner-upload-btn').before(obj);
                $('.preview-image').on("click", function () {
                    $('#image-preview').attr('src', $(this).attr('src-data'));
                    $('#image-preview-modal').modal('show');
                });
                $('#m-banner-images-string').val(imgUrl);
            },
            error: function (data) {
            }
        });

    });
    
    $("#add_creator").on("click",function(){
        $(this).parent().parent().after(
            '<div class="form-group">' +
            '<label>指定人id:</label><input type="text" value="" placeholder="请输入指定人ID" name="creator[]">' +
            '<a href="javascript:;" onclick="del(this)"><label>删除</label></a>' +
            '</div>'
        );
    });

    $("#hehe").on("click",function(){
        $("#xiajia").val(1);
        $("#form").submit();
    });
});

function checkUserInput(form) {
    var data = $(form).serializeArray();
    var error = false;

    data.forEach(function (item) {
        if( item.name != "data[WeshareProduct][0][wholesale_price]" && item.value == ''){
            error = true;
        }
    });

    if (error) {
        alert('您的输入有误, 请检查您的输入');
    }

    return !error;
}

function del(obj){
    $(obj).parent().remove();
}

function stop_share(id,obj)
{
    $.get("/share_manage/stop_share_api/"+id,function () {
        console.log($(obj).parent());
        $(obj).parent().remove();
    });
}