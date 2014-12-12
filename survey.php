<?php
  $email_to = "llmfei@gmail.com";
  $email_subject = "Survey";

  function died($error) {
    echo "We are very sorry, but we found below error(s) with the survey you submitted. \n\n";
    echo $error."\n";
    die();
  }

  $name = $_POST['name'];
  $email = $_POST['email'];
  $content = $_POST['content'];

  if(!$name || !$email || !$content) {
    died('Name and Email are required');
  }

  function clean_string($string) {
    $bad = array("content-type","bcc:","to:","cc:","href");
    return str_replace($bad,"",$string);
  }

  $email_message = clean_string($content)."\n";


  $headers = 'From: '.$email."\r\n";
  $headers .= 'Reply-To: '.$email."\r\n";
  $headers .= 'X-Mailer: PHP/' . phpversion()."\r\n";
  $headers .= "Content-Type: text/html; charset=utf-8\r\n";

  $result = @mail($email_to, $email_subject, $email_message, $headers);
  if ($result) {
    echo "ok";
  } else {
    echo "Fail to send email, please try to submit later!";
  }
?>