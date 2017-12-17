<?php

require_once 'api.php';

$service = isset($_GET['service']) ? $_GET['service']: 'bilibili'; //bilibili, netease, zhihu
$restyped = isset($_GET['restype']) ? $_GET['restype']: 'video'; //video; artist, song, album, playlist; user, answer
$resuid = isset($_GET['resid']) ? $_GET['resid']: '7248433'; //UID of specified target
$actiontype = isset($_GET['action']) ? $_GET['action']: 'avail'; //required resource of specified target


if ($service === 'netease') {
  $out = parseNeteaseAPIDocument($restyped, $resuid, $actiontype);
}
else if ($service === 'bilibili' && $restyped === 'video') {
  $out = parseBilibiliAPIDocument($resuid, $actiontype);
}
else {
  $color_name = 'lightgrey';
  $subject = '服务';
  $status = '名称不正确。';
  $out = array($color_name, $subject, $status);
}

$co = $out[0];
$su = $out[1];
$st = $out[2];

drawBadge($su, $st, $co);

/***
actions include:
- bilibili:
  - avail: returns availability of a video set;
  - danmaku: returns danmaku count of a video set;
  - shares: returns shares count of a video set;
  - coins: returns coins count of a video set;
  - favs: returns favs count of a video set.
- netease:
  - artist
    - name
    - albums count
    - songs count
  - song
    - name
    - artists
    - albumname
  - albums
    - name
    - comments: comments count of the album
    - favs: favs count of playlist
  - playlist
    - name
    - playcount
    - sharecount
    - commentcount(comments)
    - songcount(songs)
***/

/***
$status = isset($_GET['status']) ? $_GET['status']: 'OK';
$color = isset($_GET['color']) ? $_GET['color']: 'lightgrey';
***/
function parseBilibiliAPIDocument($resid, $action) {
    $bilibiliRequest = 'https://api.bilibili.com/archive_stat/stat?aid=' . $resid . '&type=jsonp&_=1482889080665';
    $biliRawDocument = file_get_contents($bilibiliRequest);
    $arrayBiliRawDocument = json_decode($biliRawDocument);
    $avState = $arrayBiliRawDocument -> code;
    if ($avState == '0') {
      if ($action === 'avail') {
        $color_name = 'bilibili_pink';
        $subject = '哔哩哔哩';
        $status = 'av' . $arrayBiliRawDocument -> data -> aid ;
      }
      else if ($action === 'danmaku') {
        $color_name = 'bilibili_blue';
        $subject = '弹幕';
        $status = $arrayBiliRawDocument -> data -> danmaku;
      }
      else if ($action === 'shares') {
        $color_name = 'bilibili_green';
        $subject = '分享';
        $status = $arrayBiliRawDocument -> data -> share;
      }
      else if ($action === 'coins'){
        $color_name = 'bilibili_yellow';
        $subject = '硬币';
        $status = $arrayBiliRawDocument -> data -> coin;
      }
      else if ($action === 'favs'){
        $color_name = 'bilibili_pink';
        $subject = '收藏';
        $status = $arrayBiliRawDocument -> data -> favorite;
      }
      else {
        $color_name = 'lightgrey';
        $subject = '哔哩哔哩';
        $status = '参数不正确。';
      }
    }
    else {
        $color_name = 'lightgrey';
        $subject = '哔哩哔哩';
        $status = '视频不可用。';
    }
    return array($color_name, $subject, $status);
  }

function parseNeteaseAPIDocument($restype, $resid, $action) {
  if ($restype === 'artist') {
    $neRequest = 'https://napi.duoee.cn/?type=artist&id=' . $resid . '&limit=0';
    $neRawDocument = file_get_contents($neRequest);
    $arrayNeRawDocument = json_decode($neRawDocument);
    if ($action === 'name') {
      $subject = '网易云音乐';
      $status = $arrayNeRawDocument -> artist -> name;
      $color_name = 'netease_red';
    }
    else if ($action === 'albums') {
      $subject = '专辑';
      $status = $arrayNeRawDocument -> artist -> albumSize;
      $color_name = 'netease_red';
      }
    else if ($action === 'songs') {
      $subject = '曲目';
      $status = $arrayNeRawDocument -> artist -> musicSize;
      $color_name = 'netease_red';
      }
    else {
      $color_name = 'lightgrey';
      $subject = '网易云音乐';
      $status = '参数不正确。';
      }
    }
  else if ($restype === 'song') {
    $neRawDocument = file_get_contents('https://napi.duoee.cn/?type=song&id=' . $resid . '&limit=0');
    $arrayNeRawDocument = json_decode($neRawDocument);
    if ($action === 'name') {
      $subject = '网易云音乐';
      $middleVar = $arrayNeRawDocument -> songs[0];
      $status = $middleVar -> name;
      $color_name = 'netease_red';
      }
    else if ($action === 'albumname') {
      $subject = '专辑';
      $middleVar = $arrayNeRawDocument -> songs[0];
      $status = $middleVar -> album;
      $color_name = 'netease_red';
      }
    else if ($action === 'artists') {
      $middleVar = $arrayNeRawDocument -> songs[0];
      foreach ($middleVar -> artists as $artistArray) {
        $artistString = $artistArray -> name;
        $artistOutput = $artistString . '/' ;
        $artistTrim = $artistTrim . $artistOutput;
        }
      $artistFinalize = rtrim($artistTrim, "/ ");
      $subject = '艺术家';
      $status = $artistFinalize;
      $color_name = 'netease_red';
      //上面这堆鬼玩意写死我了
      }
    else {
      $color_name = 'lightgrey';
      $subject = '网易云音乐';
      $status = '参数不正确。';
        }
      }
    else if ($restype === 'album') {
      $neRequest = 'https://napi.duoee.cn/?type=album&id=' . $resid . '&limit=0';
      $neRawDocument = file_get_contents($neRequest);
      $arrayNeRawDocument = json_decode($neRawDocument);
      if ($action = 'artist') {
        $subject = '艺术家';
        $status = $arrayNeRawDocument -> album -> artist -> name;
        $color_name = 'netease_red';
        }
      else if ($action === 'songs') {
        $subject = '歌曲';
        $status = $arrayNeRawDocument -> album -> size;
        $color_name = 'netease_red';
        }
      else if ($action === 'comments') {
        $subject = '评论';
        $status = $arrayNeRawDocument -> album -> info -> commentCount;
        $color_name = 'netease_red';
        }
      else if ($action === 'likes') {
        $subject = '收藏';
        $status = $arrayNeRawDocument -> album -> info -> likedCount;
        $color_name = 'netease_red';
        }
      else if ($action === 'shares') {
        $subject = '分享';
        $status = $arrayNeRawDocument -> album -> info -> shareCount;
        $color_name = 'netease_red';
        }
      else {
        $subject = '网易云音乐';
        $status = '参数不正确。';
        $color_name = 'lightgrey';
        }
      }
    else if ($restype === 'playlist') {
      $neRequest = 'https://napi.duoee.cn/?type=playlist&id=' . $resid . '&limit=0';
      $neRawDocument = file_get_contents($neRequest);
      $arrayNeRawDocument = json_decode($neRawDocument);
      if ($action = 'name') {
        $subject = '艺术家';
        $status = $arrayNeRawDocument -> result -> name;
        $color_name = 'netease_red';
      }
      else if ($action === 'songs') {
        $subject = '歌曲';
        $status = $arrayNeRawDocument -> result -> trackCount;
        $color_name = 'netease_red';
        }
      else if ($action === 'comments') {
        $subject = '评论';
        $status = $arrayNeRawDocument -> result -> commentCount;
        $color_name = 'netease_red';
        }
      else if ($action === 'likes') {
        $subject = '收藏';
        $status = $arrayNeRawDocument -> result -> likedCount;
        $color_name = 'netease_red';
        }
      else if ($action === 'shares') {
        $subject = '分享';
        $status = $arrayNeRawDocument -> result -> shareCount;
        $color_name = 'netease_red';
        }
      else {
        $subject = '网易云音乐';
        $status = '参数不正确。';
        $color_name = 'lightgrey';
        }
      }
      return array($color_name, $subject, $status);
    }

?>
