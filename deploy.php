<?php
$dir = '/home2/basta920/intra.bastardsbrewery.com.br/';
exec("cd $dir && git pull 2>&1", $output);
echo "<pre>" . implode("\n", $output) . "</pre>";
?>
