<?php

define('OAUTH2_CLIENT_ID', '516267795361562652');//CLIENT ID
define('OAUTH2_CLIENT_SECRET', 'fnvuTkj4rG-zIawwWdoZmxlrshaLsxQM');//CLIENT SECRET


define('AUTH_URL', 'https://discord.com/api/oauth2/authorize');
define('CALLBACK_URL', 'http://localhost/telegramphp');
define('SCOPE', 'identify email');
define('TOKEN_URL', 'https://discord.com/api/oauth2/token');
define('URL_BASE', 'https://discord.com/api/users/@me');

session_start();

if (get('action') == 'login') {
    
    $_SESSION['state'] = hash('sha256', microtime(TRUE).rand().$_SERVER['REMOTE_ADDR']);
    unset($_SESSION['access_token']);
    
    $params = array(
        'client_id' => OAUTH2_CLIENT_ID,
        'redirect_uri' => CALLBACK_URL,
        'response_type' => 'code',
        'scope' => SCOPE,
        'state' => $_SESSION['state']
    );
    
    
    header('Location: ' . AUTH_URL . '?' . http_build_query($params));
    die();
}

if (get('action') == 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    die();
}

if (get('code')) {
    if(!get('state') || $_SESSION['state'] != get('state')) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    }

    $token = apiRequest(TOKEN_URL, true, array (
        'client_id' => OAUTH2_CLIENT_ID,
        'client_secret' => OAUTH2_CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'code' => get('code'),
        'redirect_uri' => CALLBACK_URL,
        'scope' => SCOPE
    ));
    $_SESSION['access_token'] = $token->access_token;
    
    header('Location: ' . $_SERVER['PHP_SELF']);
}

if(session('access_token')) {
  $user = apiRequest(URL_BASE, false, '');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=utf-8>
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title>Discord oAuth2</title>
        <style>
            *{
                padding: 0;
                margin: 0;
                box-sizing: border-box;
                font-family: sans-serif;
            }
            nav{
                position: fixed;
                width: 100%;
                padding: 0.8rem 2rem;
                z-index: 10;
                background-color: rgba(0, 0, 0, 0.1);
            }
            nav .container{
                display: flex;
                align-items: center;
                justify-content: space-between;
                max-width: 1250px;
                margin: auto auto;
            }
            nav .container .logo h1{
                font-size: 1.5em;
                color: #fff;
            }
            nav .container .menu ul{
                display: flex;
                align-items: center;
            }

            nav .container .menu ul li{
                list-style-type: none;
                margin-left: 30px;
            }
            nav .container .menu ul li a{
                color: rgba(255, 255, 255, 0.8);
                font-size: 1em;
            }
            .banner-color{
                width: 100%;
                height: 25%;
                padding: 10rem;
                position: absolute;
            }
            .container-x{
                display: flex;
                flex-direction: column;
                text-align: center;
            }
            .profile{
                z-index: 9;
            }
            .picture{
                margin-top: 13rem;
            }
            .picture img{
                width: 170px;
                height: auto;
                border-radius: 50%;
                box-shadow: 7px 0px 40px rgba(0, 0, 0, 0.3);
            }
            .username h1{
                color: rgba(0, 0, 0, 0.8);
                font-size: 2em;
                margin-top: 1rem;
            }
            .username p{
                color: rgba(0, 0, 0, 0.5);
                margin-top: 0.5rem;
            }
            .username h3{
                margin-top: 0.5rem;
                color: rgba(0, 0, 0, 0.7);
            }
            .username a{
                text-decoration: none;
                font-weight: 700;
                border-radius: 7px;
                background-color: red;
                color: #fff;
                padding: 0.8rem 2rem;
                box-shadow: 1px 0px 20px rgba(0, 0, 0, 0.3);
                line-height: 250px;
                transition: 250ms all;
            }
            .username a:hover{
                opacity: darkred;
                color: #ddd;
                box-shadow: 15px 0px 40px rgba(0, 0, 0, 0.3);
            }
        </style>

    </head>
    <body>
        <nav>
            <div class="container">
                <div class="logo">
                    <h1>Profile</h1>
                </div>
                <div class="menu">
                    <ul>
                        <li><a>Home</a></li>
                        <li><a>About</a></li>
                        <li><a>Blog</a></li>
                        <li><a>Value</a></li>
                        <li><a><i class="fa fa-search"></i></a></li>
                        <li><a><i class="fa fa-users"></i></a></li>
                    </ul>
                </div>
            </div>
        </nav>  
        <div class="banner">
            <?php 
                echo '<div class="banner-color" style="background-color:'. $user->banner_color .';"></div>';
            ?>
            <div class="container-x">
                <div class="profile">
                    <div class="picture">
                        <img src="<?php echo 'https://cdn.discordapp.com/avatars/'. $user->id. '/'. $user->avatar .'.png" alt="'. $user->username.' />' ?>">
                    </div>
                    <div class="username">
                        <h1><?php echo $user->username. "#". $user->discriminator; ?></h1>
                        <p>(<?php echo $user->id ?>)</p>
                        <h3><?php echo $user->email ?></h3>
                        <a href="?action=logout">Log out</a>
                    </div>
                </div>
            </div>
        </div><!-- 
        <pre>
            <?php echo print_r($user); ?>
        </pre> -->
    </body>
</html>
<?php
} else {
  echo '<p><a href="?action=login">Log in</a></p>';
}

// Functions
function apiRequest($url, $post, $params) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    
    if (session('access_token')) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'authorization: Bearer ' . session('access_token'),
            'cache-control: no-cache',
            'Accept: application/json'
        ));
    }
    
    $data = curl_exec($ch);
    return json_decode($data);
}

function get($key, $default=NULL) {
  return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
}

function session($key, $default=NULL) {
  return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
}
?>