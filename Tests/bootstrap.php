<?php
if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install --dev

EOM;

    exit(1);
}

$rc = new \ReflectionClass('Payum\Core\PaymentInterface');
$coreDir = dirname($rc->getFileName()).'/Tests';

$loader->add('Payum\AuthorizeNet\Aim\Tests', __DIR__);
$loader->add('Payum\Core\Tests', $coreDir);

