<?php

App::uses('RequestFacade', 'Network');

/**
 * 抓取相关操作封装类
 * 扩展的Utility类名后面都加上Utility，放在类名与Model等其它类重名
 * @author Arlon
 *
 */
class CrawlUtility {

    /**
     * 保存内容中的图片到本站服务器
     * @param $content
     * @param $url
     * @param $imgprefix
     */
    public static function saveImagesInContent($content, $url, $imgprefix) {
        preg_match_all("/(src|SRC)=[\s|\"|']*((http:\/\/)?([^>]*)\.(gif|jpg|jpeg|bmp|png))/isU", $content, $imagearray);
        $imagearray = array_unique($imagearray[2]);
        $coverimg = array();
        foreach ($imagearray as $key => $value) {
            $value = str_replace('"', '', $value);
            $value = str_replace("'", '', $value);
            $originimgurl = $value = trim($value);
            $imageurl = self::getPagelinkUrl($value, $url);
            if ($imageurl = self::saveImagesByUrl($imageurl, $url, $imgprefix)) {
                if ($key == 0)
                    $coverimg[] = $imageurl;
                $imageurl = getStaticFileUrl($imageurl,true);                
                $content = str_replace($originimgurl, $imageurl, $content);
            }
        }
        return array('content' => $content, 'coverimg' => $coverimg);
    }

    /**
     * 保存远程图片到服务器
     * @param $url
     */
    public static function saveImagesByUrl($imageurl, $referer='', $imgprefix='') {
        $year_month = date('Y-m');
        $image_save_path = UPLOAD_FILE_PATH . 'remote/' . $year_month . '/';
        $imgname = $imgprefix . rawurldecode(basename($imageurl));
        if (!file_exists($image_save_path . $imgname)) {
            $img_filecontent = self::getRomoteUrlContent($imageurl, array('header' => array('Referer' => $referer)));
            if ($img_filecontent) {
                $img_file = new File($image_save_path . $imgname, true);
                $img_file->write($img_filecontent);
                $img_file->close();
            } else {
                return false;
            }
        }
        return '/files/remote/' . $year_month . '/' . $imgname;
    }

    /**
     * 获取页面链接的绝对url,将相对的url转换成全部url地址
     * @param $url_in_page  在页面上出现的链接，（可能为相对路径）
     * @param $url 页面链接url，为可以直接访问的网址.带前缀 http://或者https://
     */
    public static function getPagelinkUrl($url_in_page, $url) {
        if ('http' != substr($url_in_page, 0, 4)) {
            // url前带 '/'，仅需加上域名即可
            if (substr($url_in_page, 0, 1) == '/') {
                $hostarray = explode('/', $url);
                $url_in_page = $hostarray[0] . '//' . $hostarray[2] . $url_in_page;
            } else {
                // url前不带 '/',使用相对路径
                $pageurls = pathinfo($url);
                $pageurls = $pageurls['dirname'];
                $url_in_page = $pageurls . '/' . $url_in_page;
            }
        }
        // 将空格替换成%20,rfc1738,rawurlencode
        $url_in_page = str_replace(' ', '%20', $url_in_page);
        return $url_in_page;
    }

    /**
     * 抓取url内容
     * @param $url 页面url地址
     * @param $options  抓取时其他参数
     * @return object Response
     */
    public static function getRomoteUrlContent($url, $options=array()) {
        $request = array_merge($options, array(
                    'header' => array(
                        'Referer' => $url,
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
                    ),
                ));
        $response =  RequestFacade::get($url, array(), $request);
        return $response->body;
    }

}