<?php

namespace admin\enquiries\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use admin\enquiries\Models\Enquiry;
use Illuminate\Support\Facades\DB;

class EnquiryReplyByAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $enquiry;

    /**
     * Create a new message instance.
     */
    public function __construct(Enquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $emailTemplate = null;
        // Check if email templates are stored in DB
        if (Schema::hasTable('emails')) {
            $emailTemplate = DB::table('emails')->whereSlug('enquiry_email')->first(['subject', 'description']);

            // If not found, create default template
            if (!$emailTemplate && Schema::hasTable('emails')) {
                $emailTemplate = DB::table('emails')->insertGetId([
                    'title' => 'Enquiry Email',
                    'slug' => 'enquiry_email',
                    'subject' => 'Enquiry Reply from %APP_NAME%',
                    'description' => '<p>Dear %USER_NAME%,</p>
                    <p>Thank you for reaching out to us with your enquiry.</p>
                    <p><strong>Your Original Message:</strong></p>
                    <blockquote style="background: #f9f9f9; padding: 10px; border-left: 3px solid #ccc;">%USER_MESSAGE%</blockquote>
                    <p><strong>Admin\'s Reply:</strong></p>
                    <blockquote style="background: #f9f9f9; padding: 10px; border-left: 3px solid #ccc;">%ADMIN_REPLY%</blockquote>
                    <p>If you have any further questions or concerns, feel free to reply to this email.</p>
                    <p>Best regards,</p>
                    <p>%ADMIN_NAME%<br>%APP_NAME%</p>',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Fallback subject and content
        $subject = 'Enquiry Reply from Admin';
        $content = '<p>Hi ' . e($this->enquiry->name) . ',</p><p>Thanks for your enquiry. Here is our response:</p><blockquote>' . e($this->enquiry->admin_reply) . '</blockquote><p>Regards,<br>' . e($this->enquiry->repliedBy->name ?? 'Admin') . '</p>';

        // If template exists, process placeholders
        if ($emailTemplate) {
            $subject = str_replace('%USER_NAME%', $this->enquiry->name, $emailTemplate->subject);

            $content = str_replace(
                [
                    '%USER_NAME%',
                    '%USER_MESSAGE%',
                    '%ADMIN_REPLY%',
                    '%ADMIN_NAME%',
                    '%APP_NAME%',
                    '%APP_LOGO%',
                    '%EMAIL_FOOTER%',
                    '%SUPPORT_EMAIL%',
                ],
                [
                    $this->enquiry->name,
                    $this->enquiry->message,
                    $this->enquiry->admin_reply,
                    $this->enquiry->repliedBy->name ?? 'Admin',
                    env('APP_NAME'),
                    asset('images/logo.png'),
                    config('get.email_footer_text'),
                    config('get.support_email'),
                ],
                $emailTemplate->description
            );
        }

        return $this->subject($subject)
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->replyTo(env('MAIL_FROM_ADDRESS'))
            ->view('admin::admin.email.master ')
            ->with(['template' => $content]);
    }
}