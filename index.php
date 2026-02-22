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
