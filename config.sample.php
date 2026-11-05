<?php
/*
 * Copy this file to "config.local.php" and fill in your ONLINE MySQL details.
 * config.local.php is ignored by Git, so your password never goes to GitHub.
 */
return [
    'db_host' => 'sqlXXX.infinityfree.com',   // online MySQL host
    'db_port' => 3306,
    'db_name' => 'student_registration_system',
    'db_user' => 'your_db_user',
    'db_pass' => 'your_db_password',
    // Only for providers that require SSL (Aiven, TiDB Cloud...): path to CA file, else leave ''
    'db_ssl_ca' => '',
];
