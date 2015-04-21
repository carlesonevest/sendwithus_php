<?php

namespace SendWithUs\Api;

use Monolog\Logger;
use SendWithUs\Api\Exception\ApiException;

class Client
{
    const HTTP_POST = 'POST';
    const HTTP_GET = 'GET';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';

    protected $apiKey = 'THIS_IS_A_TEST_API_KEY';
    protected $apiHost = 'api.sendwithus.com';
    protected $apiPort = '443';
    protected $apiProto = 'https';
    protected $apiVersion = '1';
    protected $apiHeaderKey = 'X-SWU-API-KEY';
    protected $apiHeaderClient = 'X-SWU-API-CLIENT';
    protected $apiClientVersion = '2.3.1';
    protected $apiClientStub = 'php-%s';

    /** @var Logger|null */
    protected $logger = null;

    protected $debugMode = false;

    /**
     * @param string $apiKey
     * @param array $options
     * @param Logger|null $logger
     */
    public function __construct($apiKey, $options = array(), Logger $logger = null)
    {
        $this->apiKey = $apiKey;
        $this->apiClientStub = sprintf($this->apiClientStub, $this->apiClientVersion);

        foreach ($options as $key => $value) {
            $this->$key = $value;
        }

        $this->logger = $logger;
    }

    /**
     * Send an email
     *
     * The additional optional parameters are as follows:
     *     'email_data' - Default is null. Array of variables to merge into the template.
     *     'sender' - Default is null. Array ('address', 'name', 'reply_to') of sender.
     *     'cc' - Default is null. Array of ('address', 'name') for carbon copy.
     *     'bcc' - Default is null. Array of ('address', 'name') for blind carbon copy.
     *     'inline' - Default is null. String, path to file to include inline.
     *     'tags' - Default is null. Array of strings to tag email send with.
     *     'version_name' - Default is blank. String, name of version to send
     *
     * @param string $email_id ID of email to send
     * @param array $recipient array of ('address', 'name') to send to
     * @param array $args (optional) additional optional parameters
     * @return array API response object
     */
    public function send($email_id, $recipient, $args = null)
    {
        $endpoint = 'send';

        $payload = array(
            'email_id' => $email_id,
            'recipient' => $recipient
        );

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        // Optional inline attachment
        if (isset($payload['inline'])) {
            $inline_attachment_path = $payload['inline'];

            $payload['inline'] = array(
                'id' => basename($inline_attachment_path),
                'data' => $this->encodeAttachment($inline_attachment_path)
            );
        }

        // Optional file attachment
        if (isset($payload['files'])) {
            foreach ($payload['files'] as &$file) {
                $file = array(
                    'id' => basename($file),
                    'data' => $this->encodeAttachment($file)
                );
            }
        }

        $this->log("sending email `%s` to \n", $email_id);
        $this->log(print_r($recipient, true));

        if (isset($payload['sender'])) {
            $this->log("\nfrom\n");
            $this->log(print_r($payload['sender'], true));
        }

        $this->log("\nwith\n");
        $this->log(print_r($payload, true));

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Get Emails
     *
     * @return array API response object.
     */
    public function emails()
    {
        $endpoint = 'templates';
        return $this->apiRequest($endpoint, self::HTTP_GET);
    }

    /**
     * Get a specific template
     *
     * @param string $templateId template id
     * @param string $versionId optional version id to get template version
     *
     * @return array API response object
     */
    public function getTemplate($templateId, $versionId = null)
    {
        $endpoint = 'templates/' . $templateId;

        if ($versionId) {
            $endpoint .= '/versions/' . $versionId;
        }

        return $this->apiRequest($endpoint, self::HTTP_GET);
    }

    /**
     * Send to a Segment
     *
     * @param string $emailId template id
     * @param string $segmentId segment to send to
     * @param array $data dynamic data for send
     *
     * @return array API response object.
     */
    public function sendSegment($emailId, $segmentId, $data = null)
    {
        $endpoint = 'segments/' . $segmentId . '/send';
        $payload = array('email_id' => $emailId);

        if (is_array($data)) {
            $payload['email_data'] = $data;
        }

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Get Customer
     *
     * @param string $email customer email
     *
     * @return array API response object.
     */
    public function getCustomer($email)
    {
        $endpoint = 'customers/' . $email;

        return $this->apiRequest($endpoint, self::HTTP_GET);
    }

    /**
     * Create Customer
     *
     * @param string $email customer email
     * @param array $data customer data to
     *
     * @return array API response object.
     */
    public function createCustomer($email, $data = null)
    {
        $endpoint = 'customers';
        $payload = array('email' => $email);

        if (is_array($data)) {
            $payload['data'] = $data;
        }

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Update Customer
     *
     * @param string $email customer email
     * @param array $data customer data to
     *
     * @return array API response object.
     */
    public function updateCustomer($email, $data = null)
    {
        return $this->createCustomer($email, $data);
    }

    /**
     * Delete Customer
     *
     * @param string $email customer email
     *
     * @return array API response object.
     */
    public function deleteCustomer($email)
    {
        $endpoint = 'customers/' . $email;

        return $this->apiRequest($endpoint, self::HTTP_DELETE);
    }

    /**
     * Customer Conversion
     *
     * @param string $email customer email
     * @param array $revenue Optional revenue cent value
     *
     * @return array API response object.
     */
    public function customerConversion($email, $revenue = null)
    {
        $endpoint = 'customers/' . $email . '/conversions';
        $payload = array('revenue' => $revenue);

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Create an Email
     *
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @return array API response object
     */
    public function createEmail($name, $subject, $html, $text = null)
    {
        $endpoint = 'templates';

        $payload = array(
            'name' => $name,
            'subject' => $subject,
            'html' => $html
        );

        // set optional text
        if ($text) {
            $payload['text'] = $text;
        }

        $this->log("creating email with name %s and subject %s\n", $name, $subject);

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Create new template version
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $templateId template id
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @return array API response object
     */
    public function createNewTemplateVersion($name, $subject, $templateId, $html, $text = null)
    {
        $endpoint = 'templates/' . $templateId . '/versions';

        $payload = array(
            'name' => $name,
            'subject' => $subject,
            'html' => $html
        );

        // set optional text
        if ($text) {
            $payload['text'] = $text;
        }

        $this->log("creating a new template version with name %s and subject %s\n", $name, $subject);

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Update template version
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $templateId template id
     * @param string $versionId template version id
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @return array API response object
     */
    public function updateTemplateVersion($name, $subject, $templateId, $versionId, $html, $text = null)
    {
        $endpoint = 'templates/' . $templateId . '/versions/' . $versionId;

        $payload = array(
            'name' => $name,
            'subject' => $subject,
            'html' => $html
        );

        // set optional text
        if ($text) {
            $payload['text'] = $text;
        }

        $this->log(
            "updating template\n ID:%s\nVERSION:%s\n with name %s and subject %s\n",
            $templateId,
            $versionId,
            $name,
            $subject
        );

        return $this->apiRequest($endpoint, self::HTTP_PUT, $payload);
    }

    /**
     * Get Email Send Logs
     *
     * @param int $count (optional) the number of logs to return. Max: 100
     * @param int $offset (optional) offset the number of logs to return
     * @return array API response object
     */
    public function logs($count = 100, $offset = 0)
    {
        $endpoint = 'logs';

        $params = array(
            'count' => $count,
            'offset' => $offset
        );

        return $this->apiRequest($endpoint, self::HTTP_GET, null, $params);
    }

    /**
     * Get Specific Email Log
     *
     * @param string $logId the log getting retrieved
     * @return array API response object
     */
    public function getLog($logId)
    {
        $endpoint = 'logs/' . $logId;

        return $this->apiRequest($endpoint, self::HTTP_GET);
    }

    /**
     * Unsubscribe email address from active drips
     *
     * @param string $emailAddress the email to unsubscribe from active drips
     * @return array API response object
     */
    public function dripUnsubscribe($emailAddress)
    {
        $endpoint = 'drips/unsubscribe';

        $payload = array(
            'email_address' => $emailAddress
        );

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * List drip campaigns
     *
     * @return array API response object
     */
    public function listDripCampaigns()
    {
        $endpoint = 'drip_campaigns';

        return $this->apiRequest($endpoint, self::HTTP_GET);
    }

    /**
     * List drip campaign details
     *
     * @param string $dripCampaignId id of drip campaign
     * @return array API response object
     */
    public function dripCampaignDetails($dripCampaignId)
    {
        $endpoint = 'drip_campaigns/' . $dripCampaignId;

        return $this->apiRequest($endpoint, self::HTTP_GET);
    }

    /**
     * List customers on drip campaign
     *
     * @param string $dripCampaignId id of drip campaign
     * @return array API response object
     */
    public function listDripCampaignCustomers($dripCampaignId)
    {
        $endpoint = 'drip_campaigns/' . $dripCampaignId . '/customers';

        return $this->apiRequest($endpoint, self::HTTP_GET);
    }

    /**
     * List customers on drip campaign step
     *
     * @param string $dripCampaignId id of drip campaign
     * @param string $dripStepId id of drip campaign step
     * @return array API response object
     */
    public function listDripCampaignStepCustomers($dripCampaignId, $dripStepId)
    {
        $endpoint = 'drip_campaigns/' . $dripCampaignId . '/steps/' . $dripStepId . '/customers';

        return $this->apiRequest($endpoint, self::HTTP_GET);
    }
    /**
     * Start on drip campaign
     *
     * The additional optional parameters for $args are as follows:
     *     'sender' - Default is null. Array ('address', 'name', 'reply_to') of sender.
     *     'cc' - Default is null. Array of ('address', 'name') for carbon copy.
     *     'bcc' - Default is null. Array of ('address', 'name') for blind carbon copy.
     *     'tags' - Default is null. Array of strings to tag email send with.
     *     'esp' - Default is null. Value of ('esp_account': 'esp_id')
     *
     * @param string $recipientAddress email address being added to drip campaign
     * @param string $dripCampaignId drip campaign being added to
     * @param array (optional) $data email data being sent with drip
     * @param array (optional) $args additional options being sent with email (tags, cc's, etc)
     * @return array API response object
     */
    public function startOnDripCampaign($recipientAddress, $dripCampaignId, $data = null, $args = null)
    {
        $endpoint = 'drip_campaigns/' . $dripCampaignId . '/activate';

        $payload = array(
            'recipient_address' => $recipientAddress
        );

        if (is_array($data)) {
            $payload['email_data'] = $data;
        }

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Remove customer from drip campaign
     *
     * @param string $recipientAddress email address being removed drip campaign
     * @param string $dripCampaignId drip campaign being removed to
     * @return array API response object
     */
    public function removeFromDripCampaign($recipientAddress, $dripCampaignId)
    {
        $endpoint = 'drip_campaigns/' . $dripCampaignId . '/deactivate';

        $payload = array(
            'recipient_address' => $recipientAddress
        );

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Remove customer from all drip campaigns
     *
     * @param string $recipientAddress email address being removed from all drip campaigns
     * @return array API response object
     */
    public function removeFromAllDripCampaigns($recipientAddress)
    {
        $endpoint = 'drip_campaigns/deactivate';

        $payload = array(
            'recipient_address' => $recipientAddress
        );

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Render an email template with the provided data
     *
     * The additional optional parameters are as follows:
     *     'template_data' - Default is null. Array of variables to merge into the template.
     *
     * @param string $email_id ID of email to send
     * @param array $args (optional) additional optional parameters
     * @return array API response object
     */
    public function render($email_id, $args = null)
    {
        $endpoint = 'render';

        $payload = array(
            'template_id' => $email_id
        );

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        $this->log("rendering template `%s` with \n", $email_id);
        $this->log(print_r($payload, true));

        return $this->apiRequest($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Helper function to Base64 encode files and return the encoded data
     *
     * @param string $path Local path of the file to encode
     * @return string/false the encoded file data or false on failure
     * @throws ApiException
     */
    protected function encodeAttachment($path)
    {
        if (!is_string($path)) {
            $e = sprintf('inline parameter must be path to file as string, received: %s', gettype($path));
            throw new ApiException($e);
        }

        $file_data = file_get_contents($path);

        return base64_encode($file_data);
    }

    protected function buildPath($endpoint)
    {
        $path = sprintf(
            '%s://%s:%s/api/v%s/%s',
            $this->apiProto,
            $this->apiHost,
            $this->apiPort,
            $this->apiVersion,
            $endpoint
        );

        return $path;
    }

    /**
     * @param string $endpoint
     * @param string $request
     * @param null $payload
     * @param null $params
     * @return array|mixed|object
     */
    protected function apiRequest($endpoint, $request = self::HTTP_POST, $payload = null, $params = null)
    {
        $path = $this->buildPath($endpoint);

        if ($params) {
            $path = $path . '?' . http_build_query($params);
        }

        $ch = curl_init($path);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);

        // set payload
        $payload_string = null;
        if ($payload) {
            $payload_string = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_string);
        }

        // set headers
        if ($payload && ($request == self::HTTP_POST || $request == self::HTTP_PUT)) {
            $httpHeaders = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload_string),
                $this->apiHeaderKey . ': ' . $this->apiKey,
                $this->apiHeaderClient . ': ' . $this->apiClientStub
            );

        } else {
            $httpHeaders = array(
                'Content-Type: application/json',
                $this->apiHeaderKey . ': ' . $this->apiKey,
                $this->apiHeaderClient . ': ' . $this->apiClientStub
            );
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/../data/ca-certificates.pem');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);

        if ($this->debugMode) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }

        $this->log("payload: %s\r\n", $payload_string);
        $this->log("path: %s\r\n", $path);

        $code = null;

        try {
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode($result, true);

            if ($code != 200) {
                throw new ApiException('Request was not successful', $code, $result, $response);
            }

        } catch (ApiException $e) {
            $this->log("Caught exception: %s\r\n", $e->getMessage());
            //$this->log(print_r($e, true));

            $response = (object) array(
                'code' => $code,
                'status' => 'error',
                'success' => false,
                'message' => $e->getMessage(),
                'exception' => $e
            );
        }

        curl_close($ch);

        return $response;
    }

    /**
     * Pass in a message and any number of other vars and this will log
     * that message, with the vars sprintf()'d into the message.
     *
     * @param string $message
     * @param mixed $vars
     */
    protected function log($message, $vars = null)
    {
        if (!$this->debugMode) {
            return;
        }

        $args = func_get_args();
        $message = array_shift($args);
        $vars = $args;
        $output = vsprintf($message, $vars);

        if ($this->logger instanceof Logger) {
            $this->logger->addInfo($output);

        } else {
            error_log($output);
        }
    }
}
