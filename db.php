
<?php

class DataBase
{
  
  function __construct( $host, $user, $pass, $base ) {

    $this->mysqli = new mysqli($host, $user, $pass, $base);

    if ($this->mysqli->connect_error) {
      die('!!!###>>>'.$this->mysqli->connect_error);
    }

    $this->mysqli->query("SET NAMES UTF8");
  }

  function insert( $tab, $param ){

    $ins = implode("', '", $param);
    $sql = "insert into $tab values ('$ins')";

    if ($res = $this->mysqli->query($sql)) {

      $this->mysqli->close;
      return true;
    }
    else return false;
  }

  function select( $tab, $ithem, $val, $col='*'){

    $query  = "select $col from $tab where $ithem=$val";

    if ($res = $this->mysqli->query($query)) {
      if ($res->num_rows > 0) {

        $result = $res->fetch_assoc();
        $this->mysqli->close;
        return $result;
      }
    }
    else return false;
  }

  function update( $tab, $param ){

    $update = "";

    foreach ($param as $k => $v) {

      if ($k != 'trash') {
        $update .= $k."='".$v."', ";
      }
      else {
        $update .= $k."='".$v."'";
      }
    }

    $sql = "update $tab set $update where id={$param['id']}";

    if ($this->mysqli->query($sql) === TRUE) {
      return true;
    } else {
      return $this->mysqli->error;
    }
  }

  function delete( $tab, $id ){

    $sql = "delete from $tab where id='$id'";

    if ($res = $this->mysqli->query($sql)) {

      $this->mysqli->close;
      return true;
    }
    else return false;
  }

  function getImg( $text ){

    $img = new mysqli( 'localhost', 'username', 'password', 'database' );
    $img->query("SET NAMES UTF8");

    $query  = "select b.SUBDIR, b.FILE_NAME from b_iblock_element a inner join b_file b on (b.ID=a.PREVIEW_PICTURE) where a.NAME='$text'";

    if ($res = $img->query($query)) {
      if ($res->num_rows > 0) {

        $result = $res->fetch_row();
        $img->close;

        return 'https://tokio86.ru/upload/resize_cache/'.$result[0].'/368_328_0/'.$result[1];
      }
    }
    else return false;
  }
}
?>