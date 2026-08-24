<?php

namespace App\Services;

use App\Helpers\Logger;

/**
 * Mail service — sends emails through Edobase Mail API.
 */
class MailService
{
    private EdobaseClient $client;

    public function __construct()
    {
        $this->client = new EdobaseClient();
    }

    /**
     * Send a response notification email to the form owner.
     */
    public function sendResponseNotification(array $form, array $answers): void
    {
        $settings = json_decode($form['settings'] ?? '{}', true);
        $notifyEmail = $settings['notify_email'] ?? null;

        if (!$notifyEmail || !($settings['notify_on_submit'] ?? false)) {
            return;
        }

        $title = e($form['title'] ?? 'Untitled Form');
        $formUrl = url("/forms/{$form['id']}/responses");

        // Build answers HTML
        $answersHtml = '';
        foreach ($answers as $questionId => $answer) {
            $val = is_array($answer) ? implode(', ', $answer) : e((string)$answer);
            $answersHtml .= "<tr><td style='padding:8px 12px;border-bottom:1px solid #f0f0f0;color:#6b7280;font-size:13px;'>{$questionId}</td><td style='padding:8px 12px;border-bottom:1px solid #f0f0f0;font-size:13px;'>{$val}</td></tr>";
        }

        $html = <<<HTML
        <div style="font-family:'Inter',Arial,sans-serif;max-width:560px;margin:0 auto;padding:20px;">
            <div style="text-align:center;margin-bottom:24px;">
                <div style="display:inline-block;width:40px;height:40px;border-radius:10px;background:#6366f1;color:#fff;font-weight:800;font-size:18px;line-height:40px;text-align:center;">E</div>
            </div>
            <h2 style="color:#111;font-size:18px;margin:0 0 4px;">New Response Received</h2>
            <p style="color:#6b7280;font-size:14px;margin:0 0 20px;">Form: <strong>{$title}</strong></p>
            <table style="width:100%;border-collapse:collapse;margin:0 0 20px;">
                {$answersHtml}
            </table>
            <a href="{$formUrl}" style="display:inline-block;padding:10px 20px;background:#6366f1;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;font-size:13px;">View Response</a>
            <p style="color:#9ca3af;font-size:11px;margin-top:24px;">Sent by Edoble Forms</p>
        </div>
        HTML;

        try {
            $this->client->sendMail($notifyEmail, "New response: {$title}", $html);
            Logger::info('Response notification sent', ['to' => $notifyEmail, 'form' => $form['id'] ?? '']);
        } catch (\Exception $e) {
            Logger::error('Failed to send response notification', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send a confirmation email to the respondent.
     */
    public function sendRespondentConfirmation(string $email, array $form): void
    {
        $title = e($form['title'] ?? 'Untitled Form');
        $settings = json_decode($form['settings'] ?? '{}', true);
        $message = $settings['confirmation_message'] ?? 'Your response has been recorded.';

        $html = <<<HTML
        <div style="font-family:'Inter',Arial,sans-serif;max-width:560px;margin:0 auto;padding:20px;">
            <div style="text-align:center;margin-bottom:24px;">
                <div style="display:inline-block;width:40px;height:40px;border-radius:10px;background:#6366f1;color:#fff;font-weight:800;font-size:18px;line-height:40px;text-align:center;">E</div>
            </div>
            <h2 style="color:#111;font-size:18px;margin:0 0 8px;">Response Recorded</h2>
            <p style="color:#6b7280;font-size:14px;margin:0 0 8px;">Thank you for submitting <strong>{$title}</strong>.</p>
            <p style="color:#374151;font-size:14px;">{$message}</p>
            <p style="color:#9ca3af;font-size:11px;margin-top:24px;">Sent by Edoble Forms</p>
        </div>
        HTML;

        try {
            $this->client->sendMail($email, "Response confirmed: {$title}", $html);
        } catch (\Exception $e) {
            Logger::error('Failed to send respondent confirmation', ['error' => $e->getMessage()]);
        }
    }
}
