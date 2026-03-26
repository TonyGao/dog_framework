<?php
$content = '{"name":"test","testEmail":"foo@bar.com"}';
$data = json_decode($content, true);
var_dump($data);
