<?php

namespace Bolt\Extension\SahAssar\TwitterEmbeds;

use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Asset\Snippet\Snippet;
use Bolt\Extension\SimpleExtension;

class TwitterEmbedsExtension extends SimpleExtension
{
    protected function registerAssets()
    {
        $asset = new Snippet();
        $asset->setCallback([$this, 'twitterScript'])
            ->setLocation(Target::END_OF_BODY)
            ->setZone(Zone::FRONTEND)
            ->setPriority(99);

        return [
            $asset,
        ];
    }

    protected function registerTwigFunctions()
    {
        return [
            'twitter_share' => ['twitterShare', ['is_variadic' => true]],
            'twitter_follow' => ['twitterFollow', ['is_variadic' => true]],
            'twitter_hashtag' => ['twitterHashtag', ['is_variadic' => true]],
            'twitter_mention' => ['twitterMention', ['is_variadic' => true]],
            'twitter_feed' => ['twitterFeed', ['is_variadic' => true]]
        ];
    }

    public function twitterScript()
    {
        $html = <<< EOM
        <script>if(!!(document.getElementsByClassName("twitter-share-button").length || document.getElementsByClassName("twitter-follow-button").length || document.getElementsByClassName("twitter-hashtag-button").length || document.getElementsByClassName("twitter-mention-button").length || document.getElementsByClassName("twitter-timeline").length)){
        (function (d, s, id) {
        !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
        })()};</script>
EOM;
        return $html;
    }
    
    function twitterShare(array $args = array())
    {
        $app = $this->getContainer();
        $defaults = array(
              'url' => $app['resources']->getUrl('canonicalurl'),
              'text' => '',
              'via' => '',
              'large' => false,
              'related' => '',
              'hashtags' => '',
        );
        $args = array_merge($defaults, $args);
        $html = <<< EOM
        <a
        href="https://twitter.com/share"
        class="twitter-share-button"
        data-url="%url%"
        data-text="%text%"
        data-via="%via%"
        data-size="%size%"
        data-related="%related%"
        data-hashtags="%hashtags%"
        data-dnt="true">Tweet</a>
EOM;
        $html = str_replace("%url%", $args['url'], $html);
        $html = str_replace("%text%", $args['text'], $html);
        $html = str_replace("%via%", $args['via'], $html);
        $html = str_replace("%related%", $args['related'], $html);
        $html = str_replace("%large%", ($args['large'] ? 'large' : ''), $html);
        $html = str_replace("%related%", $args['related'], $html);
        $html = str_replace("%hashtags%", $args['hashtags'], $html);

        return new \Twig_Markup($html, 'UTF-8');
    }
    
    function twitterFollow(array $args = array())
    {
        $defaults = array(
              'follow' => '',
              'show_count' => true,
              'large' => false,
              'text' => 'follow'
        );
        $args = array_merge($defaults, $args);
        $html = <<< EOM
        <a
        href="https://twitter.com/%follow%"
        class="twitter-follow-button"
        data-show-count="%show_count%"
        data-size="%large%"
        data-dnt="true">Follow @%follow%</a>
EOM;
        $html = str_replace("%follow%", $args['follow'], $html);
        $html = str_replace("%show_count%", ($args['show_count'] ? 'true' : 'false'), $html);
        $html = str_replace("%large%", ($args['large'] ? 'large' : ''), $html);
        $html = str_replace("%text%", $args['text'], $html);

        return new \Twig_Markup($html, 'UTF-8');
    }
    
    function twitterHashtag(array $args = array())
    {
        $app = $this->getContainer();
        $defaults = array(
              'hashtag' => '',
              'text' => '',
              'large' => false,
              'recommend' => '',
              'url' => $app['resources']->getUrl('canonicalurl'),
        );
        $args = array_merge($defaults, $args);
        $html = <<< EOM
        <a
        href="https://twitter.com/intent/tweet?button_hashtag=%hashtag%&text=%text%"
        class="twitter-hashtag-button"
        data-size="%large%"
        data-related="%recommend%"
        data-url="%url%"
        data-dnt="true">Tweet #%hashtag%</a>
EOM;
        
        $html = str_replace("%hashtag%", urlencode($args['hashtag']), $html);
        $html = str_replace("%text%", urlencode($args['text']), $html);
        $html = str_replace("%large%", ($args['large'] ? 'large' : ''), $html);
        $html = str_replace("%recommend%", $args['recommend'], $html);
        $html = str_replace("%url%", $args['url'], $html);

        return new \Twig_Markup($html, 'UTF-8');
    }
    
    function twitterMention(array $args = array())
    {
        $defaults = array(
              'screen_name' => '',
              'text' => '',
              'large' => false,
              'related' => '',
        );
        $args = array_merge($defaults, $args);
        $html = <<< EOM
        <a
        href="https://twitter.com/intent/tweet?screen_name=%screen_name%&text=%text%"
        class="twitter-mention-button"
        data-size="%large%"
        data-related="%related%"
        data-dnt="true">Tweet to @%screen_name%</a>
EOM;
        
        $html = str_replace("%screen_name%", urlencode($args['screen_name']), $html);
        $html = str_replace("%text%", urlencode($args['text']), $html);
        $html = str_replace("%large%", ($args['large'] ? 'large' : ''), $html);
        $html = str_replace("%related%", $args['related'], $html);

        return new \Twig_Markup($html, 'UTF-8');
    }
    
    function twitterFeed(array $args = array())
    {
        $app = $this->getContainer();
        $defaults = array(
            'screen_name' => 'twitter',
            'text' => 'Tweets by',
            'widget_id' => '',
            'return_data' => false,
            'count' => 10,
            'access_token' => $app['config']->get('general/twitter_access_token'),
            'api_key' => $app['config']->get('general/twitter_api_key'),
            'api_secret' => $app['config']->get('general/twitter_api_secret')
        );
        
        $args = array_merge($defaults, $args);
        
        if($args['return_data']){
            return $this->twitterQuery($args);
        }
        
        $html = <<< EOM
        <a
        class="twitter-timeline"
        href="https://twitter.com/%screen_name%"
        data-widget-id="%widget_id%">%text% @%screen_name%</a>
EOM;
        
        $html = str_replace("%screen_name%", $args['screen_name'], $html);
        $html = str_replace("%text%", $args['text'], $html);
        $html = str_replace("%widget_id%", $args['widget_id'], $html);

        return new \Twig_Markup($html, 'UTF-8');
    }
    
    function twitterQuery(array $args = array())
    {
        $app = $this->getContainer();

        $cachekey = 'Twittertimeline'.$args['count'].$args['screen_name'];
        
        $res = $app['cache']->fetch($cachekey);
        
        if (!$args['access_token'] && $res === false) {
            $args['access_token'] = $app['cache']->fetch('twitter_access_token');

            if ($access_token === false){

                $api_credentials = base64_encode($args['api_key'].':'.$args['api_secret']);
                try {
                    $access_token = $app['guzzle.client']->post(
                        $auth_url, [
                        'body' => ['grant_type' => 'client_credentials'],
                        'headers' => ['Authorization' => 'Basic ' . $api_credentials]
                    ])->getBody(true);
                } catch (\Exception $e) {
                    return ['error' =>  $e->getMessage()];
                }
                $access_token = json_decode($access_token, true);
                $args['access_token'] = $access_token['access_token'];
                $app['cache']->save('twitter_access_token', $res['access_token'], 7884000);
            }
        }

        if ($res === false) {
            $url = sprintf (
                'https://api.twitter.com/1.1/statuses/user_timeline.json?count=%s&screen_name=%s',
                $args['count'],
                urlencode($args['screen_name'])
            );
            try {
                $res = $app['guzzle.client']->get(
                    $url, [
                    'headers' => ['Authorization' => 'Bearer ' . $args['access_token']]
                ])->getBody(true); 
            } catch (\Exception $e) {
                return ['error' =>  $e->getMessage()];
            }
            $res = json_decode($res, true);
            $app['cache']->save($cachekey, $res, 7200);
        }
        return $res;
    }
}
