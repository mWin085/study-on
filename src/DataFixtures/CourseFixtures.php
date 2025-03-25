<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $course = new Course();

        $course->setTitle('Древняя Греция: от мифов к демократии');
        $course->setDescription('Курс погрузит вас в мир античной Греции, от мифологических истоков до расцвета демократии. Вы познакомитесь с ключевыми событиями, философскими идеями и культурными достижениями этой цивилизации.');
        $course->setCode('course_1');
        $manager->persist($course);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Мифы и герои');
        $lesson->setCourseId($course);
        $lesson->setText('Рассказ о греческих богах и героях, их роли в жизни древних греков. Описаны наиболее известные мифы и их значение для понимания греческой культуры.');
        $lesson->setNumber('1');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Возникновение полисов');
        $lesson->setCourseId($course);
        $lesson->setText('Анализ формирования городов-государств (полисов). Обсуждение причин и последствий возникновения новых политических единиц в Греции.');
        $lesson->setNumber('2');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Персидские войны');
        $lesson->setCourseId($course);
        $lesson->setText('Подробное описание конфликтов между греческими полисами и персидской империей. Выявление причин, последствий и влияния этих войн на греческую цивилизацию.');
        $lesson->setNumber('3');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Золотой век Афин');
        $lesson->setCourseId($course);
        $lesson->setText('Погружение в период расцвета Афинского государства. Подробное рассмотрение афинской демократии, культуры и искусства.');
        $lesson->setNumber('4');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Греческая философия');
        $lesson->setCourseId($course);
        $lesson->setText('Знакомство с ключевыми фигурами и идеями греческой философии (Сократ, Платон, Аристотель). Анализ их влияния на развитие европейской мысли.');
        $lesson->setNumber('5');
        $manager->persist($lesson);

        $manager->flush();

        $course = new Course();
        $course->setTitle('Средневековая Европа: войны, крестовые походы и культурные преобразования');
        $course->setDescription('Этот курс осветит ключевые события и процессы в средневековой Европе. Вы узнаете о феодализме, крестовых походах, развитии городов и культурных достижениях этого периода.');
        $course->setCode('course_2');
        $manager->persist($course);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Феодализм и его особенности');
        $lesson->setCourseId($course);
        $lesson->setText('Анализ социальных и политических отношений в средневековой Европе. Выявление причин и последствий развития феодальной системы.');
        $lesson->setNumber('1');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Крестовые походы');
        $lesson->setCourseId($course);
        $lesson->setText('Подробное описание крестовых походов, мотивов участников, последствий для европейской истории и культуры');
        $lesson->setNumber('2');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Развитие городов');
        $lesson->setCourseId($course);
        $lesson->setText('Обсуждение возникновения и роста средневековых городов. Анализ экономических и социальных изменений, связанных с урбанизацией');
        $lesson->setNumber('3');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Культура и искусство Средневековья');
        $lesson->setCourseId($course);
        $lesson->setText('Описание архитектурных стилей, литературы, живописи и других форм искусства в средние века. Прослеживание влияния религиозных и светских ценностей.');
        $lesson->setNumber('4');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Падение Константинополя');
        $lesson->setCourseId($course);
        $lesson->setText('Подробный анализ падения Византийской империи и его влияния на европейскую историю.');
        $lesson->setNumber('5');
        $manager->persist($lesson);

        $manager->flush();

        $course = new Course();
        $course->setTitle('Великие географические открытия и их последствия');
        $course->setDescription('Этот курс исследует период Великих географических открытий и их воздействие на мир. Вы узнаете о причинах, методах и последствиях этих открытий для европейской истории, культуры и географии.');
        $course->setCode('course_3');
        $manager->persist($course);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Причины Великих географических открытий');
        $lesson->setCourseId($course);
        $lesson->setText('Анализ экономических, политических и религиозных мотивов, побудивших европейцев к поиску новых земель.');
        $lesson->setNumber('1');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Открытия Колумба и Васко да Гамы');
        $lesson->setCourseId($course);
        $lesson->setText('Подробное рассмотрение путешествий, географических открытий и последствий этих экспедиций.');
        $lesson->setNumber('2');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Влияние на Европу');
        $lesson->setCourseId($course);
        $lesson->setText('Анализ изменений, произошедших в европейской экономике, торговле и обществе в результате открытий новых земель.');
        $lesson->setNumber('3');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Влияние на другие континенты');
        $lesson->setCourseId($course);
        $lesson->setText('Обзор колонизации, влияния европейских культур и социальных процессов на американские и азиатские культуры и общества.');
        $lesson->setNumber('4');
        $manager->persist($lesson);

        $manager->flush();

        $lesson = new Lesson();
        $lesson->setTitle('Последствия Великих географических открытий');
        $lesson->setCourseId($course);
        $lesson->setText('Обзор долгосрочных последствий этих событий для мировой истории, экономики и культуры');
        $lesson->setNumber('5');
        $manager->persist($lesson);


        $manager->flush();
    }
}
