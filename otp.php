<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
function mailsender($email, $otp)
{
    $mailEmail = $_ENV['MAIL_EMAIL'];
    $mailPassword = $_ENV['MAIL_PASSWORD'];
    $mail = new PHPMailer(true);

    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $mailEmail;
    $mail->Password = $mailPassword;  // App Password
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
function sendNewIPNotification($email, $username, $ipAddress, $loginTime)
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

    // Encoding UTF-8
    $mail->CharSet = 'UTF-8';

    // Expéditeur
    $mail->setFrom('karimimoha0@gmail.com', 'SmartWallet Security');

    // Destinataire
    $mail->addAddress($email);

    // Sujet
    $mail->Subject = "⚠️ Nouvelle connexion détectée - SmartWallet";
    $mail->IsHTML(true);

    // Corps du message
    $mail->Body = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Connexion Détectée - SmartWallet</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 30px 20px 20px; border-bottom: 1px solid #eeeeee;">
                            <span style="font-size: 28px; font-weight: bold; color: #10b981; display: block;">SmartWallet</span>
                            <span style="font-size: 14px; color: #6b7280;">Personal Finance Manager</span>
                        </td>
                    </tr>
                    
                    <!-- Alert Icon -->
                    <tr>
                        <td align="center" style="padding: 30px 40px 0;">
                            <div style="width: 80px; height: 80px; background-color: #fef3c7; border-radius: 50%; display: inline-block; line-height: 80px;">
                                <span style="font-size: 48px;">⚠️</span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Main Content -->
                    <tr>
                        <td align="left" style="padding: 30px 40px;">
                            
                            <h1 style="font-size: 22px; color: #1f2937; margin-top: 0; margin-bottom: 20px; text-align: center;">
                                Nouvelle Connexion Détectée
                            </h1>
                            
                            <p style="font-size: 16px; line-height: 1.6; color: #374151; margin-bottom: 25px;">
                                Bonjour <strong>' . htmlspecialchars($username) . '</strong>,
                                <br><br>
                                Nous avons détecté une connexion à votre compte SmartWallet depuis une nouvelle adresse IP.
                            </p>
                            
                            <!-- Connection Details Box -->
                            <div style="background-color: #f9fafb; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0 0 10px; font-size: 14px; color: #6b7280;">
                                    <strong style="color: #1f2937;">📍 Adresse IP :</strong> ' . htmlspecialchars($ipAddress) . '
                                </p>
                                <p style="margin: 0 0 10px; font-size: 14px; color: #6b7280;">
                                    <strong style="color: #1f2937;">🌍 Localisation :</strong> unknow
                                </p>
                                <p style="margin: 0 0 10px; font-size: 14px; color: #6b7280;">
                                    <strong style="color: #1f2937;">💻 Appareil :</strong> unknow
                                </p>
                                <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                    <strong style="color: #1f2937;">🕐 Date et Heure :</strong> ' . htmlspecialchars($loginTime) . '
                                </p>
                            </div>

                            <!-- Was it you? -->
                            <div style="background-color: #dbeafe; padding: 20px; margin: 25px 0; border-radius: 6px; text-align: center;">
                                <p style="font-size: 16px; color: #1e40af; margin: 0 0 15px; font-weight: bold;">
                                    Était-ce vous ?
                                </p>
                                <p style="font-size: 14px; color: #1e3a8a; margin: 0 0 20px; line-height: 1.5;">
                                    Si vous avez effectué cette connexion, vous pouvez ignorer cet email.
                                </p>
                            </div>

                            <!-- Security Alert -->
                            <div style="background-color: #fee2e2; border-left: 4px solid #ef4444; padding: 20px; margin: 25px 0; border-radius: 4px;">
                                <p style="font-size: 15px; color: #991b1b; margin: 0 0 10px; font-weight: bold;">
                                    🔒 Ce n\'était pas vous ?
                                </p>
                                <p style="font-size: 14px; color: #7f1d1d; margin: 0 0 15px; line-height: 1.6;">
                                    Si vous ne reconnaissez pas cette activité, votre compte pourrait être compromis. Nous vous recommandons de :
                                </p>
                                <ul style="font-size: 14px; color: #7f1d1d; margin: 0; padding-left: 20px; line-height: 1.8;">
                                    <li>Changer immédiatement votre mot de passe</li>
                                    <li>Vérifier vos informations de compte</li>
                                    <li>Activer l\'authentification à deux facteurs</li>
                                </ul>
                            </div>

                            <!-- Action Buttons -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="http://smartwallet.local/change_password.php" target="_blank" style="display: inline-block; padding: 12px 30px; background-color: #ef4444; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; margin: 5px;">
                                            Changer Mon Mot de Passe
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 10px;">
                                        <a href="http://smartwallet.local/account_activity.php" target="_blank" style="display: inline-block; padding: 12px 30px; background-color: #10b981; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; margin: 5px;">
                                            Voir l\'Activité du Compte
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Support -->
                            <p style="font-size: 13px; color: #6b7280; text-align: center; margin-top: 30px; line-height: 1.6;">
                                Si vous avez des questions ou besoin d\'aide, contactez notre équipe de support à 
                                <a href="mailto:support@smartwallet.com" style="color: #10b981; text-decoration: none;">support@smartwallet.com</a>
                            </p>

                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 40px; border-top: 1px solid #eeeeee; background-color: #f9fafb;">
                            <p style="font-size: 12px; color: #9ca3af; margin: 0 0 5px;">
                                Cet email est envoyé automatiquement pour la sécurité de votre compte.
                            </p>
                            <p style="font-size: 12px; color: #9ca3af; margin: 0;">
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