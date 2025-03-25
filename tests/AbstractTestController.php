<?php

namespace App\Tests;

use App\DataFixtures\CourseFixtures;
use App\Tests\Mock\BillingClientMock;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractTestController extends WebTestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        // запрещаем перезагрузку ядра, чтобы не сбросилась подмена сервиса при запросе
        $this->client->disableReboot();

        $this->client->getContainer()->set(
            'App\Service\BillingClient',
            new BillingClientMock('')
        );
        // Очистка базы данных перед загрузкой фикстур
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($entityManager, $purger);

        // Загрузка фикстур
        $loader = new Loader();
        $loader->addFixture(new CourseFixtures());

        // Выполнение загрузки фикстур
        $executor->execute($loader->getFixtures());
    }

    /**
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function authAdmin(): \Symfony\Component\DomCrawler\Crawler
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->filter('form')->form();
        $this->client->submit($form, [
            'email' => 'admin@admin.com',
            'password' => 'adminadmin',
        ]);
        return $crawler;
    }
    /**
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function authUser(): \Symfony\Component\DomCrawler\Crawler
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->filter('form')->form();
        $this->client->submit($form, [
            'email' => 'user@user.com',
            'password' => 'useruser',
        ]);

        return $crawler;
    }
    /**
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function authNewUser(): \Symfony\Component\DomCrawler\Crawler
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->filter('form')->form();
        $this->client->submit($form, [
            'email' => 'user1@user.com',
            'password' => 'useruser',
        ]);

        return $crawler;
    }

}