<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\AuthorizeNet\Aim\Action\FillOrderDetailsAction;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->corePaymentFactory = $corePaymentFactory ?: new CorePaymentFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->corePaymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->corePaymentFactory->createConfig());

        $config->defaults(array(
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
        ));

        if (false == $config['payum.api']) {
            $config['options.required'] = array('loginId', 'transactionKey');

            $config->defaults(array(
                'sandbox' => true,
            ));

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $api = new AuthorizeNetAIM($config['loginId'], $config['transactionKey']);
                $api->setSandbox($config['sandbox']);

                return $api;
            };
        }

        return (array) $config;
    }
}
