<?php
namespace OOPress\Modules\ExampleModule;

class Controller
{
    public function home(): void
    {
        $logoBasePath = '/assets/images/logo';
        $logoLight = $logoBasePath . '/oopress_logo.png';
        $logoDark  = $logoBasePath . '/oopress_logo_dark.png';

        include __DIR__ . '/views/home.php';
    }
}
