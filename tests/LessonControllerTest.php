<?php

namespace App\Tests;

use App\Entity\Lesson;

class LessonControllerTest extends AbstractTestController /*extends WebTestCase*/
{

    public function testShow(): void
    {

        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $lessonRepository = $entityManager->getRepository(Lesson::class);
        $lessonsData = $lessonRepository->findAll();

        $this->client->request('GET', '/lessons/' . $lessonsData[0]->getId());
        $this->assertResponseRedirects();


        $this->authUser();

        $lessonBuy = $lessonRepository->findOneByCode('course_3')[0];

        $this->client->request('GET', '/lessons/' . $lessonBuy->getId());
        $this->assertResponseIsSuccessful();


        $this->client->request('GET', '/lessons/' . $lessonsData[0]->getId());
        $this->assertResponseIsSuccessful();


        //Проверка ошибки 404
        $this->client->request('GET','/lessons/' . '9' . $lessonsData[0]->getId());

        $this->assertResponseStatusCodeSame(404);

    }


    public function testEdit(): void
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $lessonRepository = $entityManager->getRepository(Lesson::class);
        $lessonsData = $lessonRepository->findAll();


        //Редактирование лекции
        $this->authUser();
        $crawler = $this->client->request('GET', '/lessons/' . $lessonsData[0]->getId());
        $this->assertEquals($crawler->filter('.lesson-edit')->count(), 0);

        $this->authAdmin();
        $crawler = $this->client->request('GET', '/lessons/' . $lessonsData[0]->getId());
        $linkEditLesson = $crawler->filter('.lesson-edit')->link();

        $this->client->click($linkEditLesson);

        $crawler = $this->client->getCrawler();

        $formEditLesson = $crawler->filter('form')->form();

        $title = 'Test edit' . $formEditLesson->get('lesson[title]')->getValue();
        $text = 'Test edit' . $formEditLesson->get('lesson[text]')->getValue();
        $number = rand(1, 100);

        $this->client->submit($formEditLesson, [
            'lesson[title]' => $title,
            'lesson[text]' => $text,
            'lesson[number]' => $number,
        ]);

        $this->client->followRedirect();
        $crawler = $this->client->getCrawler();

        $this->assertEquals($crawler->filter('.lesson-title')->text(), $title);
        $this->assertEquals($crawler->filter('.lesson-text')->text(), $text);

    }
}
