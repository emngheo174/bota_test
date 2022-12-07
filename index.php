<?php
require_once 'connect_db.php';

if (isset($_SESSION['user_token'])) {
  header("Location: dashboard.php");
} else {
//   echo "<a href='" . $client->createAuthUrl() . "'>";
//   echo '<img src="/test/Uploads/gg.png"/>';
//   echo "</a>";
header("Location: login.php");
}
