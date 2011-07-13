<?php

/**
 * Zend Library Extension
 *
 * PHP version 5
 *
 * @category Zle
 * @package  Zle_Test
 * @author   Fabio Napoleoni <f.napoleoni@gmail.com>
 * @license  http://framework.zend.com/license/new-bsd New BSD License
 * @link     http://framework.zend.com/
 */

/**
 * RewriteRecipientsTest
 *
 * @category Zle
 * @package  Zle_Test
 * @author   Andrea Giannantonio <a.giannantonio@gmail.com>
 * @license  http://framework.zend.com/license/new-bsd New BSD License
 * @link     http://framework.zend.com/
 */
class RewriteRecipientsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Used as const address to test
     */
    const TESTING_ADDRESS = 'user@example.com';

    /**
     * Smtp options sample
     *
     * @var array
     */
    protected $smtpOptions = array(
        'host' => 'mail.example.com',
        'auth' => 'login',
        'username' => 'user@example.com',
        'password' => 'secret',
    );

    /**
     * @var Zle_Mail_Transport_RewriteRecipients
     */
    protected $transport;

    protected function setUp()
    {
        // enable unit testing mode to prevent actual deliveries
        Zle_Mail_Transport_RewriteRecipients::$_unitTestEnabled = true;
        // build a transport instance
        $this->transport = new Zle_Mail_Transport_RewriteRecipients(
            array_merge($this->smtpOptions, array('address' => self::TESTING_ADDRESS))
        );
    }

    public function testConstructWithArrayOfAddresses()
    {
        $addresses = array('user1@example.com', 'user2@example.com');
        $options = array_merge($this->smtpOptions, array('addresses' => $addresses));
        $transport = new Zle_Mail_Transport_RewriteRecipients($options);
        $this->assertEquals(
            $addresses, $transport->getActualRecipients(),
            "Addresses should be parsed from constructor options"
        );
    }

    public function testConstructWithSingleAddress()
    {
        $address = 'user1@example.com';
        $options = array_merge($this->smtpOptions, array('address' => $address));
        $transport = new Zle_Mail_Transport_RewriteRecipients($options);
        $this->assertEquals(
            array($address), $transport->getActualRecipients(),
            "Address should be parsed from constructor options"
        );
    }

    public function testSentMessageRecipientsIsOverridden()
    {
        $mail = $this->getMail();
        $mail->addTo('real-user@example.com');
        $this->transport->send($mail);
        $messages = $this->transport->getSentEmails();
        $this->assertEquals(1, count($messages), "A message should be delivered");
        $this->assertEquals(
            array(self::TESTING_ADDRESS), $messages[0]->getRecipients(),
            "Recipient should be changed to " . self::TESTING_ADDRESS
        );
    }

    public function testSentMessageBodyIsRewritten()
    {
        $mail = $this->getMail();
        $realAddress = 'real-user@example.com';
        $mail->addTo($realAddress);
        $this->transport->send($mail);
        $messages = $this->transport->getSentEmails();
        $this->assertEquals(1, count($messages), "A message should be delivered");
        /** @var $sentMail Zend_Mail */
        $sentMail = $messages[0];
        $this->assertContains(
            $realAddress, quoted_printable_decode($sentMail->getBodyHtml(true)),
            "Original recipient should be added to the mail html body"
        );
        //Zend_Debug::dump($sentMail->getBodyHtml(true));
        $this->assertContains(
            $realAddress, quoted_printable_decode($sentMail->getBodyText(true)),
            "Original recipient should be added to the mail text body"
        );
    }

    /**
     * @expectedException Zle_Exception
     */
    public function testGetSentEmailsThrowsIfUnitTestIsNotEnabled()
    {
        // disable unit testing mode to test for exceptions
        Zle_Mail_Transport_RewriteRecipients::$_unitTestEnabled = false;
        $this->transport->getSentEmails();
    }

    /**
     * Test for longMailBodyShouldNotBeBrokenByRewrite
     */
    public function testLongMailBodyShouldNotBeBrokenByRewrite()
    {
        $mailWithLongBody = $this->getMail()->setBodyText(str_repeat('12345', 60));
        $mailWithLongBody->addTo('foo@example.com');
        $this->transport->send($mailWithLongBody);
        /** @var $delivered Zend_Mail */
        $delivered = current($this->transport->getSentEmails());
        $this->assertNotContains(
            '=', quoted_printable_decode($delivered->getBodyText()->getContent()),
            "Text should be encoded when appending to body"
        );
    }

    /**
     * Test for sendShouldThrowWhenNoRecipientsAreGiven
     *
     * @expectedException Zend_Mail_Transport_Exception
     */
    public function testSendShouldThrowWhenNoRecipientsAreGiven()
    {
        $this->transport->send($this->getMail());
    }


    /**
     * Return a new zend mail instance with some text inside and
     * no actualRecipients
     *
     * @return Zend_Mail
     */
    protected function getMail()
    {
        $mail = new Zend_Mail();
        $mail->setSubject('Subject');
        $mail->setBodyHtml('Body HTML');
        $mail->setBodyText('Body Text');
        return $mail;
    }
}
