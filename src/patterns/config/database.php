<?php
$content = <<<EOD
<?php
return [
    'connectionString' => 'mysql:host=$dbHost;dbname=$dbName',
    'emulatePrepare' => true,
    'username' => $dbUser,
    'password' => $dbPassword,
    'charset' => 'utf8',
],
EOD;

return $content;