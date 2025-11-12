<?php
// app/Services/VboutService.php

namespace App\Vbout;

use GuzzleHttp\Client;

class VboutService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.vbout.com/',
        ]);

        $this->apiKey ='5706248525875818056301053';
    }

    public function addContactToList($listId, $contactData)
    {
        try {
              $formParams = [
                'listid' => $listId,
                'status' => 'active',
                'email' => $contactData['email'],
            ];
        
        $email = $contactData['email'];
        $contactid=0;
 $response1 = $this->client->get('1/emailmarketing/getcontactbyemail.json', [
                'query' => [
                    'key' => $this->apiKey,
                    'email' => $email,
                    'listid' =>$listId,
                ],
            ]);
            // $contact= json_decode($response1->getBody()->getContents(), true);
            $responseBody = json_decode($response1->getBody()->getContents(), true);
           
           if (
                isset($responseBody['response']['data']['contact'][0]['id']) && 
                !empty($responseBody['response']['data']['contact'][0]['id'])
            ) {
                $contactid = $responseBody['response']['data']['contact'][0]['id'];
               
            }
            
            if ($contactid !=0) {
            //   return 1;
                  $response2 = $this->client->post('1/emailmarketing/deletecontact.json', [
                'query' => [
                    'key' => $this->apiKey,
                ],
                'form_params' => [
                    'id' => $contactid,
                    'listid' => $listId,
                ],
            ]);
               json_decode($response2->getBody()->getContents(), true);
            }
           
           
        // $result1 = $vboutService->removeContactFromList($listId, $email);

        // return response()->json($result);
            if (isset($contactData['fields']) && is_array($contactData['fields'])) {
                $formParams = array_merge($formParams, $this->formatContactFields($contactData['fields']));
            }

            $response = $this->client->post('1/emailmarketing/addcontact.json', [
                'query' => [
                    'key' => $this->apiKey,
                ],
                'form_params' => $formParams,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
       protected function formatContactFields($fields)
    {
        $formattedFields = [];
        foreach ($fields as $fieldId => $fieldValue) {
            $formattedFields["fields[$fieldId]"] = $fieldValue;
        }
        return $formattedFields;
    }
}
