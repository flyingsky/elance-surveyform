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

  $file = fopen("survey.csv", "a");
  $result = fputcsv($file,explode('<br/>', $content));
  fclose($file);

  if ($result) {
    echo "ok";
  } else {
    echo "Fail to send email, please try to submit later!";
  }
?>