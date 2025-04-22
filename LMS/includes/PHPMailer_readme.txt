PHPMailer Library for BonnieComputerHub LMS

To use PHPMailer for reliable email delivery:

1. Download the latest PHPMailer from https://github.com/PHPMailer/PHPMailer/releases
2. Extract the contents so that the 'src' folder is located at:
   c:/xampp2/htdocs/BonnieComputerHub/LMS/includes/PHPMailer/src/
3. Your directory should contain:
   - PHPMailer.php
   - SMTP.php
   - Exception.php

You can now include PHPMailer in your PHP files like this:
require_once __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../includes/PHPMailer/src/Exception.php';

For Composer users: run 'composer require phpmailer/phpmailer' and use Composer's autoloader instead.

See https://github.com/PHPMailer/PHPMailer for full documentation.
