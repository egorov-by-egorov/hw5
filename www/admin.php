<form method="post" action="admin.php">
    <button value="5" type="submit" name="button">Список всех пользователей</button>
    <button value="6" type="submit" name="button">Список всех заказов</button>
</form>
<?php

include __DIR__ . "/../src/functions.php";

connect();

if ($_POST['button'] == 5) {
    $sth = $db->prepare("SELECT * FROM `users`");
    $sth->execute();
    $array = $sth->fetchAll(PDO::FETCH_ASSOC);

    foreach ($array as $item) {
        echo "<table border='1px'>
              <tr>
                  <td >id=>" . $item["id"] . "</td>
                  <td>name=>" . $item["name"] . "</td>
                  <td>phone=>" . $item["phone"] . "</td>
                  <td>email=>" . $item["email"] . "</td>
              </tr>
              </table>";
    }
}

if ($_POST['button'] == 6) {
    $sth = $db->prepare("SELECT * FROM `orders`");
    $sth->execute();
    $array = $sth->fetchAll(PDO::FETCH_ASSOC);

    foreach ($array as $item) {
        echo "<table border='1px'>
              <tr>
                  <td >id=>" . $item["id"] . "</td>
                  <td >Userid=>" . $item["userid"] . "</td>
                  <td>address=>" . $item["address"] . "</td>
                  <td>comment=>" . $item["comments"] . "</td>
                  <td>payment=>" . $item["payment"] . "</td>
                  <td>callback=>" . $item["callback"] . "</td>
              </tr>
              </table>";
    }
}

?>