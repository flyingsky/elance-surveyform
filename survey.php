<?php
  if(isset($_POST['email'])) {

    $email_to = "llmfei@gmail.com";
    $email_subject = "survey";

    function died($error) {
        echo "We are very sorry, but there were error(s) found with the form you submitted. ";
        echo "These errors appear below.<br /><br />";
        echo $error."<br /><br />";
        echo "Please go back and fix these errors.<br /><br />";
        die();
    }

    if(!isset($_POST['name']) ||
        !isset($_POST['email']) ||
        !isset($_POST['content'])) {
        died('We are sorry, but there appears to be a problem with the form you submitted.');
    }

    $name = $_POST['name'];
    $email = $_POST['email'];

    $error_message = "";
    if(strlen($error_message) > 0) {
      died($error_message);
    }

    function clean_string($string) {
      $bad = array("content-type","bcc:","to:","cc:","href");
      return str_replace($bad,"",$string);
    }

    $content = $_POST['content'];
    $email_message = "Form details below.\n\n";
    $email_message .= clean_string($content)."\n";


    $headers = 'From: '.$email."\r\n".
    'Reply-To: '.$email."\r\n" .
    'X-Mailer: PHP/' . phpversion();
    @mail($email_to, $email_subject, $email_message, $headers);
?>

ok

<?php
}
?>