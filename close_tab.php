<?php
session_start();
if (isset($_POST['tab_closed']) && $_POST['tab_closed'] === 'true') {
    session_destroy();
}
?>
