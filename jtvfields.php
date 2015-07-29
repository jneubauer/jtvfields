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
    
        public function onContentBeforeSave($context, $article, $isNew)
        {

            $attribs = json_decode($article->attribs);

            //Let's check that the video_url field is filled in
            if(!empty($attribs->video_url)){

                jimport('joomla.filesystem.file');
                jimport( 'joomla.image.image' );

                if (!class_exists('JFolder')){
                    jimport('joomla.filesystem.folder');
                }

                //Set the path for the thumbnails
                $asset_dir = JPath::clean( JPATH_SITE.'/images/thumbs/' );
                
                $url_arr = parse_url($attribs->video_url);
                if ($url_arr['host']== 'vimeo.com')
                {
            
                    $v_id = $url_arr['path'];
                    $vim_id = substr($v_id, 1);
                    $vim_id = 'v' . $vim_id;
                    $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video$v_id.php"));
                    copy($hash[0]['thumbnail_large'], $asset_dir.$vim_id.'.jpg');
                    $attribs->video_id = $vim_id;
                }
                if ($url_arr['host'] == 'youtu.be')
                {
                    $v_id = $url_arr['path'];
                    $v_id = substr($v_id, 1);
                    $yt_id = 'y' . $v_id;
                    copy("http://i1.ytimg.com/vi/$v_id/0.jpg", $asset_dir.$yt_id.'.jpg');
                    $attribs->video_id = 'y' . $v_id;
                }
                if ($url_arr['host']== 'www.youtube.com')
                {
                    parse_str( parse_url( $attribs->video_url, PHP_URL_QUERY ), $yt_arr );
                    $v_id = $yt_arr['v'];
                    $yt_id = 'y' . $v_id;
                    copy("http://i1.ytimg.com/vi/$v_id/0.jpg", $asset_dir.$yt_id.'.jpg');
                    $attribs->video_id = $yt_id;
                }               
                
                //Set the new $attribs object for the article
                $article->attribs = json_encode($attribs);

            }

            return true;
        }
 
}
?>