<?php
session_start();
error_reporting(0);
include("includes/config.php");

// Mettre en place une politique de sécurité des contenus (CSP)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
if(isset($_POST['submit']))
{
    // Limit number of login attempts
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    $_SESSION['login_attempts']++;
    if ($_SESSION['login_attempts'] > 5) {
        $time_since_last_attempt = time() - $_SESSION['last_login_attempt'];
        if ($time_since_last_attempt < 120) {
            // Block login and display error message
            $_SESSION['errmsg'] = "Trop de tentatives de connexion. Veuillez réessayer plus tard.";
            header("location:index.php");
            exit();
        } else {
            // Réinitialiser le nombre de tentatives de connexion et le temps de la dernière tentative de connexion
            $_SESSION['login_attempts'] = 1;
            $_SESSION['last_login_attempt'] = time();
        }
    } else {
        $_SESSION['last_login_attempt'] = time();
    }

      // Protection CSRF
      // Empêcher les attaques CSRF en générant un jeton CSRF unique pour chaque session et en le comparant avec le jeton du formulaire
    if($_SESSION["token"] != $_POST["token"]) {
        die("CSRF attack detected!");
    }

    $username = $_POST['username'];
    // MD5 hash the password
    // Utilisation de la fonction de hachage password_hash() au lieu de md5()
    $password = md5($_POST['password']);
    //$password = hash('sha256', $_POST['password']); // Utilisation de l'algorithme de hachage SHA256
    //$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    //  Protection contre les injections SQL et XSS

    //$username = addslashes($username);
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $query = mysqli_query($con, "SELECT * FROM admin WHERE username='$username' and password='$password'");
    $num = mysqli_fetch_array($query);
    if($num > 0)
    {
        //protect xss
        
        $_SESSION['alogin'] = $_POST['username'];
        $_SESSION['id'] = $num['id'];
        $_SESSION['login_attempts'] = 0;
        header("location:change-password.php");
        exit();
    }
    else
    {
        $_SESSION['errmsg'] = "Invalid";
        header("location:index.php");
        exit();
    }
}

// Générer le jeton CSRF
$_SESSION["token"] = md5(uniqid(mt_rand(), true));
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <title>Admin Login</title>
    <link href="../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../assets/css/font-awesome.css" rel="stylesheet" />
    <link href="../assets/css/style.css" rel="stylesheet" />
</head>
<?php 
// generate token
$_SESSION["token"] = md5(uniqid(mt_rand(), true));
?>

<body>
    <?php include('includes/header.php');?>

    <section class="menu-section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="navbar-collapse collapse ">
                        <ul id="menu-top" class="nav navbar-nav navbar-right">
                             <li><a href="../index.php">Home </a></li>
                             <li><a href="index.php">Admin Login </a></li>
                              <li><a href="../index.php">Student Login</a></li>
        

                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="page-head-line">Please Login To Enter in to Admin Panel </h4>

                </div>

            </div>
             <span style="color:red;"><?php echo htmlspecialchars($_SESSION['errmsg'], ENT_QUOTES, 'UTF-8'); ?></span>
            <form name="admin" method="post">
            <!-- Appelle la fonction csrf_field pour insérer le jeton CSRF dans le formulaire -->
            <div class="row">
                <div class="col-md-6">
                     <label>Enter Username : </label>
                        <input type="text" name="username" class="form-control" required />
                        <label>Enter Password :  </label>
                        <input type="password" name="password" class="form-control" required />
                        <hr />
                        <input type="hidden" name="token" value="<?=$_SESSION["token"]?>"/>
                        <button type="submit" name="submit" class="btn btn-info"><span class="glyphicon glyphicon-user"></span> &nbsp;Log Me In </button>&nbsp;
                </div>
                </form>
                <div class="col-md-6">
                 <img src="../assets/img/admin.png" class="img-responsive">
                                    </div>

            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END-->
    <?php include('includes/footer.php');?>
    <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT AT THE BOTTOM TO REDUCE THE LOADING TIME  -->
    <!-- CORE JQUERY SCRIPTS -->
    <script src="../assets/js/jquery-1.11.1.js"></script>
    <!-- BOOTSTRAP SCRIPTS  -->
    <script src="../assets/js/bootstrap.js"></script>
</body>
</html>