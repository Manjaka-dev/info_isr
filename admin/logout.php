<?php
require_once __DIR__ . '/auth.php';

adminLogout();
header('Location: /optim/info_isr/admin/login.php?logged_out=1');
exit;

