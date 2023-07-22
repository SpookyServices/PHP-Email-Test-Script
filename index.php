<?php
// SMTP server configuration
$smtpHost = 'smtp.example.com';
$smtpPort = 587;
$smtpUsername = 'your_username';
$smtpPassword = 'your_password';

// Sender and recipient details
$senderEmail = 'sender@example.com';
$senderName = 'Sender Name';
$recipientEmail = 'recipient@example.com';
$recipientName = 'Recipient Name';

// Email subject and message
$subject = 'Test Email';
$message = 'This is a test email sent from PHP using SMTP.';

// Headers
$headers = array(
  'From: ' . $senderName . ' <' . $senderEmail . '>',
  'To: ' . $recipientName . ' <' . $recipientEmail . '>',
  'Subject: ' . $subject,
  'MIME-Version: 1.0',
  'Content-Type: text/html; charset=UTF-8'
);

// SMTP connection
$smtp = fsockopen($smtpHost, $smtpPort, $errno, $errstr, 30);
if (!$smtp) {
  echo "SMTP Error: $errstr ($errno)";
} else {
  // Connection successful
  $response = fgets($smtp, 515);
  if (substr($response, 0, 3) != '220') {
    echo "SMTP Error: Unexpected response '$response'";
  } else {
    // Send EHLO command
    fputs($smtp, "EHLO $smtpHost\r\n");
    $response = fgets($smtp, 515);
    if (substr($response, 0, 3) != '250') {
      echo "SMTP Error: Unexpected response '$response'";
    } else {
      // Check if AUTH is supported
      $authSupported = false;
      while (substr($response, 3, 1) == '-') {
        $response = fgets($smtp, 515);
        if (strpos($response, 'AUTH') !== false) {
          $authSupported = true;
        }
      }
      if (!$authSupported) {
        echo "SMTP Error: AUTH not supported by server";
      } else {
        // Send AUTH LOGIN command
        fputs($smtp, "AUTH LOGIN\r\n");
        $response = fgets($smtp, 515);
        if (substr($response, 0, 3) != '334') {
          echo "SMTP Error: Unexpected response '$response'";
        } else {
          // Send username
          fputs($smtp, base64_encode($smtpUsername) . "\r\n");
          $response = fgets($smtp, 515);
          if (substr($response, 0, 3) != '334') {
            echo "SMTP Error: Unexpected response '$response'";
          } else {
            // Send password
            fputs($smtp, base64_encode($smtpPassword) . "\r\n");
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) != '235') {
              echo "SMTP Error: Authentication failed";
            } else {
              // Authentication successful

              // Send MAIL FROM command
              fputs($smtp, "MAIL FROM: <$senderEmail>\r\n");
              $response = fgets($smtp, 515);
              if (substr($response, 0, 3) != '250') {
                echo "SMTP Error: Unexpected response '$response'";
              } else {
                // Send RCPT TO command
                fputs($smtp, "RCPT TO: <$recipientEmail>\r\n");
                $response = fgets($smtp, 515);
                if (substr($response, 0, 3) != '250') {
                  echo "SMTP Error: Unexpected response '$response'";
                } else {
                  // Send DATA command
                  fputs($smtp, "DATA\r\n");
                  $response = fgets($smtp, 515);
                  if (substr($response, 0, 3) != '354') {
                    echo "SMTP Error: Unexpected response '$response'";
                  } else {
                    // Send email headers and message
                    fputs($smtp, implode("\r\n", $headers) . "\r\n\r\n");
                    fputs($smtp, $message . "\r\n");

                    // Send end of message marker
                    fputs($smtp, ".\r\n");
                    $response = fgets($smtp, 515);
                    if (substr($response, 0, 3) != '250') {
                      echo "SMTP Error: Unexpected response '$response'";
                    } else {
                      // Email sent successfully
                      echo "Email sent successfully";
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  // Close SMTP connection
  fputs($smtp, "QUIT\r\n");
  fclose($smtp);
}
