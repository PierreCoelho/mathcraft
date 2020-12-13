<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Form\PostType;
use App\Form\ThreadType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Repository\ForumPostRepository;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ForumThreadRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ForumController extends AbstractController
{
    /**
     * @Route("/forum", name="forum_home")
     */
    public function index(Request $request, ForumRepository $forumRepository): Response
    {
        $forums = $forumRepository->findAll();
        
        return $this->render('forum/index.html.twig', [
            'crumbs' => $this->getCrumbs($request),
            'forums' => $forums,
        ]);
    }

    /**
     * @Route("/forum/{forum_slug}", name="forum_forums")
     * @Paramconverter("forum", options={"mapping": {"forum_slug" = "slug"}})
     */
    public function showForum(Request $request, Forum $forum, ForumThreadRepository $threadRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));

        $threads = $threadRepository->findOrderedThreadsInForumPaginator($forum,$offset);

        if(is_int(count($threads)/ForumThreadRepository::PAGINATOR_PER_PAGE)){
            $numberOfPages = count($threads)/ForumThreadRepository::PAGINATOR_PER_PAGE;
        } else {
            $numberOfPages = intdiv(count($threads),ForumThreadRepository::PAGINATOR_PER_PAGE)+1;
        }

        $paginatorPerPage = ForumThreadRepository::PAGINATOR_PER_PAGE;
        $currentPage = $offset/$paginatorPerPage + 1;
        return $this->render('forum/forum.html.twig', [
            'forum' => $forum,
            'threads' => $threads,
            'crumbs' => $this->getCrumbs($request),
            'numberOfPages' => $numberOfPages,
            'paginatorPerPage' => $paginatorPerPage,
            'offset' => $offset,
            'currentPage' => $currentPage,
            'previous' => $offset - ForumThreadRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($threads), $offset + ForumThreadRepository::PAGINATOR_PER_PAGE),
            'errorMessage' => $request->query->get('errorMessage', false)
        ]);
    }

    /**
     * @Route("/forum/{forum_slug}/nouveau-sujet", name="forum_new_thread")
     * @Route("/forum/{forum_slug}/{thread_slug}/editer-sujet", name="forum_edit_thread")
     * @Paramconverter("forum", options={"mapping": {"forum_slug" = "slug"}})
     * @Paramconverter("thread", options={"mapping": {"thread_slug" = "slug"}})
     */
    public function handleThread(Request $request, Forum $forum = null, EntityManagerInterface $manager, ForumThread $thread = null): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login',[
                'errorMessage' => 'Vous devez être connecté pour pouvoir écrire ou modifier un sujet.'
            ]);
        } elseif($thread && $this->getUser() != $thread->getAuthor()) {
            return $this->redirectToRoute('forum_forums',[
                'forum_slug' => $forum->getSlug(),
                'errorMessage' => 'Vous ne pouvez pas modifier le sujet d\'un autre utilisateur.'
            ]);
        }
        
        if(!$thread){
            $thread = new ForumThread();
        }

        $form = $this->createForm(ThreadType::class, $thread);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($thread->getId()){
                $thread->getFirstPost()->setEditedAt(new \DateTime());
                $thread->setSlug($this->slugify($thread->getTitle()));
                $post = $thread->getFirstPost();
            } else {
                $post = $thread->getFirstPost();
                $post->setCreatedAt(new \DateTime());
                $post->setIp($request->getClientIp());
                $post->setAuthor($this->getUser());
                $post->setThread($thread);
                $post->setHelpedSolve(false);
                $thread->setSlug($this->slugify($thread->getTitle()));
                $thread->setCreatedAt(new \DateTime());
                $thread->setAuthor($this->getUser());
                $thread->setForum($forum);
                $thread->setLastPostAuthor($post->getAuthor()->getUsername());
                $thread->setLastPostCreatedAt($post->getCreatedAt());
            }

            $manager->persist($thread);
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('forum_thread', [
                'forum_slug' => $forum->getSlug(),
                'thread_slug' => $thread->getSlug()
            ]);
        }

        return $this->render('forum/handle_thread.html.twig', [
            'threadForm' => $form->createView(),
            'editMode' => $thread->getId() !== null,
            'crumbs' => $this->getCrumbs($request)
        ]);
    }

    /**
     * @Route("/forum/{forum_slug}/{thread_slug}", name="forum_thread")
     * @Paramconverter("forum", options={"mapping": {"forum_slug" = "slug"}})
     * @Paramconverter("thread", options={"mapping": {"thread_slug" = "slug"}})
     */
    public function showThread(Request $request, Forum $forum, ForumThread $thread, ForumPostRepository $postRepository): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));

        $posts = $postRepository->findOrderedPostsInThreadPaginator($thread,$offset);

        if(is_int(count($posts)/ForumPostRepository::PAGINATOR_PER_PAGE)){
            $numberOfPages = count($posts)/ForumPostRepository::PAGINATOR_PER_PAGE;
        } else {
            $numberOfPages = intdiv(count($posts),ForumPostRepository::PAGINATOR_PER_PAGE)+1;
        }

        $paginatorPerPage = ForumPostRepository::PAGINATOR_PER_PAGE;
        $currentPage = $offset/$paginatorPerPage + 1;
        return $this->render('forum/thread.html.twig', [
            'forum' => $forum,
            'thread' => $thread,
            'posts' => $posts,
            'crumbs' => $this->getCrumbs($request),
            'numberOfPages' => $numberOfPages,
            'paginatorPerPage' => $paginatorPerPage,
            'offset' => $offset,
            'currentPage' => $currentPage,
            'previous' => $offset - ForumPostRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($posts), $offset + ForumPostRepository::PAGINATOR_PER_PAGE),
            'errorMessage' => $request->query->get('errorMessage', false)
        ]);
        
        /*return $this->render('forum/thread.html.twig',[
            'thread' => $thread,
            'crumbs' => $this->getCrumbs($request),
            'errorMessage' => $request->query->get('errorMessage', false)
        ]);*/
    }

    /**
     * @Route("/forum/{forum_slug}/{thread_slug}/resolu", name="forum_thread_solved")
     * @Paramconverter("forum", options={"mapping": {"forum_slug" = "slug"}})
     * @Paramconverter("thread", options={"mapping": {"thread_slug" = "slug"}})
     */
    public function solvedThread(EntityManagerInterface $manager, Forum $forum, ForumThread $thread): Response
    {
        if(!$this->getUser() || $this->getUser() != $thread->getAuthor() || $thread->getSolved()){
            return $this->redirectToRoute('forum_thread', [
                'forum_slug' => $forum->getSlug(),
                'thread_slug' => $thread->getSlug()
            ]);
        }

        $thread->setTitle('[Résolu] '. $thread->getTitle());
        $thread->setSlug($this->slugify($thread->getTitle()));
        $thread->setSolved(true);

        $manager->persist($thread);
        $manager->flush();


        return $this->redirectToRoute('forum_thread', [
            'forum_slug' => $forum->getSlug(),
            'thread_slug' => $thread->getSlug()
        ]);
    }

    /**
     * @Route("/forum/{forum_slug}/{thread_slug}/repondre", name="forum_add_post")
     * @Route("/forum/{forum_slug}/{thread_slug}/{post_id}/editer-reponse", name="forum_edit_post")
     * @Paramconverter("forum", options={"mapping": {"forum_slug" = "slug"}})
     * @Paramconverter("thread", options={"mapping": {"thread_slug" = "slug"}})
     * @Paramconverter("post", options={"mapping": {"post_id" = "id"}})
     */
    public function handlePost(Request $request, EntityManagerInterface $manager, Forum $forum, ForumThread $thread, ForumPost $post = null): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login',[
                'errorMessage' => 'Vous devez être connecté pour pouvoir écrire ou modifier un message.'
            ]);
        } elseif($post && $this->getUser() != $post->getAuthor()) {
            return $this->redirectToRoute('forum_thread',[
                'forum_slug' => $forum->getSlug(),
                'thread_slug' => $thread->getSlug(),
                'errorMessage' => 'Vous ne pouvez pas modifier le message d\'un autre utilisateur.'
            ]);
        }

        if(!$post){
            $post = new ForumPost();
        }

        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($post->getId()){
                $post->setEditedAt(new \DateTime());
            }else{
                $post->setCreatedAt(new \DateTime());
                $post->setIp($request->getClientIp());
                $post->setHelpedSolve(false);
                $post->setAuthor($this->getUser());
                $post->setThread($thread);
                $thread->setLastPostCreatedAt($post->getCreatedAt());
                $thread->setLastPostAuthor($post->getAuthor()->getUsername());
            }

            $manager->persist($post);
            $manager->persist($thread);
            $manager->flush();

            return $this->redirectToRoute('forum_thread', [
                'forum_slug' => $forum->getSlug(),
                'thread_slug' => $thread->getSlug()
            ]);
        }
        
        return $this->render('forum/handle_post.html.twig', [
            'postForm' => $form->createView(),
            'editMode' => $post->getId() !== null,
            'crumbs' => $this->getCrumbs($request)
        ]);
    }

    /**
     * @Route("/forum/{forum_slug}/{thread_slug}/{post_id}/helped-solve", name="forum_post_helped_solve")
     * @Paramconverter("forum", options={"mapping": {"forum_slug" = "slug"}})
     * @Paramconverter("thread", options={"mapping": {"thread_slug" = "slug"}})
     * @Paramconverter("post", options={"mapping": {"post_id" = "id"}})
     */
    public function helpedSolve(EntityManagerInterface $manager,Forum $forum, ForumThread $thread, ForumPost $post): Response
    {
        if(!$this->getUser() || $this->getUser() != $thread->getAuthor() || $post->getHelpedSolve()){
            return $this->redirectToRoute('forum_thread', [
                'forum_slug' => $forum->getSlug(),
                'thread_slug' => $thread->getSlug()
            ]);
        }

        $post->setHelpedSolve(true);
        $manager->persist($post);
        $manager->flush();

        return $this->redirectToRoute('forum_thread', [
            'forum_slug' => $forum->getSlug(),
            'thread_slug' => $thread->getSlug()
        ]);
    }

    /**
     * @Route("/forum/{forum_slug}/{thread_slug}/{post_id}/moderation", name="forum_post_moderate")
     * @Paramconverter("forum", options={"mapping": {"forum_slug" = "slug"}})
     * @Paramconverter("thread", options={"mapping": {"thread_slug" = "slug"}})
     * @Paramconverter("post", options={"mapping": {"post_id" = "id"}})
     */
    public function moderatePost(Request $request, EntityManagerInterface $manager,Forum $forum, ForumThread $thread, ForumPost $post): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $form = $this->createFormBuilder($post)
                     ->add('moderateReason', TextType::class)
                     ->getForm();
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('forum_thread', [
                'forum_slug' => $forum->getSlug(),
                'thread_slug' => $thread->getSlug()
            ]);
        }

        return $this->render('forum/moderate_post.html.twig', [
            'moderateForm' => $form->createView(),
            'post' => $post,
            'crumbs' => $this->getCrumbs($request)
        ]);
    }
}
