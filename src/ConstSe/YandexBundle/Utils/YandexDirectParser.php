<?php

namespace ConstSe\YandexBundle\Utils;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class YandexDirectParser
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RemoteRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->request = $this->container->get('yandex.remote_request');
        $this->token = $this->container->getParameter('yandex.direct.token');
    }

    /**
     * @param string $login
     * @return array
     * @throws \Exception
     */
    public function loadClientCampaigns($login)
    {
        $campaigns = array();

        $importCampaigns = array();
        $offset = 0;

        while (false !== $offset) {
            $result = $this->sendApi5Request(
                'campaigns',
                $login,
                [
                    'SelectionCriteria' => ['States' => ['ON', 'SUSPENDED', 'OFF', 'ENDED']],
                    'FieldNames' => ['Id', 'Name', 'StartDate', 'EndDate', 'State', 'Status', 'TimeTargeting'],
                    'Page' => ['Limit' => 1000, 'Offset' => $offset]
                ]
            );
            $result = $result['result'];
            $importCampaigns += $result['Campaigns'];
            $offset = array_key_exists('LimitedBy', $result) ? $result['LimitedBy'] : false;
        }

        foreach ($importCampaigns as $importCampaign) {
            $campaigns[$importCampaign['Id']] = [
                'id' => $importCampaign['Id'],
                'name' => $importCampaign['Name'],
                'startedAt' => new \DateTime($importCampaign['StartDate']),
                'finishedAt' => $importCampaign['EndDate'] ? new \DateTime($importCampaign['EndDate']) : null,
                'state' => $importCampaign['State'],
                'status' => $importCampaign['Status'],
                'groups' => []
            ];
        }

        $campaignIds = array_keys($campaigns);
        $campaignCount = count($campaignIds);
        $importGroups = array();

        for ($i = 0; 10 * $i < $campaignCount; $i++) {
            $offset = 0;

            while (false !== $offset) {
                $result = $this->sendApi5Request(
                    'adgroups',
                    $login,
                    [
                        'SelectionCriteria' => ['CampaignIds' => array_slice($campaignIds, 10 * $i, 10)],
                        'FieldNames' => ['Id', 'CampaignId', 'Name', 'Status'],
                        'Page' => ['Limit' => 10000, 'Offset' => $offset]
                    ]
                );
                $result = $result['result'];
                $importGroups = array_merge($importGroups, $result['AdGroups']);
                $offset = array_key_exists('LimitedBy', $result) ? $result['LimitedBy'] : false;
            }
        }

        $groupIds = array();

        foreach ($importGroups as $importGroup) {
            $campaigns[$importGroup['CampaignId']]['groups'][$importGroup['Id']] = [
                'id' => $importGroup['Id'],
                'name' => $importGroup['Name'],
                'status' => $importGroup['Status'],
                'ads' => [],
                'keywords' => []
            ];
            $groupIds[] = $importGroup['Id'];
        }

        $groupCount = count($groupIds);
        $importAds = array();

        for ($i = 0; 1000 * $i < $groupCount; $i++) {
            $offset = 0;

            while (false !== $offset) {
                $result = $this->sendApi5Request(
                    'ads',
                    $login,
                    [
                        'SelectionCriteria' => ['AdGroupIds' => array_slice($groupIds, 1000 * $i, 1000)],
                        'FieldNames' => ['Id', 'CampaignId', 'AdGroupId', 'Type', 'State', 'Status'],
                        'TextAdFieldNames' => ['Title', 'Text'],
                        'MobileAppAdFieldNames' =>['Title', 'Text'],
                        'DynamicTextAdFieldNames' => ['Text'],
                        'Page' => ['Limit' => 10000, 'Offset' => $offset]
                    ]
                );
                $result = $result['result'];
                $importAds = array_merge($importAds, $result['Ads']);
                $offset = array_key_exists('LimitedBy', $result) ? $result['LimitedBy'] : false;
            }
        }

        foreach ($importAds as $importAd) {
            $title = null;
            $text = null;

            switch ($importAd['Type']) {
                case 'TEXT_AD':
                    $title = $importAd['TextAd']['Title'];
                    $text = $importAd['TextAd']['Text'];

                    break;
                case 'MOBILE_APP_AD':
                    $title = $importAd['MobileAppAd']['Title'];
                    $text = $importAd['MobileAppAd']['Text'];

                    break;
                case 'DYNAMIC_TEXT_AD':
                    $text = $importAd['DynamicTextAd']['Text'];

                    break;
            }

            $campaigns[$importAd['CampaignId']]['groups'][$importAd['AdGroupId']]['ads'][$importAd['Id']] = [
                'id' => $importAd['Id'],
                'title' => $title,
                'text' => $text,
                'state' => $importAd['State'],
                'status' => $importAd['Status']
            ];
        }

        $importKeywords = array();

        for ($i = 0; 1000 * $i < $groupCount; $i++) {
            $offset = 0;

            while (false !== $offset) {
                $result = $this->sendApi5Request(
                    'keywords',
                    $login,
                    [
                        'SelectionCriteria' => ['AdGroupIds' => array_slice($groupIds, 1000 * $i, 1000)],
                        'FieldNames' => ['Id', 'CampaignId', 'AdGroupId', 'Keyword', 'State', 'Status', 'Productivity'],
                        'Page' => ['Limit' => 10000, 'Offset' => $offset]
                    ]
                );
                $result = $result['result'];
                $importKeywords = array_merge($importKeywords, $result['Keywords']);
                $offset = array_key_exists('LimitedBy', $result) ? $result['LimitedBy'] : false;
            }
        }

        foreach ($importKeywords as $importKeyword) {
            $campaigns[$importKeyword['CampaignId']]['groups'][$importKeyword['AdGroupId']]['keywords'][$importKeyword['Id']] = [
                'id' => (string)$importKeyword['Id'],
                'phrase' => trim(preg_replace('/\s-[^\s]+/', '', $importKeyword['Keyword'])),
                'state' => $importKeyword['State'],
                'status' => $importKeyword['Status'],
                'productivity' => array_key_exists('Productivity', $importKeyword) ?
                    (float)$importKeyword['Productivity']['Value'] : null
            ];
        }

        return $campaigns;
    }

    /**
     * @param string $method
     * @param mixed $parameters
     * @param bool $liveVersion
     * @return mixed
     * @throws \Exception
     */
    protected function sendApi4Request($method, $parameters = null, $liveVersion = false)
    {
        $url = sprintf('https://api.direct.yandex.com/%sv4/json/', $liveVersion ? 'live/' : '');
        $query = ['token' => $this->token, 'locale' => 'en', 'method' => $method];

        if ($parameters) {
            $query['param'] = $parameters;
        }

        $response = $this->request->send(
            $url,
            [
                'method' => 'POST',
                'headers' => 'Content-Type: application/json; charset=utf-8',
                'data' => json_encode($query)
            ],
            false,
            RemoteRequest::CONVERT_JSON
        );

        if (false === $response) {
            throw new \Exception(
                sprintf(
                    'Failed to get response from "%s": %s',
                    $url,
                    $this->request->getLastError()->getMessage()
                )
            );
        }

        if (array_key_exists('error_code', $response)) {
            throw new \Exception(sprintf(
                'Failed to get response from "%s": [%s] %s (%s)',
                $url,
                $response['error_code'],
                $response['error_str'],
                $response['error_detail']
            ));
        }

        return $response;
    }

    /**
     * @param string $service
     * @param string $login
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    protected function sendApi5Request($service, $login, array $parameters)
    {
        $url = sprintf('https://api.direct.yandex.com/json/v5/%s/', $service);
        $response = $this->request->send(
            $url,
            [
                'method' => 'POST',
                'headers' => [
                    'Authorization: Bearer ' . $this->token,
                    'Accept-Language: en',
                    'Client-Login: ' . $login,
                    'Content-Type: application/json; charset=utf-8'
                ],
                'data' => json_encode(array('method' => 'get', 'params' => $parameters))
            ],
            true,
            RemoteRequest::CONVERT_JSON
        );

        if (false === $response) {
            throw new \Exception(
                sprintf(
                    'Failed to get response from "%s": %s',
                    $url,
                    $this->request->getLastError()->getMessage()
                )
            );
        }

        $headers = $response['headers'];
        $body = $response['body'];

        if (array_key_exists('error', $body)) {
            $error = $body['error'];

            throw new \Exception(
                sprintf(
                    'Failed to get response from "%s" (RequestID: %s): [%s] %s (%s)',
                    $url,
                    array_key_exists('requestId', $headers) ? $headers['requestid'] : 'unknown',
                    $error['error_code'],
                    $error['error_string'],
                    $error['error_detail']
                )
            );
        }

        $units = array_key_exists('Units', $headers) ? $headers['Units'] : null;

        if (
            $units &&
            preg_match('/^\d+\/(\d+)\/(\d+)$/', $units, $matches) &&
            $matches[2] - $matches[1] < 1
        ) {
            throw new \Exception(sprintf('Units overflow: %s/%s', $matches[1], $matches[2]));
        }

        return $body;
    }
}
