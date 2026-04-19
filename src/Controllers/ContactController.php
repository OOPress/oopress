<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Setting;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ContactController
{
    private Engine $view;
    
    public function __construct()
    {
        $themeManager = new \OOPress\Core\Theme\ThemeManager();
        $this->view = new Engine($themeManager->getThemeViewPath());
    }
    
    public function show(Request $request): Response
    {
        $content = $this->view->render('contact', [
            'title' => __('Contact Us'),
            'success' => null,
            'errors' => null,
            'old' => []
        ]);
        
        return new Response($content);
    }
    
    public function submit(Request $request): Response
    {
        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $subject = trim($request->input('subject'));
        $message = trim($request->input('message'));
        
        $errors = [];
        
        // Validation
        if (empty($name)) {
            $errors['name'] = __('Name is required');
        } elseif (strlen($name) < 2) {
            $errors['name'] = __('Name must be at least 2 characters');
        }
        
        if (empty($email)) {
            $errors['email'] = __('Email is required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = __('Valid email is required');
        }
        
        if (empty($subject)) {
            $errors['subject'] = __('Subject is required');
        }
        
        if (empty($message)) {
            $errors['message'] = __('Message is required');
        } elseif (strlen($message) < 10) {
            $errors['message'] = __('Message must be at least 10 characters');
        }
        
        // Honeypot check (spam prevention)
        $honeypot = $request->input('website');
        if (!empty($honeypot)) {
            // Spam detected, silently return success
            $content = $this->view->render('contact', [
                'title' => __('Contact Us'),
                'success' => __('Thank you for your message. We will get back to you soon.'),
                'errors' => null,
                'old' => []
            ]);
            return new Response($content);
        }
        
        if (!empty($errors)) {
            $content = $this->view->render('contact', [
                'title' => __('Contact Us'),
                'success' => null,
                'errors' => $errors,
                'old' => compact('name', 'email', 'subject', 'message')
            ]);
            return new Response($content, 400);
        }
        
        // Send email
        $mailSent = $this->sendEmail($name, $email, $subject, $message);
        
        if ($mailSent) {
            $content = $this->view->render('contact', [
                'title' => __('Contact Us'),
                'success' => __('Thank you for your message. We will get back to you soon.'),
                'errors' => null,
                'old' => []
            ]);
            return new Response($content);
        }
        
        $errors['form'] = __('Failed to send message. Please try again later.');
        $content = $this->view->render('contact', [
            'title' => __('Contact Us'),
            'success' => null,
            'errors' => $errors,
            'old' => compact('name', 'email', 'subject', 'message')
        ]);
        
        return new Response($content, 500);
    }
    
    private function sendEmail(string $name, string $email, string $subject, string $message): bool
    {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = Setting::get('smtp_host', $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io');
            $mail->SMTPAuth = true;
            $mail->Username = Setting::get('smtp_username', $_ENV['MAIL_USERNAME'] ?? '');
            $mail->Password = Setting::get('smtp_password', $_ENV['MAIL_PASSWORD'] ?? '');
            $mail->SMTPSecure = Setting::get('smtp_encryption', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
            $mail->Port = Setting::get('smtp_port', $_ENV['MAIL_PORT'] ?? 2525);
            
            // Recipients
            $mail->setFrom($email, $name);
            $mail->addAddress(Setting::get('contact_email', $_ENV['MAIL_FROM_ADDRESS'] ?? 'admin@oopress.com'), Setting::get('site_title', 'OOPress'));
            $mail->addReplyTo($email, $name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = '[Contact] ' . $subject;
            $mail->Body = $this->getEmailHtml($name, $email, $subject, $message);
            $mail->AltBody = $this->getEmailText($name, $email, $subject, $message);
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("Contact email failed: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    private function getEmailHtml(string $name, string $email, string $subject, string $message): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Contact Form Submission</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4299e1; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f7fafc; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; margin-bottom: 5px; }
                .value { background: white; padding: 10px; border-radius: 4px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #718096; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Contact Form Submission</h2>
                </div>
                <div class='content'>
                    <div class='field'>
                        <div class='label'>Name:</div>
                        <div class='value'>" . htmlspecialchars($name) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Email:</div>
                        <div class='value'>" . htmlspecialchars($email) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Subject:</div>
                        <div class='value'>" . htmlspecialchars($subject) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Message:</div>
                        <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                    </div>
                </div>
                <div class='footer'>
                    <p>Sent from " . Setting::get('site_title', 'OOPress') . " contact form</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getEmailText(string $name, string $email, string $subject, string $message): string
    {
        return "Contact Form Submission\n\n" .
               "Name: $name\n" .
               "Email: $email\n" .
               "Subject: $subject\n\n" .
               "Message:\n$message\n\n" .
               "Sent from " . Setting::get('site_title', 'OOPress') . " contact form";
    }
}