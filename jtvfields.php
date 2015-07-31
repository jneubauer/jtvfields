<?php
// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
class plgContentJtvfields extends JPlugin {
 
        /**
         * Load the language file on instantiation.
         * Note this is only available in Joomla 3.1 and higher.
         * If you want to support 3.0 series you must override the constructor
         *
         * @var boolean
         * @since 3.1
         */
 
        protected $autoloadLanguage = true;
        function onContentPrepareForm($form, $data) {
                $app = JFactory::getApplication();
                $option = $app->input->get('option');
 
                switch($option) {
                    case 'com_content':
                        if ($app->isAdmin()) {
							JForm::addFormPath(__DIR__ . '/forms/');
							//Show specific forms based on categories
							$form->loadFile('content', false);     
                        }
                        if ($app->isSite()) {
							JForm::addFormPath(__DIR__ . '/forms');
							//Show specific forms based on categories
							$form->loadFile('content', false);
                        }
                        return true;
                }
                return true;
        }
        public function onContentBeforeSave($context, $article, $isNew) {
            $attribs = json_decode($article->attribs);
            //Let's check that the video_url field is filled in
            if(!empty($attribs->tv_video_url)){
                //Set the path for the thumbnails
                $asset_dir = JPath::clean( JPATH_SITE.'/images/thumbs/' );
                $url_arr = parse_url($attribs->tv_video_url);
                if ($url_arr['host']== 'vimeo.com')
                {
                    $hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video'.$url_arr['path'].'.php'));
                    copy($hash[0]['thumbnail_large'], $asset_dir.'v'.substr($url_arr['path'], 1).'.jpg');
                    $attribs->tv_video_id = 'v'.substr($url_arr['path'], 1);
                }
                if ($url_arr['host'] == 'youtu.be')
                {
                    copy("http://i1.ytimg.com/vi/$v_id/mqdefault.jpg", $asset_dir.'y'.substr($url_arr['path'], 1).'.jpg');
                    $attribs->tv_video_id = 'y'.$v_id;
                }
                if ($url_arr['host']== 'www.youtube.com')
                {
                    parse_str( parse_url($attribs->tv_video_url, PHP_URL_QUERY ), $yt_arr );
                    copy("http://i1.ytimg.com/vi/$v_id/mqdefault.jpg", $asset_dir.'y'.$yt_arr['v'].'.jpg');
                    $attribs->tv_video_id = 'y'.$yt_arr['v'];
                }
                //Set the new $attribs object for the article
                $article->attribs = json_encode($attribs);
            }
            return true;
        }
}
?>