<?php
namespace Payum\AuthorizeNet\Aim\Tests\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\CreditCard;
use Payum\Core\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Tests\GenericActionTest;

class CaptureActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\AuthorizeNet\Aim\Action\CaptureAction';

    protected $requestClass = 'Payum\Core\Request\Capture';

    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Action\CaptureAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function shouldImplementApiAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\AuthorizeNet\Aim\Action\CaptureAction');

        $this->assertTrue($rc->implementsInterface('Payum\Core\ApiAwareInterface'));
    }

    /**
     * @test
     */
    public function shouldAllowSetApi()
    {
        $expectedApi = $this->createAuthorizeNetAIMMock();

        $action = new CaptureAction();
        $action->setApi($expectedApi);

        $this->assertAttributeSame($expectedApi, 'api', $action);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\UnsupportedApiException
     */
    public function throwIfUnsupportedApiGiven()
    {
        $action = new CaptureAction();

        $action->setApi(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldDoNothingIfResponseCodeSet()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->never())
            ->method('authorizeAndCapture')
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'response_code' => 'foo',
        )));
    }

    /**
     * @test
     */
    public function shouldCaptureWithCreditCardSetExplicitly()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->will($this->returnValue($this->createAuthorizeNetAIMResponseMock()))
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'amount' => 10,
            'card_num' => '1234567812345678',
            'exp_date' => '10/16',
        )));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.
     */
    public function throwIfCreditCardNotSetExplicitlyAndObtainRequestNotSupportedOnCapture()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->never())
            ->method('authorizeAndCapture')
            ->will($this->returnValue($this->createAuthorizeNetAIMResponseMock()))
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->throwException(new RequestNotSupportedException()))
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'amount' => 10,
        )));
    }

    /**
     * @test
     */
    public function shouldCaptureWithObtainedCreditCard()
    {
        $api = $this->createAuthorizeNetAIMMock();
        $api
            ->expects($this->once())
            ->method('authorizeAndCapture')
            ->will($this->returnValue($this->createAuthorizeNetAIMResponseMock()))
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Core\Request\ObtainCreditCard'))
            ->will($this->returnCallback(function (ObtainCreditCard $request) {
                $card = new CreditCard();
                $card->setNumber('1234567812345678');
                $card->setExpireAt(new \DateTime('2014-10-01'));

                $request->set($card);
            }))
        ;

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setPayment($paymentMock);

        $action->execute(new Capture(array(
            'amount' => 10,
        )));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthorizeNetAIM
     */
    protected function createAuthorizeNetAIMMock()
    {
        return $this->getMock('Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM', array(), array(), '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AuthorizeNetAIM_Response
     */
    protected function createAuthorizeNetAIMResponseMock()
    {
        return $this->getMock('AuthorizeNetAIM_Response', array(), array(), '', false);
    }
}
