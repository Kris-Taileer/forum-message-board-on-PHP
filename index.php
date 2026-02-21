<?php
$link = mysqli_connect('localhost', 'root', '', 'myforum');
if (!$link) {
    die('DB connection error: ' . mysqli_connect_error());
}

mysqli_set_charset($link, 'utf8mb4');

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
        ':)' => '<img src="https://www.allsmileys.com/files/kolobok/light/76.gif">',
        ':(' => '<img src="https://www.allsmileys.com/files/kolobok/light/60.gif">',
        ':D' => '<img src="https://www.allsmileys.com/files/kolobok/light/10.gif">',
        ';)' => '<img src="https://www.allsmileys.com/files/kolobok/light/73.gif">',
        ':P' => '<img src="https://www.allsmileys.com/files/kolobok/light/68.gif">',
        'XD' => '<img src="https://www.allsmileys.com/files/kolobok/light/52.gif">',
        ':beer:' => '<img src="https://www.allsmileys.com/files/kolobok/light/37.gif">'
    );
    return str_replace(array_keys($smilies), array_values($smilies), $text);
}

function parse_bbcodes($text) {
    $text = sanitize_input($text);
    $text = preg_replace('/\[b\](.*?)\[\/b\]/is', '<b>$1</b>', $text);
    $text = preg_replace('/\[i\](.*?)\[\/i\]/is', '<i>$1</i>', $text);
    $text = preg_replace('/\[u\](.*?)\[\/u\]/is', '<u>$1</u>', $text);
    $text = preg_replace('/\[quote\](.*?)\[\/quote\]/is', '<div class="quote"><b>Quote:</b><br>$1</div>', $text);

    $text = preg_replace_callback('/\[url=(.*?)\](.*?)\[\/url\]/is', function($matches) {
        $url = trim($matches[1]);
        $title = $matches[2];
        if (preg_match('/^https?:\/\//i', $url)) {
            return '<a href="' . htmlspecialchars($url) . '" target="_blank">' . htmlspecialchars($title) . '</a>';
        }
        return htmlspecialchars($matches[0]);
    }, $text);

    $text = preg_replace_callback('/\[img\](.*?)\[\/img\]/is', function($matches) {
        $url = trim($matches[1]);
        if (preg_match('/^https?:\/\/.*\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
            return '<img src="' . htmlspecialchars($url) . '" class="post-img">';
        }
        return htmlspecialchars($matches[0]);
    }, $text);

    return $text;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : 'Anonymous';
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $username = mysqli_real_escape_string($link, $username);
        $message = mysqli_real_escape_string($link, $message);
        mysqli_query($link, "INSERT INTO posts (username, message) VALUES ('$username', '$message')");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$result = mysqli_query($link, "SELECT id, username, message, created_at FROM posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>my basement</title>
<style>
body {
    background: #d4d0c8;
    font-family: Tahoma, Verdana, Arial;
    font-size: 12px;
    margin: 20px;
}

.container {
    width: 850px;
    margin: auto;
    background: #ffffff;
    border: 1px solid #000000;
}

.header {
    background: #000080;
    color: #ffffff;
    padding: 8px;
    font-weight: bold;
    font-size: 14px;
}

.section-title {
    background: #c0c0c0;
    padding: 6px;
    border-top: 1px solid #000000;
    border-bottom: 1px solid #000000;
    font-weight: bold;
}

table.forum {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

table.forum th {
    background: #e0e0e0;
    border: 1px solid #999999;
    padding: 6px;
    text-align: left;
}

table.forum td {
    border: 1px solid #999999;
    padding: 8px;
    vertical-align: top;
}

.author {
    width: 180px;
    background: #f4f4f4;
    font-weight: bold;
}

.post {
    white-space: pre-wrap;
    word-break: break-word;
}

.post-img {
    max-width: 400px;
    max-height: 300px;
}

.quote {
    background: #eeeeee;
    border: 1px dashed #999999;
    padding: 6px;
    margin: 6px 0;
}

.form-box {
    border-top: 1px solid #000000;
    padding: 10px;
    background: #efefef;
}

.form-table {
    width: 100%;
}

.form-table td {
    padding: 5px;
}

input[type=text], textarea {
    width: 100%;
    border: 1px solid #666666;
    padding: 4px;
    font-family: Tahoma;
    font-size: 12px;
}

textarea {
    height: 120px;
}

input[type=submit] {
    background: #d4d0c8;
    border: 2px outset #ffffff;
    padding: 4px 12px;
    font-weight: bold;
    cursor: pointer;
}

.bb-panel {
    background: #dcdcdc;
    border: 1px solid #999999;
    padding: 5px;
    margin-bottom: 8px;
    font-size: 11px;
}

.bb-panel a {
    text-decoration: none;
    color: #000000;
    margin: 0 2px;
}

.bb-panel a[title] {
    cursor: help;
    border-bottom: 1px dashed #666;
}


.image-hint {
    background: #fff0e0;
    border: 1px solid #c60;
    padding: 8px;
    margin-bottom: 10px;
    font-size: 11px;
    line-height: 2;
    color: #630;
}

.image-hint code {
    background: #f0f0f0;
    padding: 2px 4px;
    border: 1px solid #999;
    font-family: 'Courier New', monospace;
}

.image-hint .title {
    font-weight: bold;
    color: #000;
}

#enableNotifications {
    background: #d4d0c8;
    border: 2px outset #ffffff;
    padding: 4px 12px;
    font-weight: bold;
    cursor: pointer;
    margin-bottom: 10px;
}

.footer {
    background: #c0c0c0;
    padding: 6px;
    font-size: 11px;
    text-align: center;
}
</style>
</head>

<button id="enableNotifications">>> Enable notifications</button>

<body>

<div class="container">

<div class="header">
    <?php
    $quotes = [
        "WELCOME TO the HELL, BITCH!",
        "PIPIS LOVES YOU",
        "SEND NUDES (OF YOUR CODE)",
        "3.141592653589...",
        "KRIS+MARDUK FOREVER",
        "BEER HERE -> :beer:",
        "XD XD XD"
    ];
    echo $quotes[array_rand($quotes)];
    ?>
</div>

<div class="section-title">MESSAGES</div>

<table class="forum">
<tr>
<th style="width:180px;">Author</th>
<th>Message</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
<td class="author">
<?php echo htmlspecialchars($row['username']); ?><br>
<span style="font-weight:normal;font-size:11px;">
<?php echo date("d.m.Y H:i", strtotime($row['created_at'])); ?>
</span>
</td>

<td class="post">
<?php
$message = htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');
$message = parse_bbcodes($message);
$message = parse_smilies($message);
echo $message;
?>
</td>
</tr>
<?php endwhile; ?>
</table>

<div class="form-box">

<div class="bb-panel">
BBCode:
<a href="#" onclick="insertTag('[b]','[/b]');return false;">[b]</a> |
<a href="#" onclick="insertTag('[i]','[/i]');return false;">[i]</a> |
<a href="#" onclick="insertTag('[u]','[/u]');return false;">[u]</a> |
<a href="#" onclick="insertTag('[quote]','[/quote]');return false;">[quote]</a> |
<a href="#" onclick="insertUrl();return false;">[url]</a> |
<a href="#" onclick="insertImg();return false;" title="Only https:// images ending with .jpg, .png, .gif, .webp">[img]</a>
</div>

<div class="image-hint">
    <span class="title">~Image rules:~</span><br>
    • Use <strong>https://</strong> only<br>
    • Allowed: <code>.jpg</code> <code>.jpeg</code> <code>.png</code> <code>.gif</code> <code>.webp</code><br>
    • Example: <code>[img]https://site.com/image.jpg[/img]</code>
</div>

<form method="POST">
<table class="form-table">
<tr>
<td width="180"><b>Name:</b></td>
<td><input type="text" name="username" value="Pipis"></td>
</tr>
<tr>
<td><b>Message:</b></td>
<td><textarea name="message" required></textarea></td>
</tr>
<tr>
<td></td>
<td><input type="submit" value="Push"></td>
</tr>
</table>
</form>

</div>

<div class="footer">
        <?php
        $total_posts = mysqli_fetch_assoc(mysqli_query($link, "SELECT COUNT(*) as count FROM posts"))['count'];
        $last_post = mysqli_fetch_assoc(mysqli_query($link, "SELECT username, created_at FROM posts ORDER BY created_at DESC LIMIT 1"));
        ?>
        <div style="margin-top:10px; font-size:10pt; border-top:1px dotted #999; padding-top:5px;">
            <strong>>>STATS:<<</strong> 
            Total posts: <?php echo $total_posts; ?> | 
            Last post: <?php echo $last_post ? $last_post['username'] . ' (' . date("d.m.Y H:i", strtotime($last_post['created_at'])) . ')' : 'none'; ?>
        </div>
        <?php
        $hits_file = 'hits.txt';
        if (!file_exists($hits_file)) {
            file_put_contents($hits_file, '0');
        }
        $hits = (int)file_get_contents($hits_file) + 1;
        file_put_contents($hits_file, $hits);
        ?>
        &copy; visited >>  <?php echo $hits; ?> | 
        &copy; 2010 MySuperForum
</div>

</div>

<script>
function insertTag(openTag, closeTag) {
    var textarea = document.querySelector('textarea[name="message"]');
    var start = textarea.selectionStart;
    var end = textarea.selectionEnd;
    textarea.value = textarea.value.substring(0, start) + openTag +
                     textarea.value.substring(start, end) +
                     closeTag +
                     textarea.value.substring(end);
}

function insertUrl() {
    var url = prompt('Enter URL (with https://):', 'https://');
    if (url) insertTag('[url=' + url + ']', '[/url]');
}

function insertImg() {
    var url = prompt('Enter image URL (must end with .jpg, .png, .gif, .webp):', 'https://');
    if (url) insertTag('[img]' + url, '[/img]');
}
</script>

<script src="notifications.js"></script>

</body>
</html>

<?php mysqli_close($link); ?>
