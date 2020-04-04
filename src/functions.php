<?php

require_once '../vendor/autoload.php';

use Intervention\Image\ImageManager;

function connect()
{
    global $db;
    $user = "root";
    $pass = "root";
    $dbname = "cd46185_develop";
    $db = new PDO('mysql:host=localhost;dbname=' . $dbname, $user, $pass);
}

function main()
{
    global $street, $home, $part, $appt, $floor, $comment, $payment, $callback, $submit, $errors;
    $errors = [];
    $street = trim($_POST["street"]);
    $street = htmlspecialchars($street);
    $home = trim($_POST["home"]);
    $home = htmlspecialchars($home);
    $part = trim($_POST["part"]);
    $part = htmlspecialchars($part);
    $appt = trim($_POST["appt"]);
    $appt = htmlspecialchars($appt);
    $floor = trim($_POST["floor"]);
    $floor = htmlspecialchars($floor);

    if ($floor == "") {
        $floor = "Not indicated";
    }

    $comment = trim($_POST["comment"]);
    $comment = htmlspecialchars($comment);

    if ($comment == "") {
        $comment = "Not indicated";
    }

    $payment = $_POST["payment"];

    if ($payment == "") {
        $payment = "Not indicated";
    }

    $callback = $_POST["callback"];

    if ($callback == "") {
        $callback = "Not indicated";
    }

    $submit = $_POST["submit"];


}

function getUser($db)
{
    global $name, $phone, $email, $user_email, $user_id;

    $name = trim($_POST["name"]);
    $name = htmlspecialchars($name);
    $phone = trim($_POST["phone"]);
    $phone = htmlspecialchars($phone);
    $email = trim($_POST["email"]);
    $email = htmlspecialchars($email);
    $user_email = $email;
    $sth = $db->prepare("SELECT `id` FROM users` WHERE `email`=?");
    $sth->execute([$email]);
    $user = $sth->fetchAll(PDO::FETCH_ASSOC);
    $user_id = $user["0"]["id"];

}

function addUser($email, $name, $phone, $db, $submit, $street, $home, $appt, $errors)
{
    global $errors;
    global $new_orders;
    global $new_email;

    if (isset($submit)) {
        if ($email == "") {
            $errors[] = "<h3 style='color: red'>Введите email</h3>";
        }
        if (empty($errors) && !empty($street) && !empty($home) && !empty($appt)) {
            $sth = $db->prepare("SELECT * FROM `users`");
            $sth->execute();
            $array = $sth->fetchAll(PDO::FETCH_ASSOC);
            foreach ($array as $value) {
                if ($value["email"] == trim($email)) {
                    $value["orders"] += 1;
                    $new_orders = $value["orders"];
                    $new_email = $value["email"];
                    echo $new_orders . "<br>";
                    echo $new_email;
                }
            }
            $sql = "INSERT INTO `users` (`email`, `name`, `phone`) VALUES (?,?,?)";
            $stm = $db->prepare($sql);
            $stm->execute([$email, $name, $phone]);
        }
    }
}

function addOrder($street, $home, $part, $appt, $user_email, $db, $submit, $floor, $comment, $payment, $callback, $errors, $email, $user_id)
{
    if (isset($submit)) {
        if ($street == "") {
            $errors[] = "<h3 style='color: red'>Введите улицу</h3>";
        }
        if ($home == "") {
            $errors[] = "<h3 style='color: red'>Введите номер дома</h3>";
        }
        if ($appt == "") {
            $errors[] = "<h3 style='color: red'>Введите номер квартиры</h3>";
        }
        if (empty($errors)) {
            $sql = "INSERT INTO `orders` (`street`, `home`, `part`, `appt`, `floor`, `comment`, `payment`, `callback`, `user_email`,`user_id`) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $stm = $db->prepare($sql);
            $stm->execute([$street, $home, $part, $appt, $floor, $comment, $payment, $callback, $user_email, $user_id]);
            sendMail($email, $db, $user_id);
            header("Location: " . $_SERVER["REQUEST_URI"]);
        }
    }
    if (!empty($errors)) {
        echo array_shift($errors);
    }
}

function sendMail($email, $db, $user_id)
{
    $to = $email;
    $sth = $db->prepare("SELECT O.id,O.street,O.home,O.part,O.appt,O.floor,U.orders FROM orders as O INNER JOIN users AS U ON O.user_id=U.id WHERE U.id=?  ORDER BY O.id DESC LIMIT 1");
    $sth->execute([$user_id]);
    $array = $sth->fetchAll(PDO::FETCH_ASSOC);
    global $order;

    if ($array['0']['orders'] == "1") {
        $order = "ваш первый";
    } else {
        $order = "уже " . $array['0']['orders'];
    }
    $transport = new Swift_SmtpTransport('smtp.mail.ru', 465, 'ssl');
    $transport->setUsername('user');
    $transport->setPassword('password');
    $mailer = new Swift_Mailer($transport);
    $message = new Swift_Message();
    $message->setSubject("Заказ # " . $array['0']['id'] . ". \n");
    $message->setFrom(['user' => 'user']);
    $message->addTo('user', 'user');
    $message->setBody("Ваш заказ будет доставлен по адресу : Улица - " . $array['0']['street'] . " Дом - " . $array['0']['home'] . " Корпус - "
        . $array['0']['part'] . " Квартира - " . $array['0']['appt'] . ".\nСодержимое заказа: DarkBeefBurger за 500 рублей, 1 шт.\n" .
        "Спасибо - это " . $order . " заказ.");
    $mailer->send($message);
}

function imageAction()
{
    $manager = new ImageManager(array('driver' => 'gd'));

    $source = "img/1.jpeg";
    $image = $manager->make($source)
        ->resize(200, null, function ($image) {
            $image->aspectRatio();
        })
        ->rotate(45)
        ->blur(1);

    $image->text("WATERMARK",
        $image->width() / 2,
        $image->height() / 2,
        function ($font) {
            $font->color(array(255, 0, 0, 0.5));
            $font->align("right");
            $font->valign("center");
        });
    $image->save("img/1_new.jpeg", 80);
//    include_once "../www/image.php";
}




