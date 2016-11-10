<?php
//
// function core
include('config.php');

// Bot input
$bot = json_decode(file_get_contents("php://input"), true);
$chat = $bot["message"]["chat"]["id"];
$user = $bot["message"]["chat"]["last_name"].' '.$bot["message"]["chat"]["first_name"];
$text = $bot["message"]["text"];

// Massage to user
$startMsg = "Привет, ".$user."!";
$stopMsg = "Хорошего дня, ".$user."!";
$wcmdMsg = "Эта команда  пока мне не знакома. Понимаю навигацию по кнопкам и сообщения: меню, пицца, филадельфия, корзина. Чтобы сделать заказ, открывайте товары в меню и добавляйте в корзину";
$menuMsg = "Меню к вашим услугам";
$orderMsg = "Товар в корзине";
$trashMsg = "У Вас в корзине:\n\n";
$helpMsg = "Понимаю навигацию по кнопкам и сообщения: меню, пицца, филадельфия, корзина. Чтобы сделать заказ, открывайте товары в меню и добавляйте в корзину";

// Check Input
// -- system command

if ( $text == "/start" ){

  if ( $botDB->select('bot_users', 'id', $chat) == null ) {

    $botDB->insert(
      'bot_users',
      makeParam(
        $chat,
        $user
      )
    );
  }
  else {
    $botDB->update(
      'bot_users',
      makeParam(
        $chat,
        $user
      )
    );
  }

  send(
    $chat,
    $startMsg
  );
}

elseif ( $text == "/help" ){

  send(
    $chat,
    $helpMsg
  );
}

elseif ( $text == "/stop" ){

  $botDB->delete( 'bot_users', $chat );

  send(
    $chat,
    $stopMsg
  );
}

// main menu

elseif ( preg_match( '/меню\z/iu', $text ) == 1 ){

  send_keyb(
    $chat,
    $menuMsg,
    makeKeyboard(
      allMenuData()
    )
  );
}

elseif ( mutch( $text, allMenuData()) ) {

  send_keyb(
    $chat,
    $menuMsg,
    makeKeyboard(
      categoryData(
        mutch(
          $text,
          allMenuData()
        )
      )
    )
  );
}

elseif ( mutch( $text, allMenuData(null)) ) {

  $botDB->update(
    'bot_users',
    makeParam(
      $chat,
      $user,
      mutch(
        $text,
        allMenuData(null)
      ),
      $botDB->select('bot_users', 'id', $chat)['trash']
    )
  );

  sendImg(
    $chat,
    menuData(
      mutch(
        $text,
        allMenuData(null)
      )
    ),
    $botDB->getIMG($text),
    defMenu($chat, $botDB)
  );
}

elseif ( preg_match( '/заказать\z/iu', $text ) == 1 ){

  $last = $botDB->select('bot_users', 'id', $chat)['good'];

  if ($last != "") {

    $botDB->update(
      'bot_users',
      makeParam(
        $chat,
        $user,
        $last,
        $botDB->select('bot_users', 'id', $chat)['trash'].$last.":"
      )
    );

    send_keyb(
      $chat,
      $orderMsg,
      orderMenu($chat, $botDB)
    );
  }

  else {
    send_keyb(
      $chat,
      "вы не выбрали товар!",
      makeKeyboard(
        allMenuData()
      )
    );
  }
}

elseif ( preg_match( '/корзина(?)/iu', $text ) == 1 ){

  $trash = $botDB->select('bot_users', 'id', $chat)['trash'];

  if ($trash != "") {

    foreach ( explode( ":", trim( $trash, ":" ) ) as $good ) {
      $trashMsg .= trashData( $good )."\n";
    }

    send_keyb(
      $chat,
      $trashMsg,
      trashMenu()
    );
  }

  else {
    send_keyb(
      $chat,
      "корзина пока что пуста",
      makeKeyboard(
        allMenuData()
      )
    );
  }
}

elseif ( preg_match( '/последний товар/iu', $text ) == 1 ){

  $last = $botDB->select('bot_users', 'id', $chat)['good'];

  if ($last != "") {

    sendImg(
      $chat,
      menuData($last),
      $botDB->getIMG(goodName($last)),
      defMenu($chat, $botDB)
    );
  }

  else {
    send_keyb(
      $chat,
      "вы не выбрали товар!",
      makeKeyboard(
        allMenuData()
      )
    );
  }
}

elseif ( preg_match( '/отмена/iu', $text ) == 1 ){

  $botDB->update(
    'bot_users',
    makeParam(
      $chat,
      $user,
      '',
      ''
    )
  );

  send_keyb(
    $chat,
    "корзина очищена",
    makeKeyboard(
      allMenuData()
    )
  );
}

else {

  send_keyb(
    $chat,
    $wcmdMsg,
    makeKeyboard(
      allMenuData()
    )
  );
}
// 
// end Check Input
//
//
//
// dubug example  (visit webhook page)
//
//
//
/*
var_dump( mutch( 'детское меню', allMenuData()) );
var_dump( $botDB->getIMG('Эби') );
var_dump($botDB->select('bot_users', 'id', '166437541'));
var_dump(trashCount( '166437541', $botDB ));
$ww = $botDB->select('bot_users', 'id', '166437541', 'good');
var_dump( $ww['good'] );
*/
?>