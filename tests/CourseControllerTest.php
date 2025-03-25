<?php

namespace App\Tests;

use App\Entity\Course;
use App\Entity\Lesson;
class CourseControllerTest extends AbstractTestController
{


    public function testRedirect(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseRedirects();

    }
    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/courses');

        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);
        $coursesData = $courseRepository->findAll();

        $this->assertResponseIsSuccessful();


        //Проверка количества курсов на странице
        $coursesCount = $crawler->filter('.course-container')->count();

        $this->assertEquals(count($coursesData), $coursesCount);


        //добавление нового курса
        $this->assertEquals(0, $crawler->filter('.course-new')->count());

        $this->authAdmin();

        $crawler = $this->client->request('GET', '/courses');

        $linkNewCourse = $crawler->filter('.course-new')->link();

        $crawler = $this->client->click($linkNewCourse);

        $formNewCourse = $crawler->filter('form')->form();

        //Не заполнена цена
        $this->client->submit($formNewCourse, [
            'course[title]' => 'Test Course',
            'course[description]' => 'Test Course Description',
            'course[code]' => 'course_4',
            'course[type]' => 'rent',
        ]);

        $this->assertResponseStatusCodeSame(422);

        $formNewCourse = $crawler->filter('form')->form();

        //Корректные данные
        $this->client->submit($formNewCourse, [
            'course[title]' => 'Test Course',
            'course[description]' => 'Test Course Description',
            'course[code]' => 'course_4',
            'course[type]' => 'rent',
            'course[price]' => 400,
        ]);

        $crawler = $this->client->followRedirect();

        $this->assertEquals($crawler->filter('.course-container')->count(), $coursesCount + 1);

    }


    public function testShow(): void
    {

        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);
        $coursesData = $courseRepository->findAll();

        $crawler = $this->client->request('GET', '/courses/' . $coursesData[0]->getId());
        $this->assertResponseIsSuccessful();


        //Проверка количества лекций
        $lessonRepository = $entityManager->getRepository(Lesson::class);
        $coursesData = $courseRepository->findAll();
        $lessonData = $lessonRepository->findBy(['course_id' => $coursesData[0]->getId()]);
        $lessonCount = count($lessonData);
        $this->assertEquals($lessonCount, $coursesData[0]->getLessons()->count());

        $this->authAdmin();

        $crawler = $this->client->request('GET', '/courses/' . $coursesData[0]->getId());

        //Добавление лекции
        $linkNewLesson = $crawler->filter('.course-new-lesson')->link();

        $crawler = $this->client->click($linkNewLesson);

        $formNewLesson = $crawler->filter('form')->form();

        $this->client->submit($formNewLesson, [
            'lesson[title]' => 'Test Lesson',
            'lesson[text]' => 'Test Lesson Description',
            'lesson[number]' => rand(1, 100),
        ]);

        $crawler = $this->client->followRedirect();

        $this->assertEquals($crawler->filter('.lesson-link')->count(), $lessonCount + 1);

        $this->assertResponseIsSuccessful();


        //Редактирование курса
        $linkEditCourse = $crawler->filter('.course-edit')->link();

        $crawler = $this->client->click($linkEditCourse);

        $formEditCourse = $crawler->filter('form')->form();

        $title = 'Test edit' . $formEditCourse->get('course[title]')->getValue();
        $description = 'Test edit' . $formEditCourse->get('course[description]')->getValue();

        //Код курса уже используется
        $this->client->submit($formEditCourse, [
            'course[title]' => $title,
            'course[description]' => $description,
            'course[code]' => 'course_2',
            'course[type]' => 'rent',
            'course[price]' => 400,
        ]);

        $this->assertResponseStatusCodeSame(422);

        $formEditCourse = $crawler->filter('form')->form();

        //корректные данные
        $this->client->submit($formEditCourse, [
            'course[title]' => $title,
            'course[description]' => $description,
            'course[code]' => $formEditCourse->get('course[code]')->getValue(),
            'course[type]' => 'rent',
            'course[price]' => 400,
        ]);

        $crawler = $this->client->followRedirect();

        $this->assertEquals($crawler->filter('.course-title')->text(), $title);
        $this->assertEquals($crawler->filter('.course-description')->text(), $description);


        //Проверка ошибки 404
        $this->client->request('GET', '/courses/' . '9' . $coursesData[0]->getId());

        $this->assertResponseStatusCodeSame(404);


    }

    public function testPrice()
    {
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);
        $coursesData = $courseRepository->findAll();

        $crawler = $this->client->request('GET', '/courses/' . $coursesData[0]->getId());
        $this->assertEquals($crawler->filter('.free-type')->count(), 1);


        $crawler = $this->client->request('GET', '/courses/' . $coursesData[1]->getId());
        $this->assertEquals($crawler->filter('.rent-type')->count(), 1);
        $this->assertEquals($crawler->filter('.rent-data')->count(), 0);

        $crawler = $this->client->request('GET', '/courses/' . $coursesData[2]->getId());
        $this->assertEquals($crawler->filter('.buy-type')->count(), 1);
        $this->assertEquals($crawler->filter('.buy-data')->count(), 0);


        $this->authUser();
        $crawler = $this->client->request('GET', '/courses/' . $coursesData[1]->getId());
        $this->assertEquals($crawler->filter('.rent-data')->count(), 1);


        $crawler = $this->client->request('GET', '/courses/' . $coursesData[2]->getId());
        $this->assertEquals($crawler->filter('.buy-data')->count(), 1);
    }

    public function testPayCourse(){
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $courseRepository = $entityManager->getRepository(Course::class);
        $coursesData = $courseRepository->findAll();

        $this->authNewUser();
        $crawler = $this->client->request('GET', '/courses/' . $coursesData[1]->getId());

        $form = $crawler->filter('form')->form();
        //Корректная оплата
        $this->client
            ->submit($form, []);

        $crawler = $this->client->followRedirect();

        $this->assertEquals($crawler->filter('.alert-success')->count(), 1);


        //Недостаточно средств для покупки курса
        $crawler = $this->client->request('GET', '/courses/' . $coursesData[2]->getId());
        $this->assertEquals($crawler->filter('#coursePayment')->count(), 0);

    }
}
