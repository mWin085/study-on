<?php

namespace App\Tests;

use function PHPUnit\Framework\assertEquals;

class SecurityControllerTest extends AbstractTestController
{
    public function testLogin()
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();

        $form = $crawler->filter('form')->form();

        //Некорректные данные
        $this->client->submit($form, [
            'email' => 'admin@admin.com',
            'password' => 'admin',
        ]);
        $this->assertResponseStatusCodeSame('302');


        //Корректные данные
        $this->client->submit($form, [
            'email' => 'admin@admin.com',
            'password' => 'adminadmin',
        ]);

        $this->assertResponseRedirects();

    }

    public function testIndex()
    {

        $this->client->request('GET', '/profile');
        $this->assertResponseRedirects();

        $this->authUser();
        $this->client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();

    }

    public function testRegister()
    {

        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();

        //Email уже используется
        $this->client->submit($form, [
            'register[email]' => 'admin@admin.com',
            'register[password][first]' => 'adminadmin',
            'register[password][second]' => 'adminadmin',
        ]);

        $this->assertResponseStatusCodeSame(422);

        //Невалидный пароль
        $this->client->submit($form, [
            'register[email]' => 'user1@admin.com',
            'register[password][first]' => '1',
            'register[password][second]' => '1',
        ]);

        $this->assertResponseStatusCodeSame(422);

        //Корректные данные
        $this->client->submit($form, [
            'register[email]' => 'user1@admin.com',
            'register[password][first]' => 'useruser',
            'register[password][second]' => 'useruser',
        ]);
        $this->assertResponseRedirects();
    }

    public function testTransactions()
    {

        $this->client->request('GET', '/transactions');
        $this->assertResponseRedirects();

        $this->authUser();
        $crawler = $this->client->request('GET', '/transactions');
        $this->assertResponseIsSuccessful();

        $rows = $crawler->filter('tbody')->filter('tr');
        $this->assertEquals(2, $rows->count());

    }

}