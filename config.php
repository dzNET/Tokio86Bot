<?php
// lib
include('db.php');

// Telegram URL, token, API url
define('TOKEN', '<TOKEN>');
define('URL', 'https://api.telegram.org/bot'.TOKEN.'/');
define('API', 'http://rest.tokio86.ru:5858/');

// connect DB
$botDB = new DataBase('localhost', 'username', 'password', 'database');

//
// send message to user

function send( $chat, $msg ){

  curlGET(
    URL."sendmessage?parse_mode=HTML&chat_id=".$chat."&text=".$msg
  );
}

// send message and keyboard to user

function send_keyb( $chat, $msg, $keyb ){

  $content = array(
    'parse_mode' => 'HTML',
    'chat_id' => $chat,
    'text' => $msg,
    'reply_markup' => keyboard($keyb)
  );

  curlGET(
    URL."sendmessage?".http_build_query( $content )
  );
}

function sendImg( $chat, $msg, $photo, $keyb ){

  $content = array(
    'chat_id' => $chat,
    'caption' => $msg,
    'photo' => $photo,
    'reply_markup' => keyboard($keyb)    
  );

  curlGET(
    URL."sendPhoto?".http_build_query( $content )
  );
}

// get full ithem from client API

function allMenuData ( $group = "group" ) {

  $url = API.'getallmenu'. $group;

  $data = curlGET( $url );

  return $data;
}

function categoryData ( $id ) {

  if ( $id == false ) {
    return false;
  }

  $url = API.'getmmgbyowner?ownerid='.$id;

  $data = curlGET( $url );

  $data = $data["MENU"];

  return $data;
}

function menuData( $menuID ){

  $url = API."getmenudata?menuid=".$menuID;

  $data = curlGET( $url );

  $str = $data[1]["MENUGROUPNAME"]." / ".$data[1]["NAME"]."\n".$data[1]["DESCRIPTION"]."\nвес: ".$data[1]["PORTIONWEIGHT"]." кг."."\nцена: ".$data[1]["COST"]." руб.";

  return $str;
}

function trashData( $id ){

  $url = API."getmenudata?menuid=".$id;

  $data = curlGET( $url );

  $trashStr = $data[1]["NAME"].", ".$data[1]["COST"]." руб.";

  return $trashStr;
}

function goodName( $goodID ){

  $url = API."getmenudata?menuid=".$goodID;

  $data = curlGET( $url );

  return $data[1]["NAME"];
}

function mutch ( $text, $data ) {

  foreach ( $data as $key => $val ) {

    if ( ( preg_match ( '/'.$text.'/iu', $val["NAME"] ) == 1 ) ) {
      return $val["ID"];
    }
  }

  return false;
}

function makeKeyboard ( $data ) {

  foreach ( $data as $key ) {

    if ( $i%2 == 0 ) {

      $buttons .= $key['NAME'].":";
    }
    else {

      $buttons .= $key['NAME']."::";
    }

    $i += 1;
  }

  $tmp = explode(
    "::",
    $buttons
  );

  foreach ( $tmp as $key ) {

    $board[] = explode(
      ":",
      $key
    );
  }

  return $board;
}

function trashCount( $chat, $botDB ) {

  $count = $botDB->select('bot_users', 'id', $chat, 'trash');

  if ($count['trash'] != "") {

    $num = count(
      explode(
        ":",
        trim(
          $count['trash'],
          ":"
        )
      )
    );
    return $num;
  }

  else {
    return "0";
  }
}

// Menu keyboard

function orderMenu( $chat, $botDB ){

  $orderMenu = array(
    array("меню", "корзина(".trashCount( $chat, $botDB ).")"),
    array("\xF0\x9F\x92\xB0 оформить", "\xE2\x9D\x8C отмена")
  );
  return $orderMenu;
}

function trashMenu(){

  $trashMenu = array(
    array("меню", "\xE2\xA4\xB4 последний товар"),
    array("\xF0\x9F\x92\xB0 оформить", "\xE2\x9D\x8C отмена")
  );
  return $trashMenu;
}

function defMenu( $chat, $botDB ){

  $defMenu = array(
    array("меню", "корзина(".trashCount( $chat, $botDB ).")"),
    array("\xE2\x9C\x85 заказать", "\xE2\x9D\x8C отмена")
  );
  return $defMenu;
}

function keyboard( $keyb ){

  $reply = array(
    'keyboard' => $keyb,
    'one_time_keyboard' => false,
    'resize_keyboard' => false,
    'selective' => true
  );
  return json_encode( $reply, true );
}

function makeParam( $chat, $user, $good='', $order='' ){

  $param = array(
    'id' => $chat,
    'name' => $user,
    'good' => $good,
    'trash' => $order
  );
  return $param;
}

function curlGET( $url ) {

  $menuIthem = curl_init(
    trim( $url )
  );

  curl_setopt(
    $menuIthem,
    CURLOPT_RETURNTRANSFER,
    true
  );

  $res = explode(
    "\nDATA=",
    curl_exec(
      $menuIthem
    )
  );

  curl_close ( $menuIthem );

  $data = json_decode( $res[1], true );

  return $data;
}
?>