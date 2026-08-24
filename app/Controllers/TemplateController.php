<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\FormService;
use App\Services\EdobaseClient;

class TemplateController
{
    public function index(Request $request, array $params): void
    {
        // Built-in templates (stored in code, seeded to Edobase if needed)
        $templates = self::getBuiltInTemplates();

        echo view('templates.index', [
            'pageTitle'  => 'Templates',
            'templates'  => $templates,
        ], 'layouts.app');
        exit;
    }

    public function useTemplate(Request $request, array $params): void
    {
        $templates = self::getBuiltInTemplates();
        $template = null;
        foreach ($templates as $t) {
            if ($t['id'] === $params['id']) {
                $template = $t;
                break;
            }
        }

        if (!$template) {
            flash('error', 'Template not found.');
            redirect('/templates');
        }

        $formService = new FormService();
        $result = $formService->createFromTemplate($template);

        if ($result['success'] ?? false) {
            $formId = $result['data']['id'] ?? $result['data']['_id'] ?? '';
            flash('success', 'Form created from template!');
            redirect("/forms/{$formId}/edit");
        }

        flash('error', 'Failed to create form from template.');
        redirect('/templates');
    }

    /**
     * Built-in form templates.
     */
    public static function getBuiltInTemplates(): array
    {
        return [
            [
                'id' => 'tpl_event_registration',
                'title' => 'Event Registration',
                'description' => 'Collect registrations for events, workshops, and seminars.',
                'category' => 'Events',
                'icon' => 'bi-calendar-event',
                'schema' => json_encode([
                    'questions' => [
                        ['id' => 'q1', 'type' => 'short_text', 'title' => 'Full Name', 'required' => true, 'placeholder' => 'Enter your full name'],
                        ['id' => 'q2', 'type' => 'email', 'title' => 'Email Address', 'required' => true, 'placeholder' => 'your@email.com'],
                        ['id' => 'q3', 'type' => 'phone', 'title' => 'Phone Number', 'required' => false, 'placeholder' => '+91 XXXXX XXXXX'],
                        ['id' => 'q4', 'type' => 'dropdown', 'title' => 'How did you hear about this event?', 'required' => false, 'options' => [
                            ['value' => 'Social Media', 'label' => 'Social Media'],
                            ['value' => 'Email', 'label' => 'Email'],
                            ['value' => 'Friend', 'label' => 'Friend/Colleague'],
                            ['value' => 'Website', 'label' => 'Website'],
                            ['value' => 'Other', 'label' => 'Other'],
                        ]],
                        ['id' => 'q5', 'type' => 'paragraph', 'title' => 'Any special requirements?', 'required' => false, 'placeholder' => 'Dietary needs, accessibility, etc.'],
                    ],
                    'sections' => [['id' => 'sec_1', 'title' => 'Registration Details', 'order' => 0]],
                ]),
                'settings' => json_encode(['collect_email' => true, 'show_progress' => false, 'allow_multiple' => false]),
                'theme' => json_encode(['preset' => 'modern', 'primary_color' => '#6366f1']),
            ],
            [
                'id' => 'tpl_feedback',
                'title' => 'Feedback Form',
                'description' => 'Gather feedback from customers, students, or attendees.',
                'category' => 'Feedback',
                'icon' => 'bi-chat-square-text',
                'schema' => json_encode([
                    'questions' => [
                        ['id' => 'q1', 'type' => 'short_text', 'title' => 'Your Name', 'required' => false],
                        ['id' => 'q2', 'type' => 'email', 'title' => 'Email', 'required' => false],
                        ['id' => 'q3', 'type' => 'rating', 'title' => 'Overall Satisfaction', 'required' => true, 'max' => 5],
                        ['id' => 'q4', 'type' => 'multiple_choice', 'title' => 'Would you recommend us?', 'required' => true, 'options' => [
                            ['value' => 'Yes', 'label' => 'Yes'],
                            ['value' => 'Maybe', 'label' => 'Maybe'],
                            ['value' => 'No', 'label' => 'No'],
                        ]],
                        ['id' => 'q5', 'type' => 'paragraph', 'title' => 'Additional Comments', 'required' => false, 'placeholder' => 'Tell us more...'],
                    ],
                    'sections' => [['id' => 'sec_1', 'title' => 'Your Feedback', 'order' => 0]],
                ]),
                'settings' => json_encode(['collect_email' => false, 'allow_multiple' => true]),
                'theme' => json_encode(['preset' => 'minimal', 'primary_color' => '#10b981']),
            ],
            [
                'id' => 'tpl_contact',
                'title' => 'Contact Form',
                'description' => 'Simple contact form for your website.',
                'category' => 'General',
                'icon' => 'bi-envelope',
                'schema' => json_encode([
                    'questions' => [
                        ['id' => 'q1', 'type' => 'short_text', 'title' => 'Name', 'required' => true],
                        ['id' => 'q2', 'type' => 'email', 'title' => 'Email', 'required' => true],
                        ['id' => 'q3', 'type' => 'short_text', 'title' => 'Subject', 'required' => true],
                        ['id' => 'q4', 'type' => 'paragraph', 'title' => 'Message', 'required' => true, 'placeholder' => 'Your message...'],
                    ],
                    'sections' => [['id' => 'sec_1', 'title' => 'Contact Us', 'order' => 0]],
                ]),
                'settings' => json_encode(['collect_email' => true, 'notify_on_submit' => true]),
                'theme' => json_encode(['preset' => 'professional', 'primary_color' => '#3b82f6']),
            ],
            [
                'id' => 'tpl_job_application',
                'title' => 'Job Application',
                'description' => 'Collect job applications with resume upload.',
                'category' => 'HR',
                'icon' => 'bi-briefcase',
                'schema' => json_encode([
                    'questions' => [
                        ['id' => 'q1', 'type' => 'short_text', 'title' => 'Full Name', 'required' => true],
                        ['id' => 'q2', 'type' => 'email', 'title' => 'Email Address', 'required' => true],
                        ['id' => 'q3', 'type' => 'phone', 'title' => 'Phone Number', 'required' => true],
                        ['id' => 'q4', 'type' => 'short_text', 'title' => 'Position Applied For', 'required' => true],
                        ['id' => 'q5', 'type' => 'number', 'title' => 'Years of Experience', 'required' => true],
                        ['id' => 'q6', 'type' => 'file_upload', 'title' => 'Resume / CV', 'required' => true, 'accept' => '.pdf,.doc,.docx'],
                        ['id' => 'q7', 'type' => 'paragraph', 'title' => 'Cover Letter', 'required' => false],
                    ],
                    'sections' => [['id' => 'sec_1', 'title' => 'Application', 'order' => 0]],
                ]),
                'settings' => json_encode(['collect_email' => true, 'allow_multiple' => false]),
                'theme' => json_encode(['preset' => 'professional', 'primary_color' => '#0f172a']),
            ],
            [
                'id' => 'tpl_student_registration',
                'title' => 'Student Registration',
                'description' => 'Register students for courses or programs.',
                'category' => 'Education',
                'icon' => 'bi-mortarboard',
                'schema' => json_encode([
                    'questions' => [
                        ['id' => 'q1', 'type' => 'short_text', 'title' => 'Full Name', 'required' => true],
                        ['id' => 'q2', 'type' => 'email', 'title' => 'Email', 'required' => true],
                        ['id' => 'q3', 'type' => 'number', 'title' => 'Roll Number', 'required' => true],
                        ['id' => 'q4', 'type' => 'dropdown', 'title' => 'Department', 'required' => true, 'options' => [
                            ['value' => 'CSE', 'label' => 'Computer Science'],
                            ['value' => 'ECE', 'label' => 'Electronics'],
                            ['value' => 'ME', 'label' => 'Mechanical'],
                            ['value' => 'CE', 'label' => 'Civil'],
                            ['value' => 'EE', 'label' => 'Electrical'],
                        ]],
                        ['id' => 'q5', 'type' => 'dropdown', 'title' => 'Year', 'required' => true, 'options' => [
                            ['value' => '1', 'label' => '1st Year'],
                            ['value' => '2', 'label' => '2nd Year'],
                            ['value' => '3', 'label' => '3rd Year'],
                            ['value' => '4', 'label' => '4th Year'],
                        ]],
                        ['id' => 'q6', 'type' => 'phone', 'title' => 'Phone Number', 'required' => false],
                    ],
                    'sections' => [['id' => 'sec_1', 'title' => 'Student Information', 'order' => 0]],
                ]),
                'settings' => json_encode(['collect_email' => true, 'allow_multiple' => false]),
                'theme' => json_encode(['preset' => 'edoble', 'primary_color' => '#6366f1']),
            ],
            [
                'id' => 'tpl_survey',
                'title' => 'Customer Survey',
                'description' => 'Comprehensive customer satisfaction survey.',
                'category' => 'Feedback',
                'icon' => 'bi-clipboard-data',
                'schema' => json_encode([
                    'questions' => [
                        ['id' => 'q1', 'type' => 'rating', 'title' => 'How satisfied are you with our product?', 'required' => true, 'max' => 5],
                        ['id' => 'q2', 'type' => 'rating', 'title' => 'How satisfied are you with our support?', 'required' => true, 'max' => 5],
                        ['id' => 'q3', 'type' => 'linear_scale', 'title' => 'How likely are you to recommend us? (NPS)', 'required' => true, 'scaleMin' => 0, 'scaleMax' => 10, 'minLabel' => 'Not likely', 'maxLabel' => 'Very likely'],
                        ['id' => 'q4', 'type' => 'checkboxes', 'title' => 'What do you like most?', 'required' => false, 'options' => [
                            ['value' => 'Quality', 'label' => 'Product Quality'],
                            ['value' => 'Price', 'label' => 'Pricing'],
                            ['value' => 'Support', 'label' => 'Customer Support'],
                            ['value' => 'UX', 'label' => 'User Experience'],
                            ['value' => 'Features', 'label' => 'Features'],
                        ]],
                        ['id' => 'q5', 'type' => 'paragraph', 'title' => 'Any suggestions for improvement?', 'required' => false],
                    ],
                    'sections' => [['id' => 'sec_1', 'title' => 'Survey', 'order' => 0]],
                ]),
                'settings' => json_encode(['collect_email' => false, 'allow_multiple' => true, 'show_progress' => true]),
                'theme' => json_encode(['preset' => 'modern', 'primary_color' => '#8b5cf6']),
            ],
        ];
    }
}
