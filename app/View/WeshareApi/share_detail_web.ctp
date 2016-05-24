<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1.1, user-scalable=no, width=device-width">
    <title><?php echo $detail['title'] ?></title>
    <style>
        * { padding: 0; margin: 0; font-family: Helvetica, STHeiti STXihei, Microsoft JhengHei, Microsoft YaHei, Arial; }
        body { min-width: 320px; max-width: 640px; font-size: 13px;}
        ul li { list-style: none; }
        .fl { float: left; }
        .fr { float: right; }
        .clearfix { zoom: 1; }
        .clearfix:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
        .sharedetail{ background-color: #ffffff; padding: 15px 12px 5px 12px;}
        .sharedetail h1 { font-size: 1.2em; color: #333; margin-bottom: 10px !important;}
        .sharedetail p { color: #999; padding-bottom: 15px;}
        .sharedetail .images {margin-top: 10px;}
        .sharedetail .images dt {float: left;margin-right: 10px;position: relative;height: 65px;  }
        .sharedetail .images dt img {width: 60px;height: 60px;}
        .sharedetail .images dt a {width: 100%;}
        .cp-name { line-height: 24px; margin-bottom: 15px; font-size: 1.1em; color: #333333;}
        .detaildemo img{ display: block; margin-top: 20px; width: 100%;}
        .detaildemo ul{ border-top: 1px #eeeeee solid; margin-top: 20px;}
        .detaildemo ul li{ width: 100%; border-bottom: 1px #eeeeee solid; border-right: 1px #eeeeee solid;}
        .detaildemo ul li span{ display: block; float: left; padding: 8px 10px; color: #333333; border-left: 1px #eeeeee solid;}
        .detaildemo h1{ text-align: center; font-size: 1.2em; padding: 20px 10px 0 20px;}
        .detaildemo p{ padding-top: 20px; line-height: 22px; color: #333333;}
        .detaildemo label{ line-height: 24px; margin-top: 20px; display: block;}
        .margin-bottom-12 {margin-bottom: 12px !important;}
    </style>
</head>
<body style="background-color:#f1f1f1;">
<div class="sharedetail">
    <div class="clearfix cp-name margin-bottom-12">
        <?php echo $detail['description'] ?>
    </div>
    <?php if(count($detail['images']) > 0 ){?>
    <dl class="clearfix images margin-bottom-12">
        <?php foreach($detail['images'] as $image) { ?>
        <dt><a><img src="<?php echo $image ?>"/></a></dt>
        <?php } ?>
    </dl>
    <?php } ?>
</div>
</body>
</html>