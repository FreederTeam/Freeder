<?php
$install_template =
'
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta charset="utf-8"/>
</head>
<body>
    <h1>Installation</h1>

    <form method="post" action="">
        <p><label for="login">Login: </label><input type="text" name="login" id="login"/></p>
        <p><label for="password">Password: </label><input type="password" name="password" id="password"/></p>

        <p><input type="submit" value="Install !"/></p>
    </form>
</body>
</html>
';

function install() {
    global $install_template;

    if(!empty($_POST['login']) && !empty($_POST['password'])) {
        $bdd = new PDO('sqlite:'.DATA_DIR.'db.sqlite');

        $salt = uniqid(mt_rand(), true);
        $password = sha1($salt.$_POST['password']);

        $bdd->query('CREATE TABLE IF NOT EXISTS users(id integer, login text, password text, salt text, admin int)');
        $query = $bdd->prepare('INSERT INTO users(id, login, password, salt, admin) VALUES("", :login, :password, :salt, 1)');
        $query->bindValue(':login', $_POST['login']);
        $query->bindValue(':password', $password);
        $query->bindValue(':salt', $salt);
        $query->execute();

        header('location: index.php');
        exit();
    }
    else {
        echo $install_template;
    }
}
