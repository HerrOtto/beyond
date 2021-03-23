<?php

class mailHandler
{

    private $language; // string
    private $prefix; // string
    private $database; // beyondDatabaseDriver
    private $tools; // beyondTools
    private $config; // beyondConfig

    /**
     * Constructor
     * @param string $language Current user language
     * @param string $prefix Prefix
     * @param beyondDatabaseDriver $database Database connection of content plugin
     * @param beyondTools $tools Global beyond tools
     * @param beyondConfig $config Configuration
     */
    public function __construct($language, $prefix, $database, $tools, $config)
    {
        $this->language = $language;
        $this->prefix = $prefix;
        $this->database = $database;
        $this->tools = $tools;
        $this->config = $config;

        $this->cache = array();
    }

    /**
     * Get site header
     * @param mixed $to Mail recipient
     * @param mixed $subject Mail subject
     * @param mixed $body Mail body
     * @param mixed $kind Mail kind "html" or "text"
     * @param mixed $language If "false" use current user language otherwise the specified
     * @param mixed $replyTo Overwrite default "reply to"
     * @param mixed $bcc Overwrite default "bcc"
     * @param mixed $from Overwrite default "from"
     * @param mixed $attachment Path to attachment
     * @param mixed $attachmentName Name of attachment
     * @result string Field content
     */
    public function send($to, $subject, $body, $kind = 'text', $language = false, $replyTo = false, $bcc = false, $from = false, $attachment = false, $attachmentName = false)
    {

        // Load default values
        $configJson = file_get_contents(__DIR__ . '/../../../config/mail_settings.json');
        if (trim($configJson) === '') {
            $configObj = new stdClass();
        } else {
            $configObj = json_decode($configJson);
        }

        // Language
        if ($language === false) {
            $language = $this->language;
        }

        // Subject
        if ((property_exists($configObj->{'settings_' . $language}, 'subjectPrefix')) && (trim($configObj->{'settings_' . $language}->subjectPrefix) !== '')) {
            $subject = $configObj->{'settings_' . $language}->subjectPrefix . $subject;
        } else if ((property_exists($configObj->{'settings_default'}, 'subjectPrefix')) && (trim($configObj->{'settings_default'}->subjectPrefix) !== '')) {
            $subject = $configObj->{'settings_default'}->subjectPrefix . $subject;
        }
        if ((property_exists($configObj->{'settings_' . $language}, 'subjectSuffix')) && (trim($configObj->{'settings_' . $language}->subjectSuffix) !== '')) {
            $subject = $subject . $configObj->{'settings_' . $language}->subjectSuffix;
        } else if ((property_exists($configObj->{'settings_default'}, 'subjectSuffix')) && (trim($configObj->{'settings_default'}->subjectSuffix) !== '')) {
            $subject = $subject . $configObj->{'settings_default'}->subjectSuffix;
        }

        // Footer
        if ($kind === 'html') {
            if ((property_exists($configObj->{'settings_' . $language}, 'footerHtml')) && (trim($configObj->{'settings_' . $language}->footerHtml) !== '')) {
                $body = $body . "\r\n\r\n" . $configObj->{'settings_' . $language}->footerHtml;
            } else if ((property_exists($configObj->{'settings_default'}, 'footerHtml')) && (trim($configObj->{'settings_default'}->footerHtml) !== '')) {
                $body = $body . "\r\n\r\n" . $configObj->{'settings_default'}->footerHtml;
            }
        } else {
            if ((property_exists($configObj->{'settings_' . $language}, 'footerText')) && (trim($configObj->{'settings_' . $language}->footerText) !== '')) {
                $body = $body . "\r\n\r\n" . $configObj->{'settings_' . $language}->footerText;
            } else if ((property_exists($configObj->{'settings_default'}, 'footerText')) && (trim($configObj->{'settings_default'}->footerText) !== '')) {
                $body = $body . "\r\n\r\n" . $configObj->{'settings_default'}->footerText;
            }
        }

        // From
        if ($from === false) {
            $from = '';
            if ((property_exists($configObj->{'settings_' . $language}, 'from')) && (trim($configObj->{'settings_' . $language}->from) !== '')) {
                $from = $configObj->{'settings_' . $language}->from;
            } else if ((property_exists($configObj->{'settings_default'}, 'from')) && (trim($configObj->{'settings_default'}->from) !== '')) {
                $from = $configObj->{'settings_default'}->from;
            }
            if (trim($from) === '') {
                $from = $this->config->get('base', 'mail.from', 'root@localhost'); // System default
            }
        }

        // To
        if ($to === false) {
            if ((property_exists($configObj->{'settings_' . $language}, 'to')) && (trim($configObj->{'settings_' . $language}->to) !== '')) {
                $to = $configObj->{'settings_' . $language}->to;
            } else if ((property_exists($configObj->{'settings_default'}, 'to')) && (trim($configObj->{'settings_default'}->to) !== '')) {
                $to = $configObj->{'settings_default'}->to;
            }
            if (trim($to) === '') {
                $to = $this->config->get('base', 'mail.to', 'root@localhost'); // System default
            }
        }

        // Reply to
        if ($replyTo === false) {
            $replyTo = '';
            if ((property_exists($configObj->{'settings_' . $language}, 'replyTo')) && (trim($configObj->{'settings_' . $language}->replyTo) !== '')) {
                $replyTo = $configObj->{'settings_' . $language}->replyTo;
            } else if ((property_exists($configObj->{'settings_default'}, 'replyTo')) && (trim($configObj->{'settings_default'}->replyTo) !== '')) {
                $replyTo = $configObj->{'settings_default'}->replyTo;
            }
            if (trim($replyTo) === '') {
                $replyTo = $this->config->get('base', 'mail.replyTo', ''); // System default
            }
        }

        // BCC
        if ($bcc === false) {
            $bcc = '';
            if ((property_exists($configObj->{'settings_' . $language}, 'bcc')) && (trim($configObj->{'settings_' . $language}->bcc) !== '')) {
                $bcc = $configObj->{'settings_' . $language}->bcc;
            } else if ((property_exists($configObj->{'settings_default'}, 'bcc')) && (trim($configObj->{'settings_default'}->bcc) !== '')) {
                $bcc = $configObj->{'settings_default'}->bcc;
            }
            if (trim($bcc) === '') {
                $bcc = $this->config->get('base', 'mail.bcc', ''); // System default
            }
        }

        // Send mail
        $this->tools->sendMail(
            $subject,
            $body, // $mail,
            $kind, // $kind
            $from, // $from
            $to, // $to
            $replyTo, // $replyTo
            $bcc, // $bcc
            $attachment, // attachment
            $attachmentName // attachment name
        );

        // Add mail to database
        $query = $this->database->insert(
            $this->prefix . 'mail_data',
            array(
                'date' => date('d.m.Y h:i:s', time()),
                'from' => $from,
                'to' => $to,
                'replyTo' => $replyTo,
                'bcc' => $bcc,
                'subject' => $subject,
                'mail' => $body,
                'kind' => $kind
            )
        );
        if ($query === false) {
            throw new Exception('Can not insert into table [' . $this->prefix . 'mail_data]');
        }

    }


}
