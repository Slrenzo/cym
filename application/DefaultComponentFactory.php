<?php

namespace application;

use controllers\AccountsController;
use controllers\HomeController;
use controllers\HumeursController;
use controllers\RegisterController;
use controllers\StatsController;
use services\AccountsService;
use services\HumeursService;
use services\RegisterService;
use services\StatsService;
use yasmf\ComponentFactory;
use yasmf\NoControllerAvailableForName;
use yasmf\NoServiceAvailableForName;

/**
 *  The controller factory
 */
class DefaultComponentFactory implements ComponentFactory
{
    private ?AccountsService $accountsService = null;
    private ?HumeursService $humeursService = null;
    private ?RegisterService $registerService = null;
    private ?StatsService $statsService = null;

    /**
     * @param string $controller_name the name of the controller to instanciate
     * @return mixed the controller
     * @throws NoControllerAvailableForName when controller is not found
     */
    public function buildControllerByName(string $controller_name): mixed
    {
        return match ($controller_name) {
            "Home", "home" => $this->buildHomeController(),
            "Accounts" , "accounts" => $this->buildAccountsController(),
            "Humeurs", "humeurs" => $this->buildHumeursController(),
            "Register", "register" => $this->buildRegisterController(),
            "Stats", "stats" => $this->buildStatsController(),
            default => throw new NoControllerAvailableForName($controller_name)
        };
    }

    /**
     * @param string $service_name the name of the service
     * @return mixed the created service
     * @throws NoServiceAvailableForName when service is not found
     */
    public function buildServiceByName(string $service_name): mixed
    {
        return match($service_name) {
            "Accounts" => $this->buildAccountsService(),
            "Humeurs" => $this->buildHumeursService(),
            "Register" => $this->buildRegisterService(),
            "Stats" => $this->buildStatsService(),
            default => throw new NoServiceAvailableForName($service_name)
        };
    }

    /**
     * @return AccountsService
     */
    private function buildAccountsService(): AccountsService
    {
        if ($this->accountsService == null) {
            $this->accountsService = new AccountsService();
        }
        return $this->accountsService;
    }

    /**
     * @return HumeursService
     */
    private function buildHumeursService(): HumeursService
    {
        if ($this->humeursService == null) {
            $this->humeursService = new HumeursService();
        }
        return $this->humeursService;
    }

    /**
     * @return RegisterService
     */
    private function buildRegisterService(): RegisterService
    {
        if ($this->registerService == null) {
            $this->registerService = new RegisterService();
        }
        return $this->registerService;
    }

    /**
     * @return StatsService
     */
    private function buildStatsService(): StatsService
    {
        if ($this->statsService == null) {
            $this->statsService = new StatsService();
        }
        return $this->statsService;
    }

    /**
     * @return AccountsController
     */
    private function buildAccountsController(): AccountsController
    {
        return new AccountsController($this->buildAccountsService());
    }

    /**
     * @return HomeController
     */
    private function buildHomeController(): HomeController
    {
        return new HomeController();
    }

    /**
     * @return HumeursController
     */
    private function buildHumeursController(): HumeursController
    {
        return new HumeursController($this->buildHumeursService());
    }

    /**
     * @return RegisterController
     */
    private function buildRegisterController(): RegisterController
    {
        return new RegisterController($this->buildRegisterService(), $this->buildAccountsService());
    }

    /**
     * @return StatsController
     */
    private function buildStatsController(): StatsController
    {
        return new StatsController($this->buildStatsService(), $this->buildHumeursService());
    }
}