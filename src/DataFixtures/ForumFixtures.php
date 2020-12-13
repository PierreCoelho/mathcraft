<?php

namespace App\DataFixtures;

use App\Entity\Forum;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ForumFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i=0; $i<4; $i++){
            $forum = new Forum();

            $forum->setName($faker->sentence())
                  ->setSlug($this->slugify($forum->getName()))
                  ->setDescription($faker->paragraph());

            $manager->persist($forum);

            for($j=0; $j<mt_rand(3, 18); $j++){
                $thread = new ForumThread();

                if(mt_rand(0,1)){
                    $user = $this->getReference('user-dummy');
                } else {
                    $user = $this->getReference('user-admin');
                }

                $thread->setTitle($faker->sentence())
                       ->setSlug($this->slugify($thread->getTitle()))
                       ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                       ->setSolved((bool)random_int(0, 1))
                       ->setAuthor($user)
                       ->setForum($forum);

                $previousPost = new ForumPost();
                
                for($k=0; $k<mt_rand(3,18); $k++){
                    $post = new ForumPost();

                    if($k==0){
                        $post->setCreatedAt($thread->getCreatedAt())
                             ->setHelpedSolve(false)
                             ->setAuthor($thread->getAuthor());
                    } else {
                        if(mt_rand(0,1)){
                            $user = $this->getReference('user-dummy');
                        } else {
                            $user = $this->getReference('user-admin');
                        }
                        $days = (new \DateTime())->diff($previousPost->getCreatedAt())->days;
                        $post->setCreatedAt($faker->dateTimeBetween('-'. $days .' days'))
                             ->setHelpedSolve((bool)random_int(0, 1))
                             ->setAuthor($user);
                    }

                    $post->setContent($faker->realText())
                         ->setIp($faker->ipv6)
                         ->setThread($thread);
                    
                    $manager->persist($post);
                    $previousPost = $post;
                    if($k==0){
                        $thread->setFirstPost($post);
                    }
                }
                $thread->setLastPostAuthor($post->getAuthor()->getUsername());
                $thread->setLastPostCreatedAt($post->getCreatedAt());
                
                $manager->persist($thread);
            }
        }
        

        $manager->flush();
    }

    private function slugify($string, $delimiter = '-') {
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower($clean);
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        $clean = trim($clean, $delimiter);
        setlocale(LC_ALL, $oldLocale);
        return $clean;
    }

    public function getDependencies()
    {
        return array(
            UserFixtures::class,
        );
    }
}
