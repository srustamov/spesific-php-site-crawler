<?php


use PHPHtmlParser\Dom;
use Carbon\Carbon;

class Crawler
{

    private $url;

    private $loop = false;

    private $sleep = 10;

    private $index;

    private $selector;

    private $enable_log = false;

    private static $last_time;

    private static $content;


    public static function setContent($content)
    {
        self::$content = $content;
    }


    public static function getContent()
    {
        return self::$content;
    }


    public static function assertEqualsContent(string $content):bool
    {
        if(!self::getContent()) {
            self::setContent($content);
            return true;
        }

        $assert = self::getContent() === $content;

        self::setContent($content);

        return $assert;
    }


    public static function startRequest()
    {
        printf("\n %s : request start... \n",Carbon::now());
    }

    public static function finishRequest()
    {
        printf("\n %s : request finish... \n",Carbon::now());
    }

    public static function setLastTime($time)
    {
        static::$last_time = $time;
    }

    public static function getLastTime()
    {
        return static::$last_time ?? Carbon::now();
    }

    public function url(string $url)
    {
        $this->url = $url;

        return $this;
    }

    public function selector(string $selector,int $index = null)
    {
        $this->selector = $selector;

        $this->index = $index;

        return $this;
    }


    public function watcherChangeContent(int $interval = null)
    {
        $this->loop = true;

        if ($interval) {
            $this->sleep = $interval;
        }

        return $this;
    }

    public function enableLog()
    {
        $this->enable_log = true;

        return $this;
    }


    public function interval(int $seconds)
    {
        $this->sleep = $seconds;

        return $this;
    }


    public function run()
    {
        if ($this->loop) {
            print("\n ============= Watching... ============== \n");
            while (true) {

                if($parsing = $this->parsing()) {
                    if(is_array($parsing)) {
                        $content = serialize($parsing);
                    } else {
                        $content = $parsing->text;
                    }

                    if ($content && !self::assertEqualsContent($content)) {
                        if ($this->enable_log) {
                            print("\n********* Change content detected! ************\n");
                            print(PHP_EOL . 'Content : ' . mb_substr($content,0,100) . PHP_EOL);
                            printf("\n Last change time : %s \n",self::getLastTime());
                            printf("\n Current time : %s \n",self::getLastTime());
                        }
                        self::setLastTime(\Carbon\Carbon::now());
                    }
                }
                sleep($this->sleep);
            }
        } else {
            return $this->parsing();
        }
    }


    public function parsing()
    {
        $dom = new Dom();

        try
        {
            $dom->load($this->request($this->url));

            return $dom->find($this->selector, $this->index);

        } catch (\PHPHtmlParser\Exceptions\ChildNotFoundException $e) {
        } catch (\PHPHtmlParser\Exceptions\CircularException $e) {
        } catch (\PHPHtmlParser\Exceptions\CurlException $e) {
        } catch (\PHPHtmlParser\Exceptions\StrictException $e) {
        } catch (\PHPHtmlParser\Exceptions\NotLoadedException $e) {}

        return null;
    }


    public static function init()
    {
        return new static;
    }



    private function request(string $url)
    {
        if ($this->enable_log) {
            self::startRequest();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '&no_cache=' . time());
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Language: es-es,en',
            'Cache-Control: no-cache'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($this->enable_log) {
            self::finishRequest();
        }

        return $result;
    }


}