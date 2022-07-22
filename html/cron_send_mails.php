<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

require_once('utils.php');

try {

    $options = getopt("s::");
    $secret = isset($options['s'])?$options['s']:false;

    if ($secret === CRON_SECRET)
    {

        $emails = getPendingEmails();

        foreach ($emails as $email) {

            //always create new mailer, to prevent leaking information between two emails
            $mailer = new PHPMailer(true);

            $mailer->setFrom(FROM_MAIL_GENERAL);

            $mailer->addAddress($email['to']);
            $mailer->Subject = $email['subject'];
            $mailer->CharSet = 'UTF-8';
            $mailer->Encoding = 'quoted-printable';            
            $mailer->msgHTML($email['message']);


            $attachments = @json_decode($email['attachments']);

            if ($attachments)
            {
                foreach ($attachments as $name => $attachment) {
                    if (is_object($attachment))
                    {
                        if ($attachment->disposition == 'inline' && isset($attachment->cid))
                        {
                            $mailer->addEmbeddedImage($attachment->path, $attachment->cid, $name);
                        }
                        else
                        {
                            $mailer->addAttachment($attachment->path, $name, PHPMailer::ENCODING_BASE64, '', $attachment->disposition);
                        }
                    }
                    else
                    {
                        $mailer->addAttachment($attachment, $name);
                    }
                }
            }

            if ($mailer->send())
            {
                setMailSent($email['id']);
            }
        }
    }

} catch (\Exception $e) {
    error_log($e->getMessage());
}