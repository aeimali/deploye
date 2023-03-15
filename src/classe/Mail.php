<?php

namespace App\classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $api_key = '70c5613f3334b17c8ca6a259e3f084dc';
    private $api_key_secret = '44c78d039021bef3afe64bea099d6f33';

    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "mamadout710@gmail.com",
                        'Name' => "Aeimali"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 4463511,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}