<?php
echo "<h1>Server is UP</h1>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Laravel Environment: " . (getenv('APP_ENV') ?: 'Not Set') . "</p>";
?>
