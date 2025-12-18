<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
function mailsender($email, $otp)
{
    $mail = new PHPMailer(true);

    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'karimimoha0@gmail.com';
    $mail->Password = 'yppl pdnh fyeg xrar';  // App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Encoding UTF-8 (للعربية)
    $mail->CharSet = 'UTF-8';
    // Expéditeur
    $mail->setFrom('karimimoha0@gmail.com', 'Smart Wallet');

    // Destinataire (email user li login)
    $mail->addAddress($email);  // $email من form

    // Sujet
    $mail->Subject = "Code d'activation";
    $mail->IsHTML(true);
    // Corps du message
    $mail->Body = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Code d\'Activation SmartWallet</title>
    </head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);">
                    
                    <tr>
                        <td align="center" style="padding: 30px 20px 20px; border-bottom: 1px solid #eeeeee;">
                            <span style="font-size: 28px; font-weight: bold; color: #10b981; display: block;">SmartWallet</span>
                            <span style="font-size: 14px; color: #6b7280;">Personal Finance Manager</span>
                        </td>
                    </tr>
                    
                    <tr>
                        <td align="left" style="padding: 40px 40px 30px;">
                            
                            <h1 style="font-size: 22px; color: #1f2937; margin-top: 0; margin-bottom: 20px;">
                                Activation de votre compte
                            </h1>
                            
                            <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 25px;">
                                Bonjour,
                                <br><br>
                                Veuillez utiliser le code ci-dessous pour vérifier votre adresse e-mail et activer votre compte SmartWallet.
                            </p>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <span style="display: inline-block; padding: 15px 30px; background-color: #e0f2f1; color: #10b981; font-size: 32px; font-weight: bold; border-radius: 8px; letter-spacing: 5px; border: 1px dashed #2dd4bf;">
                                    ' . $otp . '
                                </span>
                            </div>

                            <p style="font-size: 14px; line-height: 1.6; color: #6b7280; text-align: center; margin-bottom: 30px;">
                                **Ce code expire dans 5 minutes.** Ne le partagez avec personne.
                            </p>
                            
                            <p style="text-align: center; margin-top: 30px;">
                                <a href="http://smartwallet.local/verify_otp.php?otp=' . $otp . '" target="_blank" style="display: inline-block; padding: 12px 25px; background-color: #10b981; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;">
                                    Activer Mon Compte
                                </a>
                            </p>
                            <p style="font-size: 12px; color: #9ca3af; text-align: center; margin-top: 15px;">
                                (Si le lien ne fonctionne pas, utilisez le code ci-dessus.)
                            </p>

                        </td>
                    </tr>
                    
                    <tr>
                        <td align="center" style="padding: 20px 40px; border-top: 1px solid #eeeeee;">
                            <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                                Vous recevez cet e-mail car vous avez récemment créé un compte SmartWallet.
                            </p>
                            <p style="font-size: 12px; color: #9ca3af; margin: 5px 0 0;">
                                &copy; ' . date("Y") . ' SmartWallet. Tous droits réservés.
                            </p>
                        </td>
                    </tr>

                </table>
                </td>
        </tr>
    </table>
</body>
</html>
';
    $mail->send();
}
?>