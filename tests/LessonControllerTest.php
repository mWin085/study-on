<?php

namespace App\Tests;

use App\Entity\Lesson;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LessonControllerTest extends WebTestCase
{
    public function testShow(): void
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $lessonRepository = $entityManager->getRepository(Lesson::class);
        $lessonsData = $lessonRepository->findAll();

        $crawler = $client->request('GET', '/lessons/' . $lessonsData[0]->getId());
        $this->assertResponseIsSuccessful();


        //Редактирование лекции
        $linkEditLesson = $crawler->filter('.lesson-edit')->link();

        $client->click($linkEditLesson);

        $crawler = $client->getCrawler();

        $formEditLesson = $crawler->filter('form')->form();

        $title = 'Test edit' . $formEditLesson->get('lesson[title]')->getValue();
        $text = 'Test edit' . $formEditLesson->get('lesson[text]')->getValue();
        $number = rand(1, 100);

        $client->submit($formEditLesson, [
            'lesson[title]' => $title,
            'lesson[text]' => $text,
            'lesson[number]' => $number,
        ]);

        $client->followRedirect();
        $crawler = $client->getCrawler();

        $this->assertEquals($crawler->filter('.lesson-title')->text(), $title);
        $this->assertEquals($crawler->filter('.lesson-text')->text(), $text);


        //Проверка ошибки 404
        $client->request('GET','/lessons/' . '99999999' . $lessonsData[0]->getId());

        $this->assertResponseStatusCodeSame(404);

    }



    public function testEdit(): void
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $lessonRepository = $entityManager->getRepository(Lesson::class);
        $lessonsData = $lessonRepository->findAll();

        $crawler = $client->request('GET', '/lessons/' . $lessonsData[0]->getId() . '/edit');

        $this->assertResponseIsSuccessful();


    }
}
