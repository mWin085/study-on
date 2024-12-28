<?php

namespace App\Tests;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
class CourseControllerTest extends WebTestCase
{

    public function testRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects();
/*
        $this->assertSelectorTextContains('h2', 'Give your feedback');*/
    }

    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/courses');

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);
        $coursesData = $courseRepository->findAll();

        $this->assertResponseIsSuccessful();


        //Проверка количества курсов на странице
        $coursesCount = $crawler->filter('.course-container')->count();
        $this->assertEquals(count($coursesData), $coursesCount);


        //добавление нового курса
        $linkNewCourse = $crawler->filter('.course-new')->link();

        $client->click($linkNewCourse);

        $crawler = $client->getCrawler();

        $formNewCourse = $crawler->filter('form')->form();

        $client->submit($formNewCourse, [
            'course[title]' => 'Test Course',
            'course[description]' => 'Test Course Description',
            'course[code]' => 'test_course' . rand(1, 9999999),
        ]);

        $client->followRedirect();
        $crawler = $client->getCrawler();

        $this->assertEquals($crawler->filter('.course-container')->count(), $coursesCount+1);

    }
    public function testShow(): void
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);
        $coursesData = $courseRepository->findAll();

        $crawler = $client->request('GET', '/courses/' . $coursesData[0]->getId());
        $this->assertResponseIsSuccessful();


        //Проверка количества лекций
        $lessonRepository = $entityManager->getRepository(Lesson::class);
        $coursesData = $courseRepository->findAll();
        $lessonData = $lessonRepository->findBy(['course_id' => $coursesData[0]->getId()]);
        $lessonCount = count($lessonData);
        $this->assertEquals($lessonCount, $coursesData[0]->getLessons()->count());


        //Добавление лекции
        $linkNewLesson = $crawler->filter('.course-new-lesson')->link();

        $client->click($linkNewLesson);

        $crawler = $client->getCrawler();

        $formNewLesson = $crawler->filter('form')->form();

        $client->submit($formNewLesson, [
            'lesson[title]' => 'Test Lesson',
            'lesson[text]' => 'Test Lesson Description',
            'lesson[number]' => rand(1, 100),
        ]);

        $client->followRedirect();
        $crawler = $client->getCrawler();

        $this->assertEquals($crawler->filter('.lesson-link')->count(), $lessonCount+1);

        $this->assertResponseIsSuccessful();


        //Редактирование курса
        $linkEditCourse = $crawler->filter('.course-edit')->link();

        $client->click($linkEditCourse);

        $crawler = $client->getCrawler();

        $formEditCourse = $crawler->filter('form')->form();

        $title = 'Test edit' . $formEditCourse->get('course[title]')->getValue();
        $description = 'Test edit' . $formEditCourse->get('course[description]')->getValue();

        $client->submit($formEditCourse, [
            'course[title]' => $title,
            'course[description]' => $description,
            'course[code]' => $formEditCourse->get('course[code]')->getValue(),
        ]);

        $client->followRedirect();
        $crawler = $client->getCrawler();

        $this->assertEquals($crawler->filter('.course-title')->text(), $title);
        $this->assertEquals($crawler->filter('.course-description')->text(), $description);


        //Проверка ошибки 404
        $client->request('GET','/courses/' . '99999999' . $coursesData[0]->getId());

        $this->assertResponseStatusCodeSame(404);


    }
    public function testEdit(): void
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);
        $coursesData = $courseRepository->findAll();

        $crawler = $client->request('GET', '/courses/' . $coursesData[0]->getId() . '/edit');

        $this->assertResponseIsSuccessful();


    }
    public function testNew(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/courses/new');

        $this->assertResponseIsSuccessful();

    }
}
