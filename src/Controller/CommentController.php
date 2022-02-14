<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

#[Route("/api",name:"api_")]

class CommentController extends AbstractFOSRestController
{
    #[Route('/comment/{id}', name: 'comment')]
    public function index(int $id,Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $repository = $doctrine->getRepository(Post::class);
        $post = $repository->find($id);
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setPost($post);
        if($request->request->get('content')==null && $request->files->get('image')== null){
            return $this->json("Atleast one is required content and image both cannot be null");
        }
        if($request->request->get('content')!=null){
            $comment->setContent($request->request->get('content'));
        }
        if($request->files->get('image')!= null){
            $file = $request->files->get('image');
            $uploads_directory = $this->getParameter('uploads_directory');
            $filename = md5(uniqid()). '.' . $file->guessExtension();
            $file->move(
                $uploads_directory,
                $filename
            );
            $comment->setImage($filename);
        }
        
        
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();
     
        return $this->json('Created new comment successfully with id ' . $comment->getId()); 
    }


    #[Route('/comment/update/{id}', name: 'comment_update', methods:'POST')]

    public function update(int $id,Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $repository = $doctrine->getRepository(Comment::class);
        $comment = $repository->find($id);
        if($comment==null){
            return $this->json("comment donnot exists");
        }
        if($user->getId()!=$comment->getUser()->getId()){
            return $this->json("access denied");
        }
        if($request->request->get('content')!=null){
            $comment->setContent($request->request->get('content'));
        }
        if($request->files->get('image')!= null){
            $fileSystem = new Filesystem();
            $projectDir = $this->getParameter('kernel.project_dir');
            $filename = $comment->getImage();
            $fileSystem->remove($projectDir.'/public/uploads/'.$filename);
            $file = $request->files->get('image');
            $uploads_directory = $this->getParameter('uploads_directory');
            $filename = md5(uniqid()). '.' . $file->guessExtension();
            $file->move(
                $uploads_directory,
                $filename
            );
            $comment->setImage($filename);
        }
        
        
        
        $entityManager = $doctrine->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();
     
        return $this->json('updatd comment successfully with id ' . $comment->getId()); 
        
    }

    #[Route('/comment/delete/{id}', name: 'comment_delete', methods:'DELETE')]

    public function delete(int $id,Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $repository = $doctrine->getRepository(Post::class);
        $comment = $repository->find($id);
        if($post==null){
            return $this->json("file donnot exists");
        }
        if($user->getId()!=$comment->getUser()->getId()){
            return $this->json("access denied");
        }
        
        
        
        $fileSystem = new Filesystem();
        $projectDir = $this->getParameter('kernel.project_dir');
        $filename = $comment->getImage();
        $fileSystem->remove($projectDir.'/public/uploads/'.$filename);
        $id = $comment->getId();
        $entityManager = $doctrine->getManager();
        $entityManager->remove($comment);
        $entityManager->flush();
     
        return $this->json('updatd comment successfully with id ' . $id); 
        
    }
}
