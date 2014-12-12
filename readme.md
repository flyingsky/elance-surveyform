1. Install Composer In Your Project

Run this in your command line:

curl -s http://getcomposer.org/installer | php
Or download composer.phar into your project root.

2. Install Dependencies

Execute this in your project root.

php composer.phar install

3. Change the email information in survey.php

  $name = 'YourNameHere';  // your name
  $email = 'TheEmailYouWantToGetSurveyResult';   // your target email

  // your sender email, should be gmail, if not, please change below $mail.HOST
  $from = 'TheEmailYouUseToSendEmail';           // you can make it the same to target email
  $pwd = 'ThePasswordOfYourEmail';               // your sender email password

4. Access survey.php and you can use it