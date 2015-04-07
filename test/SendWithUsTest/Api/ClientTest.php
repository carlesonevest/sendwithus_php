<?php

namespace SendWithUsTest\Api;

use SendWithUs\Api\Client;

/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    private $EMAIL_ID = 'test_fixture_1';
    private $SEGMENT_ID = 'seg_VC8FDxDno9X64iUPDFSd76';

    private $options = null;

    /** @var Client */
    private $api = null;
    private $recipient = null;
    private $incompleteRecepient = null;
    private $sender = null;
    private $data = null;
    private $cc = null;
    private $bcc = null;

    public function setUp()
    {
        $this->options = array(
            'debugMode' => false
        );

        $this->api = new Client($this->API_KEY, $this->options);

        $this->good_html = '<html><head></head><body></body></html>';

        $this->bad_html = '<html><hed><body></body</html>';

        $this->recipient = array(
            'name' => 'Unit Tests - PHP Client',
            'address' => 'swunit+phpclient@sendwithus.com'
        );

        $this->incompleteRecipient = array(
            'name' => 'Unit Tests - PHP Client'
        );

        $this->sender = array(
            'name' => 'Company Name',
            'address' => 'company@example.com',
            'reply_to' => 'info@example.com'
        );

        $this->data = array(
            'name' => 'Jimmy the snake'
        );

        $this->cc = array(
            array(
                'name' => 'test cc',
                'address' => 'testcc@example.com'
            )
        );

        $this->bcc = array(
            array(
                'name' => 'test bcc',
                'address' => 'testbcc@example.com'
            )
        );

        $this->inline = 'test/test_img.png';

        $this->files = array('test/test_img.png', 'test/test_txt.txt');

        $this->tags = array('tag_one', 'tag_two');

        $this->template_id = 'pmaBsiatWCuptZmojWESme';

        $this->version_id = 'ver_pYj27c8DTBsWB4MRsoB2MF';

        $this->enabled_drip_campaign_id = 'dc_Rmd7y5oUJ3tn86sPJ8ESCk';

        $this->enabled_drip_campaign_step_id = 'dcs_yaAMiZNWCLAEGw7GLjBuGY';

        $this->disabled_drip_campaign_id = 'dc_AjR6Ue9PHPFYmEu2gd8x5V';

        $this->false_drip_campaign_id = 'false_drip_campaign_id';

        $this->log_id = '130be975-dc07-4071-9333-58530e5df052-i03a5q';
    }

    protected function tearDown()
    {
        //var_dump(memory_get_usage());
    }

    protected function assertSuccess($r)
    {
        $this->assertEquals("OK", $r->status);
        $this->assertTrue($r->success);
    }

    protected function assertFail($r)
    {
        $this->assertNotEquals(200, $r->code);
        $this->assertEquals("error", $r->status);
        $this->assertFalse($r->success);
        $this->assertNotNull($r->exception);
    }

    public function testGetEmails()
    {
        $r = $this->api->emails();
        $this->assertNotNull($r);
        print 'Got emails';
    }

    public function testGetLogs()
    {
        $r = $this->api->logs();
        $this->assertNotNull($r);
        print 'Got logs';
    }

    public function testGetSingleLog()
    {
        $r = $this->api->getLog($this->log_id);
        $this->assertNotNull($r);
        print 'Getting a log';
    }

    public function testCreateEmailSuccess()
    {
        $r = $this->api->createEmail(
            'test name',
            'test subject',
            $this->good_html
        );

        $this->assertNotNull($r);
        print 'Created an email';
    }

    public function testCreateNewTemplateVersion()
    {
        $r = $this->api->createNewTemplateVersion(
            'test name',
            'test subject',
            $this->template_id,
            $html=$this->good_html
        );
        $this->assertNotNull(isset($r->created) ? $r->created : null);
        print "Created a new template version";
    }

    public function testUpdateTemplateVersion()
    {
        $r = $this->api->updateTemplateVersion(
            'test name',
            'test subject',
            $this->template_id,
            $this->version_id,
            $this->good_html
        );
        $this->assertNotNull(isset($r->created) ? $r->created : null);
        print "Updated a template version";
    }

    public function testSimpleSend()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send';
    }

    public function testSendWithEmptyData()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => array())
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Send with empty data';
    }

    public function testSendWithNullData()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => null)
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Send with null data';
    }

    public function testSendWithSender()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "sender" => $this->sender
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with Sender';
    }

    public function testSendWithCC()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "cc" => $this->cc
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with CC';
    }

    public function testSendWithBCC()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "bcc" => $this->bcc
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with bcc';
    }

    public function testSendWithInline()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "inline" => $this->inline
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with inline';
    }

    public function testSendWithFiles()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "files" => $this->files
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with file attachments';
    }

    public function testSendWithTags()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "tags" => $this->tags
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with tags';
    }

    public function testSendIncomplete()
    {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->incompleteRecipient,
            array(
                "data" => $this->data,
                "sender" => $this->sender
            )
        );

        $this->assertFail($r);
        $this->assertEquals($r->code, 400); // incomplete

        print 'Simple bad send';
    }

    public function testInvalidAPIKey()
    {
        $api = new Client('INVALID_API_KEY', $this->options);

        $r = $api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => $this->data)
        );

        $this->assertFail($r);
        $this->assertEquals($r->code, 403); // bad api key

        print 'Test bad api key';
    }


    public function testInvalidEmailId()
    {
        $r = $this->api->send(
            'INVALID_EMAIL_ID',
            $this->recipient,
            array("data" => $this->data)
        );

        $this->assertFail($r);
        $this->assertEquals($r->code, 400); // email_id not found

        print 'Test invalid email id';
    }

    public function testRender()
    {
        $r = $this->api->render(
            $this->EMAIL_ID,
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->html ?: $r->text);
        print 'Test render';
    }

    public function testCreateCustomer()
    {
        $r = $this->api->createCustomer(
            $this->recipient['address'],
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        print 'Test create customer';
    }

    public function testGetCustomer()
    {
        $r = $this->api->createCustomer(
            $this->recipient['address'],
            array("data" => $this->data)
        );
        $this->assertSuccess($r);
        $r = $this->api->getCustomer(
            $this->recipient['address']
        );
        $this->assertSuccess($r);

        print 'Test get customer';
    }

    public function testUpdateCustomer()
    {
        $r = $this->api->updateCustomer(
            $this->recipient['address'],
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        print 'Test update customer';
    }

    public function testDeleteCustomer()
    {
        $r = $this->api->createCustomer($this->recipient['address']);
        $this->assertSuccess($r);

        $r = $this->api->deleteCustomer($this->recipient['address']);
        $this->assertSuccess($r);

        print 'Test delete customer';
    }

    public function testCustomerConversion()
    {
        $r = $this->api->customerConversion($this->recipient['address']);
        $this->assertSuccess($r);

        print 'Test customer conversion';
    }

    public function testCustomerConversionRevenue()
    {
        $r = $this->api->customerConversion($this->recipient['address'], 1234);
        $this->assertSuccess($r);

        print 'Test customer conversion revenue';
    }

    public function testSendSegment()
    {
        $r = $this->api->sendSegment($this->EMAIL_ID, $this->SEGMENT_ID);
        $this->assertSuccess($r);

        print 'Test send segment';
    }

    public function testListDripCampaigns()
    {
        $r = $this->api->listDripCampaigns();
        $this->assertNotNull($r);

        print 'Test list drip campaigns';
    }

    public function testListDripCampaignDetails()
    {
        $r = $this->api->dripCampaignDetails($this->enabled_drip_campaign_id);

        $this->assertEquals('TEST_CAMPAIGN', isset($r->name) ? $r->name : null);

        print 'Test list drip campaign details';
    }

    public function testStartOnEnabledDripCampaign()
    {
        $r = $this->api->dripCampaignDetails($this->enabled_drip_campaign_id);
        $this->assertTrue(isset($r->enabled) ? $r->enabled : null);

        $r = $this->api->startOnDripCampaign('person@example.com', $this->enabled_drip_campaign_id);
        $this->assertSuccess($r);

        print 'Test add to enabled drip campaigns';
    }

    public function testStartOnEnabledDripCampaignWithData()
    {
        $r = $this->api->dripCampaignDetails($this->enabled_drip_campaign_id);
        $this->assertTrue(isset($r->enabled) ? $r->enabled : null);

        $r = $this->api->startOnDripCampaign('person@example.com', $this->enabled_drip_campaign_id, $this->data);
        $this->assertSuccess($r);

        print 'Test add to enabled drip campaigns with data';
    }

    public function testStartOnDisabledDripCampaign()
    {
        $r = $this->api->dripCampaignDetails($this->disabled_drip_campaign_id);
        $this->assertFalse(isset($r->enabled) ? $r->enabled : null);

        $r = $this->api->startOnDripCampaign('person@example.com', $this->disabled_drip_campaign_id);
        $this->assertFail($r);

        print 'Test add to disabled drip campaigns';
    }

    public function testStartOnFalseDripCampaign()
    {
        $r = $this->api->dripCampaignDetails($this->false_drip_campaign_id);
        $this->assertFail($r);

        $r = $this->api->startOnDripCampaign('person@example.com', $this->false_drip_campaign_id);
        $this->assertFail($r);

        print 'Test add to false drip campaigns';
    }

    public function testListCustomersOnCampaign()
    {
        $r = $this->api->listDripCampaignCustomers($this->enabled_drip_campaign_id);

        $this->assertEquals($this->enabled_drip_campaign_id, isset($r->id) ? $r->id : null);

        print 'Test list customers on drip campaign';
    }

    public function testListCustomersOnCampaignStep()
    {
        $r = $this->api->listDripCampaignStepCustomers($this->enabled_drip_campaign_id, $this->enabled_drip_campaign_step_id);

        $this->assertEquals($this->enabled_drip_campaign_step_id, isset($r->id) ? $r->id : null);

        print 'Test list customers on a drip campaign step';
    }

    public function testRemoveOnDripCampaign()
    {
        $r = $this->api->removeFromDripCampaign('person@example.com', $this->enabled_drip_campaign_id);
        $this->assertSuccess($r);

        print 'Test remove from drip campaigns';
    }

    public function testListDripCampaignSteps()
    {
        $r = $this->api->dripCampaignDetails($this->enabled_drip_campaign_id);

        $this->assertEquals('TEST_CAMPAIGN', isset($r->name) ? $r->name : null);
        print 'Test list drip campaign steps';
    }
}
