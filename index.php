<?php
session_start();
error_reporting(0);
include("includes/config.php");
//xss
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
// Vérifier si le nombre de tentatives de connexion dépasse 3
if(isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 3) {
    $_SESSION['errmsg'] = "Trop de tentatives de connexion. Veuillez réessayer plus tard.";
    header("location:index.php");
    exit;
}

if(isset($_POST['submit']))
{
    //éviter l'attaque CSRF
    if($_SESSION["token"] != $_POST["token"]) {
        // Rejet de la requête en raison d'une attaque CSRF potentielle
        die("Erreur CSRF détectée !");
    }

    $regno=htmlspecialchars($_POST['regno'], ENT_QUOTES, 'UTF-8');
    $password=md5($_POST['password']);
    // protection contre une injection sql
    $regno = addslashes($regno); 
    $query=mysqli_query($con,"SELECT * FROM students WHERE StudentRegno='$regno' and password='$password'");
    $num=mysqli_fetch_array($query);

    if($num>0)
    {   // protection contre Xss
        $_SESSION['login']=htmlspecialchars($_POST['regno'], ENT_QUOTES, 'UTF-8');
        $_SESSION['id']=$num['studentRegno'];
        $_SESSION['sname']=htmlentities($num['studentName'], ENT_QUOTES, 'UTF-8');
        $uip=$_SERVER['REMOTE_ADDR'];
        $status=1;
        $log=mysqli_query($con,"insert into userlog(studentRegno,userip,status) values('".$_SESSION['login']."','$uip','$status')");
        header("location:http:change-password.php");
        exit;
    }
    else {
        // Augmenter le nombre de tentatives de connexion
        if(isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts']++;
        }
        else {
            $_SESSION['login_attempts'] = 1;
        }
        $_SESSION['errmsg']= "Invalid Reg no or Password";
        header("location:index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Security-Policy"
     content="script-src 'self' assets/js/jquery-1.11.1.js 
     assets/js/bootstrap.js 'unsafe-inline';"
     content="style-src 'self'
      assets/css/style.css
      assets/css/font-awesome.css
      assets/css/style.css 'unsafe-inline'"
      ;
     >
     

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <title>Student Login</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
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
                             <li><a href="index.php">Home </a></li>
                             <li><a href="admin/">Admin Login </a></li>
                              <li><a href="index.php">Student Login</a></li>
        

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
                    <h4 class="page-head-line">Please Login To Enter </h4>

                </div>

            </div>
             <span style="color:red;" ><?php echo htmlentities($_SESSION['errmsg']); ?><?php echo htmlentities($_SESSION['errmsg']="");?></span>
            <form name="admin" method="post">
            <div class="row">
                <div class="col-md-6">
                     <label>Enter Reg no : </label>
                        <input type="text" name="regno" class="form-control"  />
                        <label>Enter Password :  </label>
                        <input type="password" name="password" class="form-control"  />
                        <hr />
                        <input type="hidden" name="token" value="<?=$_SESSION["token"]?>"/>
                        <button type="submit" name="submit" class="btn btn-info"><span class="glyphicon glyphicon-user"></span> &nbsp;Log Me In </button>&nbsp;
                </div>
                </form>
                <div class="col-md-6">
                    <div class="alert alert-info">
                
                         <strong> Latest News / Updates</strong>
                         <marquee direction='up'  scrollamount="2" onmouseover="this.stop();" onmouseout="this.start();">
                        <ul>
                            <?php
$sql=mysqli_query($con,"select * from news");
$cnt=1;
while($row=mysqli_fetch_array($sql))
{
?>
                            <li>
                              <a href="news-details.php?nid=<?php echo htmlentities($row['id']);?>"><?php echo htmlentities($row['newstitle']);?>-<?php echo htmlentities($row['postingDate']);?></a>
                            </li>
                           <?php } ?> 
                     
                        </ul>
                    </marquee>
                       
                    </div>
                                    </div>

            </div>
        </div>
    </div>
    <!-- CONTENT-WRAPPER SECTION END-->
    <?php include('includes/footer.php');?>
    <!-- FOOTER SECTION END-->
    <!-- JAVASCRIPT AT THE BOTTOM TO REDUCE THE LOADING TIME  -->
    <!-- CORE JQUERY SCRIPTS -->
    <script src="assets/js/jquery-1.11.1.js"></script>
    <!-- BOOTSTRAP SCRIPTS  -->
    <script src="assets/js/bootstrap.js"></script>
</body>
</html>
