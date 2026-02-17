<?php
$link = mysqli_connect('localhost', 'root', '', 'myforum');

if (!$link) {
    die('DB connection error: ' . mysqli_connect_error());
}


function sanitize_input($text) {
    $text = preg_replace('/javascript:/i', 'blocked:', $text);
    $text = preg_replace('/on\w+=/i', 'blocked=', $text);
    $text = preg_replace('/data:\w+/i', 'blocked:', $text);
    $text = preg_replace('/vbscript:/i', 'blocked:', $text);
    $text = preg_replace('/expression\(/i', 'blocked(', $text);
    
    return $text;
}

function parse_smilies($text) {
    $text = sanitize_input($text);
    
    $smilies = array(
        ':)' => '<img src="https://www.allsmileys.com/files/kolobok/light/76.gif" alt=":)">',
        ':(' => '<img src="https://www.allsmileys.com/files/kolobok/light/60.gif" alt=":(">',
        ':D' => '<img src="https://www.allsmileys.com/files/kolobok/light/10.gif" alt=":D" >',
        ';)' => '<img src="https://www.allsmileys.com/files/kolobok/light/73.gif" alt=";)" >',
        ':P' => '<img src="https://www.allsmileys.com/files/kolobok/light/68.gif" alt=":P">',
        '8)' => '<img src="https://www.allsmileys.com/files/kolobok/light/23.gif" alt="8)">',
        ':nerd:' => '<img src="https://www.allsmileys.com/files/kolobok/light/63.gif" alt="*nerd*">',
        'XD' => '<img src="https://www.allsmileys.com/files/kolobok/light/52.gif" alt="XD">',
        ':dance:' => '<img src="https://www.allsmileys.com/files/kolobok/light/26.gif" alt="*dancing*">',
        ':beer:' => '<img src="https://www.allsmileys.com/files/kolobok/light/37.gif" alt=*cheers!* >',
    );
    return str_replace(array_keys($smilies), array_values($smilies), $text);
}

function parse_bbcodes($text) {
    $text = sanitize_input($text);
    
    // BOLD
    $text = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong>$1</strong>', $text);
    // ITALIC
    $text = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em>$1</em>', $text);
    // UNDERLINED
    $text = preg_replace('/\[u\](.*?)\[\/u\]/is', '<u>$1</u>', $text);
    // QUOTE
    $text = preg_replace('/\[quote\](.*?)\[\/quote\]/is', '<div style="background:#f0f0f0; border-left:3px solid #808080; padding:5px; margin:5px;"><i>quote:</i><br>$1</div>', $text);
    
    $text = preg_replace_callback('/\[url\](.*?)\[\/url\]/is', function($matches) {
        $url = trim($matches[1]);
        if (preg_match('/^https?:\/\//i', $url)) {
            return '<a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($url) . '</a>';
        } else {
            return htmlspecialchars($matches[0]);
        }
    }, $text);
    
    $text = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/is', function($matches) {
        $url = trim($matches[1]);
        $title = $matches[2];
        if (preg_match('/^https?:\/\//i', $url)) {
            return '<a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($title) . '</a>';
        } else {
            return htmlspecialchars($matches[0]);
        }
    }, $text);
    $text = preg_replace_callback('/\[img\](.*?)\[\/img\]/is', function($matches) {
        $url = trim($matches[1]);
        if (preg_match('/^https?:\/\/.*\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
            return '<img src="' . htmlspecialchars($url) . '" style="max-width:400px; max-height:300px;">';
        } else {
            return htmlspecialchars($matches[0]);
        }
    }, $text);
    
    return $text;
}

mysqli_set_charset($link, 'utf8mb4');

$message_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '!Put ur name here!';
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $username = mysqli_real_escape_string($link, $username);
        $message = mysqli_real_escape_string($link, $message);

        $sql = "INSERT INTO posts (username, message) VALUES ('$username', '$message')";
        if (mysqli_query($link, $sql)) {
            $message_sent = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "DB error: " . mysqli_error($link);
        }
    } else {
        echo "<p style='color:red;'>message can't be empty!</p>";
    }
}

$sql = "SELECT id, username, message, created_at FROM posts ORDER BY created_at DESC";
$result = mysqli_query($link, $sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>my basement</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            margin: 20px;
        }
        .forum-container {
            width: 900px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #ccc;
            padding: 20px;
        }
        .header {
            background-color: #d0d0d0;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #aaa;
            text-align: center;
            font-weight: bold;
        }
        table.forum-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.forum-table th {
            background-color: #e0e0e0;
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }
        table.forum-table td {
            border: 1px solid #999;
            padding: 8px;
            vertical-align: top;
        }
        .post-username {
            font-weight: bold;
            color: #333;
            width: 150px;
        }
        .post-date {
            font-size: 10pt;
            color: #666;
        }
        .post-message {
            /* dont even ask */
        }
        .form-area {
            background-color: #e8e8e8;
            border: 1px solid #aaa;
            padding: 15px;
        }
        .form-area input[type=text], .form-area textarea {
            width: 300px;
            padding: 5px;
            border: 1px solid #aaa;
            font-family: Arial, Helvetica, sans-serif;
        }
        .form-area textarea {
            width: 500px;
            height: 100px;
        }
        .form-area input[type=submit] {
            background-color: #c0c0c0;
            border: 2px solid #696969;
            padding: 5px 15px;
            font-weight: bold;
            cursor: pointer;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11pt;
            color: #777;
        }
    </style>
</head>
<body>
<div class="forum-container">
    <div class="header">
        WELCOME TO the  HELL, BITCH!
    </div>

    <h3>MESSages:</h3>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="forum-table">
            <tr>
                <th>author</th>
                <th>message</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="post-username">
                        <?php echo htmlspecialchars($row['username']); ?>
                        <div class="post-date"><?php echo $row['created_at']; ?></div>
                    </td>
                    <td class="post-message">
                        <?php
                        $message = htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');
                        $message = parse_bbcodes($message);
                        $message = parse_smilies($message);                   
                        echo nl2br($message);
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>theres nothig here...Be the first to text something!</p>
    <?php endif; ?>

    <div class="form-area">
        <h4>write to a thread</h4>
        <form method="POST" action="">
            <label for="username">Name (or anonimous pipis):</label><br>
            <input type="text" name="username" id="username" value="Pipis"><br><br>

            <label for="message">MESSage:</label><br>
            <textarea name="message" id="message" required></textarea><br><br>

            <input type="submit" value="push">
        </form>
    </div>

    <div class="footer">
        <?php
        $hits_file = 'hits.txt';
        if (!file_exists($hits_file)) {
            file_put_contents($hits_file, '0');
        }
        $hits = (int)file_get_contents($hits_file) + 1;
        file_put_contents($hits_file, $hits);
        ?>
        &copy; visited >>  <?php echo $hits; ?> | 
        &copy; 2010 MySuperForum / works on PHP and MySQL(MariaDB)
    </div>
</div>
<?php
mysqli_close($link);
?>
</body>
</html>
