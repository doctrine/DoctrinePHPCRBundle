<?php

namespace Doctrine\Bundle\PHPCRBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

/*
 * Hackery to be compatible with both Symfony < 5 and >= 5.
 *
 * @author Christophe Coevoet <stof@notk.org>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
if (Kernel::VERSION_ID >= 50000) {
    class PHPCRDataCollector extends AbstractPHPCRDataCollector
    {
        /**
         * {@inheritdoc}
         */
        public function collect(Request $request, Response $response, \Throwable $exception = null)
        {
            $this->collectInternal($request, $response);
        }
    }
} else {
    class PHPCRDataCollector extends AbstractPHPCRDataCollector
    {
        /**
         * {@inheritdoc}
         */
        public function collect(Request $request, Response $response, \Exception $exception = null)
        {
            $this->collectInternal($request, $response);
        }
    }
}
