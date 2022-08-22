<?php

namespace App;

use function Swoole\Coroutine\Http\get;


class Tiktok
{
    public string $url;

    function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * @throws \Swoole\Coroutine\Http\Client\Exception
     */
    public function getRedirectUrl(): string
    {
        preg_match("(\d{10,25})", $this->url, $matches);
        if ($matches) {
            return $matches[0];
        } else {
            $response = get($this->url);
            if($response and $response->getStatusCode() == 301){
                return $response->getHeaders()['location'];
            }else{
                return $this->url;
            }
        }
    }

    /**
     * @throws \Swoole\Coroutine\Http\Client\Exception
     */
    public function getAwemeId(): string|bool
    {
        $url = $this->getRedirectUrl();
        if (is_numeric($url)) return $url;
        preg_match("(\d{10,25})", $url, $matches);
        return $matches ? $matches[0] : false;
    }

    public function getInfo()
    {
        $aweme_id = $this->getAwemeId();
        $data_url = "https://api-h2.tiktokv.com/aweme/v1/aweme/detail/?aweme_id=$aweme_id";
        $response = get($data_url);
        $datas = json_decode($response->getBody());
        if(isset($datas->aweme_detail)){
            $desc = $datas->aweme_detail->desc;

            $video_data = $datas->aweme_detail->video;
            $duration = $video_data->duration;
            $thumbnail = end($video_data->cover->url_list);

            $video_addr = $video_data->play_addr;
            $file_size = $video_addr->data_size;
            $video_url = end($video_addr->url_list);

            $music_data = $datas->aweme_detail->music;
            $music_is_orginal = $music_data->is_original_sound;
            $music_uri = $music_data->play_url->uri;

            $data = array(
                'ok'=>true,
                'description'=>$desc,
                'video_url'=>$video_url,
                'cover'=>$thumbnail,
                'duration'=>$duration,
                'music_url'=> $music_uri,
                'music_orginal'=>$music_is_orginal,
                'file_size'=>$file_size
            );
        }else{
            $data = array('ok'=> false, 'message'=>'Video not found!');
        }
        return json_encode($data);
    }
}