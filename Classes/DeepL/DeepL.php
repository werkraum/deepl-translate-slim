<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\DeepL;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeepL implements LoggerAwareInterface, SingletonInterface
{
    use LoggerAwareTrait;

    /**
     * API BASE URL
     * https://api.deepl.com/v2/[resource]?auth_key=12345
     */
    public const API_URL_BASE = '%s://%s/v%s/%s';

    protected bool $authKeyInHeader = false;

    /**
     * DeepL API Version (v2 is default since 2018)
     */
    protected int $apiVersion = 2;

    /**
     * DeepL API Auth Key (DeepL Pro access required)
     */
    protected string $authKey = '';

    /**
     * cURL resource
     *
     * @var resource
     */
    protected \CurlHandle $curl;

    /**
     * Hostname of the API (in most cases api.deepl.com)
     */
    protected string $host = 'api-free.deepl.com';

    /**
     * Maximum number of seconds the query should take
     */
    protected ?int $timeout = null;

    protected $internalCache = [];

    protected bool $debug;

    /**
     * DeepL constructor
     */
    public function __construct(?string $authKey = null, ?string $host = null)
    {
        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('deepl_translate');
        $this->authKey = $authKey ?? $config['authenticationKey'];
        $this->host = $host ?? $config['host'];
        $this->curl = curl_init();

        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(self::class);

        $this->debug = getenv('DEEPL_DUMMY_REQUESTS') && (bool)getenv('DEEPL_DUMMY_REQUESTS');
    }

    /**
     * DeepL destructor
     */
    public function __destruct()
    {
        if (!$this->curl) {
            return;
        }
        if (!is_resource($this->curl)) {
            return;
        }
        curl_close($this->curl);
    }

    public function setApiVersion(int $apiVersion): void
    {
        $this->apiVersion = $apiVersion;
    }

    public function setAuthKey(string $authKey): void
    {
        $this->authKey = $authKey;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * Set a timeout for queries to the DeepL API
     *
     * @param int $timeout Timeout in seconds
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return bool
     */
    public function isAuthKeyInHeader()
    {
        return $this->authKeyInHeader;
    }

    public function setAuthKeyInHeader(bool $authKeyInHeader): void
    {
        $this->authKeyInHeader = $authKeyInHeader;
    }

    /**
     * Translate the text string or array from source to destination language
     * For detailed info on Parameters see README.md
     *
     * @param string|string[] $text
     * @param bool|string $splitSentences
     *
     * @return array<int, array{detected_source_language: string, text: string}>
     * @throws DeepLException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function translate(
        $text,
        string $sourceLang = 'en',
        string $targetLang = 'de',
        string $tagHandling = 'xml',
        string $ignoreTags = 'script,style,img,audio,video',
        string $formality = 'default',
        string $splitSentences = 'nonewlines',
        int $preserveFormatting = 0,
        string $nonSplittingTags = '',
        int $outlineDetection = 1,
        string $splittingTags = '',
        string $glossaryId = ''
    ): array {
        $params = [
            'text' => $text,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'splitting_tags' => $splittingTags,
            'non_splitting_tags' => $nonSplittingTags,
            'ignore_tags' => $ignoreTags,
            'tag_handling' => $tagHandling,
            'formality' => $formality,
            'split_sentences' => $splitSentences,
            'preserve_formatting' => $preserveFormatting,
            'outline_detection' => $outlineDetection,
            'glossary_id' => $glossaryId,
        ];

        $params = $this->removeEmptyParams($params);
        $url = $this->buildBaseUrl();
        $body = $this->buildQuery($params);

        if ($this->debug) {
            if (!is_array($text)) {
                $text = [$text];
            }
            return \array_map(static fn($t): array => [
                'text' => $t
            ], $text);
        }

        $debugParams = $params;
        if (\is_array($debugParams['text'])) {
            $temp = [];
            foreach ($debugParams['text'] as $t) {
                $temp = \substr((string) $t, 0, 15) . '[...]';
            }
            $debugParams['text'] = $temp;
        } else {
            $debugParams['text'] = \substr((string) $debugParams['text'], 0, 15) . '[...]';
        }
        $this->logger->debug('translate', [
            'params' => $debugParams,
            'request' => [
                $_SERVER['HTTP_COOKIE'] ?? '',
                $_SERVER['HTTP_REFERER'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $_SERVER['HTTP_X_REAL_IP'] ?? '',
                $_SERVER['HTTP_HOST'] ?? '',
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['REMOTE_PORT'] ?? '',
                $_SERVER['REQUEST_URI'] ?? '',
            ]
        ]);
        // request the DeepL API
        $translationsArray = $this->request($url, $body);

        return $translationsArray['translations'];
    }

    private function removeEmptyParams(array $params): array
    {
        foreach ($params as $key => $value) {
            if (empty($value)) {
                unset($params[$key]);
            }
            // Special Workaround for outline_detection which will be unset above
            // DeepL assumes outline_detection=1 if it is not send
            // in order to deactivate it, we need to send outline_detection=0 to the api
            if ('outline_detection' === $key) {
                if (1 === $value) {
                    unset($params[$key]);
                }

                if (0 === $value) {
                    $params[$key] = 0;
                }
            }
        }

        return $params;
    }

    protected function buildBaseUrl(string $resource = 'translate'): string
    {
        $base = sprintf(
            self::API_URL_BASE,
            'https',
            $this->host,
            $this->apiVersion,
            $resource
        );

        if (!$this->authKeyInHeader) {
            return $base . '?auth_key=' . $this->authKey;
        }

        return $base;
    }

    protected function buildQuery(array $params): string
    {
        if (isset($params['text']) && is_array($params['text'])) {
            $text = $params['text'];
            unset($params['text']);
            $textString = '';
            foreach ($text as $textElement) {
                $textString .= '&text=' . rawurlencode((string) $textElement);
            }
        }

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = implode(',', $value);
            }
        }

        $body = http_build_query($params, null, '&');

        if (isset($textString)) {
            return $textString . '&' . $body;
        }

        return $body;
    }

    /**
     * Make a request to the given URL
     *
     * @return array|string|bool
     * @throws DeepLException
     * @throws \Exception
     */
    protected function request(string $url, string $body = '', string $method = 'POST', array $headers = [])
    {
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);

        if ($this->authKeyInHeader) {
            $headers []= 'Authorization: DeepL-Auth-Key ' . $this->authKey;
        }

        switch ($method) {
            case 'GET':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
                break;
            case 'POST':
                curl_setopt($this->curl, CURLOPT_POST, true);
                $headers []= 'Content-Type: application/x-www-form-urlencoded';
                break;
            case 'PUT':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
            case 'DELETE':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                throw new \Exception('Unexpected value');
        }

        if (is_array($headers) && $headers !== []) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }

        if ($this->timeout !== null) {
            curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
        }

        $response = curl_exec($this->curl);

        if (curl_errno($this->curl) !== 0) {
            throw new DeepLException('There was a cURL Request Error : ' . curl_error($this->curl));
        }
        $httpCode = curl_getinfo($this->curl, CURLINFO_RESPONSE_CODE);
        // do not throw errors on json_decode, response might be simple text handled below
        $responseArray = json_decode($response, true, 512);

        if ($httpCode != 200 && is_array($responseArray) && array_key_exists('message', $responseArray)) {
            $exception = new DeepLException($responseArray['message'] ?? '', $httpCode, null);
            $exception->setDetail($responseArray['detail'] ?? '');
            throw $exception;
        }

        if (!is_array($responseArray)) {
            if ($httpCode >= 200 && $httpCode < 400) {
                return $response;
            }

            throw new DeepLException('DeepL API returned with status code ' . $httpCode, 1_634_708_875_340);
        }

        return $responseArray;
    }

    /**
     * Call languages-Endpoint and return Json-response as an Array
     *
     * @return array<int, array{language: string, name: string, supports_formality: bool}>
     * @throws DeepLException
     */
    public function languages(string $type = 'source'): array
    {
        if (isset($this->internalCache['language_' . $type])) {
            return $this->internalCache['language_' . $type];
        }
        $url = $this->buildBaseUrl('languages');
        $body = $this->buildQuery(['type' => $type]);
        return $this->request($url, $body, 'GET');
    }

    /**
     * @return array{character_count: int, character_limit: int}
     * @throws DeepLException
     */
    public function usage(): array
    {
        if (isset($this->internalCache['usage'])) {
            return $this->internalCache['usage'];
        }
        $url = $this->buildBaseUrl('usage');
        return $this->request($url, '', 'GET');
    }

    /**
     * @throws DeepLException
     */
    public function supportsFormality(string $target): bool
    {
        $data = $this->languageData($target);
        if (\is_array($data)) {
            return $data['supports_formality'];
        }
        return false;
    }

    /**
     * @return array{language: string, name: string, supports_formality: bool}|null
     * @throws DeepLException
     */
    public function languageData(string $target)
    {
        $languages = $this->languages('target');

        foreach ($languages as $language) {
            if ($language['language'] === $target) {
                return $language;
            }
        }
        return null;
    }
}
