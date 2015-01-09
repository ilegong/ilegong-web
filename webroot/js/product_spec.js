$(function() {
    var $json_spec_str = $('#json_spec_str');
    var $product_specs = $('#product_specs');//提交的值
    var $ProductSpecs = $('#spec_tags');
    var $product_tag_name=$('#product_tag_name');//标签名称
    var spec_obj=JSON.parse($json_spec_str.text());
    var hasSelect = new Array();
    $product_specs.val($json_spec_str.text());
    if(spec_obj){
        if(spec_obj['choices']){
            $.each(spec_obj['choices'],function(key,val){
                hasSelect=val;
            });
        }
        $ProductSpecs.val(hasSelect.join(','));
        $.each(spec_obj['choices'],function(key,val){
            $product_tag_name.val(key);
        });
    }else{
        spec_obj = {
            "map":{},
            "choices":{'规格':[]}
        };
    }
    // update tag name
    $product_tag_name.on('blur',function(e){
        e.preventDefault();
        var tagName = $product_tag_name.val();
        var choicesData = spec_obj['choices'];
        if(!choicesData[tagName]){
            var temp = {};
            temp[''+tagName] = hasSelect;
            spec_obj['choices'] = temp;
            updateView();
        }
    });

    $('#spec_tags').tagsInput({
        'width':'auto',
        'defaultText':'回车添加规格',
        'removeWithBackspace' : true,
        'onAddTag':addJsonCode,
        'onRemoveTag':removeJsonCode
    });
    // update map data
    function updateData(tags){
        var maxIndex = maxKeyInMap(spec_obj['map']);
        var update = false;
        $.each(tags,function(index,tag){
            if(!tagIsInMap(tag)){
                var mapData = spec_obj['map'];
                mapData[maxIndex] = {"name":tag};
                update = true;
            }
        });
        updateView();
    }

    function updateView(){
        var postData = JSON.stringify(spec_obj);
        $json_spec_str.text(postData);
        $product_specs.val(postData);
    }

    function addJsonCode(tag){
        if($.inArray(tag, hasSelect)<0){
            hasSelect.push(tag);
            updateChoice(hasSelect);
            updateData(hasSelect);
        }
    }
    function removeJsonCode(tag){
        if($.inArray(tag, hasSelect)>=0){
            //remove data
            hasSelect.splice($.inArray(tag,hasSelect),1);
            updateChoice(hasSelect);
            updateData(hasSelect);
        }

    }

    function tagIsInMap(tag){
        var flag = false;
        $.each(spec_obj['map'],function(key,value){
            if(value['name']==tag){
                flag = true;
                return false;
            }
        });
        return flag;
    }

    function updateChoice(hasSelect){
        var choiceData = spec_obj['choices'];
        $.each(choiceData,function(key,value){
            choiceData[key]=hasSelect;
        });
    }

    function maxKeyInMap(map){
        var keys = new Array();
        $.each(map,function(index,val){
            keys.push(index);
        });
        var max = keys[0];
        for(var i=1;i<keys.length;i++){
            if(max<keys[i])
                max=keys[i];
        }
        return max?parseInt(max)+1:1;
    }
});

//        var guid = (function() {
//            function s4() {
//                return Math.floor((1 + Math.random()) * 0x10000)
//                        .toString(16)
//                        .substring(1);
//            }
//            return function() {
//                return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
//                s4() + '-' + s4() + s4() + s4();
//            };
//        })();
