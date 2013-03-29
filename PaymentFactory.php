<?php
namespace Payum\AuthorizeNet\Aim;

use Payum\Payment;
use Payum\Extension\EndlessCycleDetectorExtension;
use Payum\Action\CaptureDetailsAggregatedModelAction;
use Payum\Action\SyncDetailsAggregatedModelAction;
use Payum\Action\StatusDetailsAggregatedModelAction;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\AuthorizeNet\Aim\Action\CaptureAction;
use Payum\AuthorizeNet\Aim\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param AuthorizeNetAIM $api
     *
     * @return Payment
     */
    public static function create(AuthorizeNetAIM $api)
    {
        $payment = new Payment;

        $payment->addApi($api);
        
        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new CaptureDetailsAggregatedModelAction);
        $payment->addAction(new SyncDetailsAggregatedModelAction);
        $payment->addAction(new StatusDetailsAggregatedModelAction);

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}