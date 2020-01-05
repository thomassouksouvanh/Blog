<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpParser\Node\Stmt\If_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{

    /**
     * @Route("/blog", name="blog")
     * @param ArticleRepository $repo
     * @return Response
     */
    public function index(ArticleRepository $repo)
    {
        $articles = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/" , name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig',
            [
                'title' => "Page d'accueil",
            ]);
    }

    /**
     * @Route("/blog/new", defaults={"article" = null}, name = "blog_create")
     * @route("/blog/edit/{id}", name = "blog_edit")
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     * @throws Exception
     */
    public function form(Article $article = null, Request $request ,EntityManagerInterface $manager)
    {
        if(!$article){
            $article = new Article();
        }

        //$form = $this->createFormBuilder($article)
           // ->add('title')
            //->add('content')
            //->add('image')
            //->getForm();

        $form = $this->createForm(ArticleType::class,$article);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if(!$article->getId())
            {
                $article->setCreatedAt(new \DateTime());
            }

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('blog_show',['id' => $article->getId()]);
        }

        return $this->render('blog/create.html.twig',
            [
                'formArticle' => $form->createView(),
                'editMode' => $article->getId() !== null,
            ]);
    }

    /**
     * @Route("/blog/show/{id}" , name = "blog_show")
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     * @throws Exception
     */
    public function show(Article $article,Request $request,EntityManagerInterface $manager)  // show($id)
    {
        // $repo = $this->getDoctrine()->getRepository(Article::class);
            // $article = $repo->find($id);
        $comment = new Comment();
        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $comment->setCreatedAt(new \DateTime())
                    ->setArticle($article);
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('blog_show',['id'=> $article->getId()]);
        }

        return $this->render('blog/show.html.twig',
            [
                'article' => $article,
                'commentForm' => $form->createView()
            ]);
    }

}
